<?php

/**
 * Migra logos existentes de institucion.logo_ifts a Cloudinary.
 *
 * Uso:
 *   php scripts/migrar_logos_cloudinary.php --env-file=.env.production --dry-run
 *   php scripts/migrar_logos_cloudinary.php --dry-run
 *   php scripts/migrar_logos_cloudinary.php
 *   php scripts/migrar_logos_cloudinary.php --limit=20
 *   php scripts/migrar_logos_cloudinary.php --only-id=12
 */

$options = getopt('', ['dry-run', 'limit::', 'only-id::', 'env-file::']);
$dryRun = array_key_exists('dry-run', $options);
$limit = isset($options['limit']) ? (int)$options['limit'] : null;
$onlyId = isset($options['only-id']) ? (int)$options['only-id'] : null;
$envFile = isset($options['env-file']) ? (string)$options['env-file'] : null;

if ($envFile !== null && $envFile !== '') {
    cargarEnvDesdeArchivo($envFile);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/CloudinaryService.php';

$mediaFolders = require __DIR__ . '/../config/media-folders.php';

$db = Database::getInstance();
$pdo = $db->getConnection();
$cloudinary = new CloudinaryService($mediaFolders['base'] ?? 'ComunidadIFTS');
$folderLogo = $mediaFolders['instituciones']['logo'] ?? 'ComunidadIFTS/logoIFTS';

$dbHostActivo = (string)($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'N/A');
$dbNameEnv = (string)($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'N/A');
$dbNameReal = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();

echo 'DB_HOST activo: ' . $dbHostActivo . PHP_EOL;
echo 'DB_NAME (.env): ' . $dbNameEnv . PHP_EOL;
echo 'DB_NAME (conexion real): ' . $dbNameReal . PHP_EOL;
if (stripos($dbHostActivo, 'localhost') !== false || stripos($dbHostActivo, '127.0.0.1') !== false) {
    echo '[ADVERTENCIA] Estas apuntando a base local.' . PHP_EOL;
}
echo str_repeat('-', 50) . PHP_EOL;

$sql = "SELECT id_institucion, logo_ifts, logo_cloudinary_public_id
    FROM institucion
    WHERE logo_ifts IS NOT NULL AND TRIM(logo_ifts) <> ''";
$params = [];

if ($onlyId !== null && $onlyId > 0) {
    $sql .= ' AND id_institucion = ?';
    $params[] = $onlyId;
}

$sql .= ' ORDER BY id_institucion ASC';

if ($limit !== null && $limit > 0) {
    $sql .= ' LIMIT ' . $limit;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Registros candidatos: " . count($rows) . PHP_EOL;
echo $dryRun ? "Modo: DRY-RUN (sin actualizar DB)" . PHP_EOL : "Modo: MIGRACION REAL" . PHP_EOL;

$stats = [
    'ok' => 0,
    'skip_cloudinary' => 0,
    'skip_vacio' => 0,
    'skip_formato' => 0,
    'error' => 0,
];

$updateStmt = $pdo->prepare(
    'UPDATE institucion
    SET logo_ifts = ?, logo_cloudinary_public_id = ?
     WHERE id_institucion = ?'
);

$backfillStmt = $pdo->prepare(
    'UPDATE institucion
    SET logo_cloudinary_public_id = ?
     WHERE id_institucion = ?'
);

foreach ($rows as $row) {
    $id = (int)$row['id_institucion'];
    $logo = trim((string)$row['logo_ifts']);

    if ($logo === '') {
        $stats['skip_vacio']++;
        echo "[SKIP] IFTS {$id}: logo vacio" . PHP_EOL;
        continue;
    }

    if (strpos($logo, 'res.cloudinary.com') !== false) {
        // Si ya estaba en Cloudinary, aprovechar para completar metadata faltante
        $existingPublicId = trim((string)($row['logo_cloudinary_public_id'] ?? ''));

        if ($existingPublicId === '') {
            $parsedPublicId = $cloudinary->extractPublicIdFromUrl($logo);
            if ($parsedPublicId !== null && !$dryRun) {
                $backfillStmt->execute([$parsedPublicId, $id]);
            }
            echo "[SKIP/BACKFILL] IFTS {$id}: ya estaba en Cloudinary, public_id " . ($parsedPublicId ? 'completado' : 'no detectado') . PHP_EOL;
        } else {
            echo "[SKIP] IFTS {$id}: ya esta en Cloudinary" . PHP_EOL;
        }

        $stats['skip_cloudinary']++;
        continue;
    }

    $tmpFile = null;

    try {
        $tmpFile = prepararArchivoTemporal($logo, $id);

        if ($tmpFile === null) {
            $stats['skip_formato']++;
            echo "[SKIP] IFTS {$id}: formato no soportado" . PHP_EOL;
            continue;
        }

        $upload = $cloudinary->upload($tmpFile, $folderLogo, 'image');
        if (empty($upload['success'])) {
            $stats['error']++;
            $msg = $upload['error'] ?? $upload['message'] ?? 'error desconocido';
            echo "[ERROR] IFTS {$id}: {$msg}" . PHP_EOL;
            continue;
        }

        $newUrl = (string)($upload['url'] ?? '');
        $newPublicId = (string)($upload['public_id'] ?? '');
        if ($newUrl === '') {
            $stats['error']++;
            echo "[ERROR] IFTS {$id}: Cloudinary no devolvio URL" . PHP_EOL;
            continue;
        }

        if ($newPublicId === '') {
            $stats['error']++;
            echo "[ERROR] IFTS {$id}: Cloudinary no devolvio public_id" . PHP_EOL;
            continue;
        }

        if (!$dryRun) {
            $updateStmt->execute([$newUrl, $newPublicId, $id]);
        }

        $stats['ok']++;
        echo "[OK] IFTS {$id}: migrado" . PHP_EOL;
    } catch (Throwable $e) {
        $stats['error']++;
        echo "[ERROR] IFTS {$id}: " . $e->getMessage() . PHP_EOL;
    } finally {
        if ($tmpFile && is_file($tmpFile)) {
            @unlink($tmpFile);
        }
    }
}

echo PHP_EOL . "Resumen:" . PHP_EOL;
foreach ($stats as $k => $v) {
    echo "- {$k}: {$v}" . PHP_EOL;
}

function prepararArchivoTemporal(string $logo, int $id): ?string
{
    // Caso 1: data URI base64
    if (strpos($logo, 'data:image/') === 0) {
        return dataUriATemporal($logo, $id);
    }

    // Caso 2: URL http(s)
    if (preg_match('/^https?:\/\//i', $logo)) {
        return urlATemporal($logo, $id);
    }

    return null;
}

function dataUriATemporal(string $dataUri, int $id): ?string
{
    if (!preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.*)$/s', $dataUri, $m)) {
        return null;
    }

    $mime = strtolower($m[1]);
    $data = $m[2];

    // Algunos base64 llegan con espacios
    $data = str_replace(' ', '+', $data);
    $binary = base64_decode($data, true);
    if ($binary === false) {
        return null;
    }

    $ext = mimeAExtension($mime);
    $tmp = tempnam(sys_get_temp_dir(), 'logo_' . $id . '_');
    if ($tmp === false) {
        return null;
    }

    $tmpWithExt = $tmp . '.' . $ext;
    if (file_put_contents($tmpWithExt, $binary) === false) {
        @unlink($tmp);
        return null;
    }

    @unlink($tmp);
    return $tmpWithExt;
}

function urlATemporal(string $url, int $id): ?string
{
    $context = stream_context_create([
        'http' => [
            'timeout' => 25,
            'follow_location' => 1,
            'user_agent' => 'ComunidadIFTS-Migrador/1.0',
        ]
    ]);

    $binary = @file_get_contents($url, false, $context);
    if ($binary === false || $binary === '') {
        return null;
    }

    $tmp = tempnam(sys_get_temp_dir(), 'logo_url_' . $id . '_');
    if ($tmp === false) {
        return null;
    }

    $tmpWithExt = $tmp . '.jpg';
    if (file_put_contents($tmpWithExt, $binary) === false) {
        @unlink($tmp);
        return null;
    }

    @unlink($tmp);
    return $tmpWithExt;
}

function mimeAExtension(string $mime): string
{
    $map = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/svg+xml' => 'svg',
    ];

    return $map[$mime] ?? 'jpg';
}

/**
 * Carga variables de entorno desde un archivo .env sin tocar el archivo principal.
 * Se ejecuta antes de Database para que Dotenv no las sobreescriba.
 */
function cargarEnvDesdeArchivo(string $envFile): void
{
    $fullPath = __DIR__ . '/../' . ltrim($envFile, '/\\');
    if (!is_file($fullPath)) {
        throw new RuntimeException("No existe el archivo de entorno: {$fullPath}");
    }

    $lines = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new RuntimeException("No se pudo leer el archivo de entorno: {$fullPath}");
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        // Quitar comillas envolventes si existen
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}
