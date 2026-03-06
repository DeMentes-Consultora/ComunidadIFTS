<?php
/**
 * Migracion de logos a Cloudinary por HTTP (para hosting sin CLI/SSH).
 *
 * Seguridad:
 * - Requiere token via ?token=... (definir MIGRATION_TOKEN en .env.production)
 *
 * Ejemplos:
 * - Dry-run:  /api/migrar-logos-cloudinary.php?token=TU_TOKEN&dry_run=1&limit=20
 * - Real:     /api/migrar-logos-cloudinary.php?token=TU_TOKEN&limit=20&offset=0
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/CloudinaryService.php';

header('Content-Type: application/json; charset=utf-8');

$expectedToken = $_ENV['MIGRATION_TOKEN'] ?? '';
$token = $_GET['token'] ?? '';

if ($expectedToken === '' || !hash_equals($expectedToken, $token)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Token invalido o no configurado.',
    ]);
    exit;
}

$dryRun = isset($_GET['dry_run']) && (string)$_GET['dry_run'] === '1';
$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 20;
$offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
$onlyId = isset($_GET['only_id']) ? max(0, (int)$_GET['only_id']) : 0;

$mediaFolders = require __DIR__ . '/../config/media-folders.php';
$db = Database::getInstance();
$pdo = $db->getConnection();
$cloudinary = new CloudinaryService($mediaFolders['base'] ?? 'ComunidadIFTS');
$folderLogo = $mediaFolders['instituciones']['logo'] ?? 'ComunidadIFTS/logoIFTS';

$sql = "SELECT id_institucion, logo_ifts, logo_cloudinary_public_id
        FROM institucion
        WHERE logo_ifts IS NOT NULL
          AND TRIM(logo_ifts) <> ''";
$params = [];

if ($onlyId > 0) {
    $sql .= ' AND id_institucion = ?';
    $params[] = $onlyId;
}

$sql .= ' ORDER BY id_institucion ASC LIMIT ? OFFSET ?';
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params as $idx => $value) {
    $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($idx + 1, $value, $paramType);
}
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stats = [
    'ok' => 0,
    'skip_cloudinary' => 0,
    'skip_vacio' => 0,
    'skip_formato' => 0,
    'error' => 0,
];

$details = [];

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
        $details[] = ['id' => $id, 'status' => 'skip', 'reason' => 'logo vacio'];
        continue;
    }

    if (strpos($logo, 'res.cloudinary.com') !== false) {
        $existingPublicId = trim((string)($row['logo_cloudinary_public_id'] ?? ''));
        if ($existingPublicId === '') {
            $parsedPublicId = $cloudinary->extractPublicIdFromUrl($logo);
            if ($parsedPublicId !== null && !$dryRun) {
                $backfillStmt->execute([$parsedPublicId, $id]);
            }
            $details[] = [
                'id' => $id,
                'status' => 'skip_backfill',
                'public_id' => $parsedPublicId,
            ];
        } else {
            $details[] = ['id' => $id, 'status' => 'skip', 'reason' => 'ya en cloudinary'];
        }

        $stats['skip_cloudinary']++;
        continue;
    }

    $tmpFile = null;

    try {
        $tmpFile = prepararArchivoTemporal($logo, $id);

        if ($tmpFile === null) {
            $stats['skip_formato']++;
            $details[] = ['id' => $id, 'status' => 'skip', 'reason' => 'formato no soportado'];
            continue;
        }

        $upload = $cloudinary->upload($tmpFile, $folderLogo, 'image');
        if (empty($upload['success'])) {
            $stats['error']++;
            $details[] = [
                'id' => $id,
                'status' => 'error',
                'reason' => $upload['error'] ?? $upload['message'] ?? 'error subiendo',
            ];
            continue;
        }

        $newUrl = (string)($upload['url'] ?? '');
        $newPublicId = (string)($upload['public_id'] ?? '');

        if ($newUrl === '' || $newPublicId === '') {
            $stats['error']++;
            $details[] = [
                'id' => $id,
                'status' => 'error',
                'reason' => 'cloudinary sin url/public_id',
            ];
            continue;
        }

        if (!$dryRun) {
            $updateStmt->execute([$newUrl, $newPublicId, $id]);
        }

        $stats['ok']++;
        $details[] = [
            'id' => $id,
            'status' => 'ok',
            'url' => $newUrl,
            'public_id' => $newPublicId,
        ];
    } catch (Throwable $e) {
        $stats['error']++;
        $details[] = ['id' => $id, 'status' => 'error', 'reason' => $e->getMessage()];
    } finally {
        if ($tmpFile && is_file($tmpFile)) {
            @unlink($tmpFile);
        }
    }
}

echo json_encode([
    'success' => true,
    'dry_run' => $dryRun,
    'limit' => $limit,
    'offset' => $offset,
    'count' => count($rows),
    'stats' => $stats,
    'details' => $details,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

function prepararArchivoTemporal(string $logo, int $id): ?string
{
    if (strpos($logo, 'data:image/') === 0) {
        return dataUriATemporal($logo, $id);
    }

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
    $data = str_replace(' ', '+', $m[2]);
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
            'user_agent' => 'ComunidadIFTS-MigradorWeb/1.0',
        ],
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
