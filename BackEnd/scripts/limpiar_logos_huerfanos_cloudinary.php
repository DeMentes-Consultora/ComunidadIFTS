<?php

/**
 * Elimina logos huerfanos de Cloudinary en la carpeta de instituciones.
 *
 * Compara los recursos existentes en Cloudinary con los logos actualmente
 * referenciados por la tabla `institucion`.
 *
 * Uso:
 *   php scripts/limpiar_logos_huerfanos_cloudinary.php --dry-run
 *   php scripts/limpiar_logos_huerfanos_cloudinary.php --env-file=.env.production --dry-run
 *   php scripts/limpiar_logos_huerfanos_cloudinary.php --max-results=500
 *   php scripts/limpiar_logos_huerfanos_cloudinary.php --limit-delete=20
 */

$options = getopt('', ['dry-run', 'env-file::', 'max-results::', 'limit-delete::']);

$dryRun = array_key_exists('dry-run', $options);
$envFile = isset($options['env-file']) ? (string)$options['env-file'] : null;
$maxResults = isset($options['max-results']) ? max(1, (int)$options['max-results']) : 500;
$limitDelete = isset($options['limit-delete']) ? max(0, (int)$options['limit-delete']) : null;

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
echo 'Carpeta Cloudinary evaluada: ' . $folderLogo . PHP_EOL;
echo $dryRun ? 'Modo: DRY-RUN (sin borrar en Cloudinary)' . PHP_EOL : 'Modo: LIMPIEZA REAL' . PHP_EOL;
echo str_repeat('-', 60) . PHP_EOL;

$stmt = $pdo->query(
    "SELECT id_institucion, logo_ifts, logo_cloudinary_public_id
     FROM institucion"
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$usedPublicIds = [];
foreach ($rows as $row) {
    $storedPublicId = trim((string)($row['logo_cloudinary_public_id'] ?? ''));
    if ($storedPublicId !== '') {
        $usedPublicIds[$storedPublicId] = true;
    }

    $logoUrl = trim((string)($row['logo_ifts'] ?? ''));
    if ($logoUrl !== '' && strpos($logoUrl, 'res.cloudinary.com') !== false) {
        $parsed = $cloudinary->extractPublicIdFromUrl($logoUrl);
        if ($parsed !== null && $parsed !== '') {
            $usedPublicIds[$parsed] = true;
        }
    }
}

$list = $cloudinary->listByFolder($folderLogo, 'image', $maxResults);
if (empty($list['success'])) {
    $msg = $list['error'] ?? $list['message'] ?? 'No se pudo listar Cloudinary.';
    throw new RuntimeException($msg);
}

$resources = $list['resources'] ?? [];
$totalCloudinary = count($resources);

$orphans = [];
foreach ($resources as $resource) {
    $publicId = trim((string)($resource['public_id'] ?? ''));
    if ($publicId === '') {
        continue;
    }

    if (!isset($usedPublicIds[$publicId])) {
        $orphans[] = $publicId;
    }
}

echo 'Instituciones leidas: ' . count($rows) . PHP_EOL;
echo 'Public IDs en uso (DB + URL parseada): ' . count($usedPublicIds) . PHP_EOL;
echo 'Recursos en Cloudinary (carpeta): ' . $totalCloudinary . PHP_EOL;
echo 'Candidatos huerfanos: ' . count($orphans) . PHP_EOL;

if (empty($orphans)) {
    echo PHP_EOL . 'No hay logos huerfanos para eliminar.' . PHP_EOL;
    exit(0);
}

if ($limitDelete !== null) {
    $orphans = array_slice($orphans, 0, $limitDelete);
    echo 'Aplicando limite de borrado: ' . $limitDelete . PHP_EOL;
}

echo PHP_EOL . 'Primeros candidatos:' . PHP_EOL;
foreach (array_slice($orphans, 0, 15) as $publicId) {
    echo '- ' . $publicId . PHP_EOL;
}

$stats = [
    'delete_ok' => 0,
    'delete_error' => 0,
    'delete_skipped_dry_run' => 0,
];

echo PHP_EOL;
foreach ($orphans as $publicId) {
    if ($dryRun) {
        $stats['delete_skipped_dry_run']++;
        echo '[DRY-RUN] Se borraria: ' . $publicId . PHP_EOL;
        continue;
    }

    $deleted = $cloudinary->delete($publicId, 'image');
    if (!empty($deleted['success'])) {
        $stats['delete_ok']++;
        echo '[OK] Borrado: ' . $publicId . PHP_EOL;
    } else {
        $stats['delete_error']++;
        $msg = $deleted['error'] ?? $deleted['message'] ?? 'error desconocido';
        echo '[ERROR] No se pudo borrar ' . $publicId . ': ' . $msg . PHP_EOL;
    }
}

echo PHP_EOL . 'Resumen:' . PHP_EOL;
echo '- delete_ok: ' . $stats['delete_ok'] . PHP_EOL;
echo '- delete_error: ' . $stats['delete_error'] . PHP_EOL;
echo '- delete_skipped_dry_run: ' . $stats['delete_skipped_dry_run'] . PHP_EOL;

if ($maxResults < 500) {
    echo PHP_EOL . '[Nota] max-results bajo puede dejar recursos sin revisar.' . PHP_EOL;
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

        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}
