<?php

/**
 * Reconcilia recursos de Cloudinary contra referencias reales en base de datos.
 *
 * Uso recomendado:
 *   php scripts/reconciliar_cloudinary_general.php
 *   php scripts/reconciliar_cloudinary_general.php --dry-run
 *   php scripts/reconciliar_cloudinary_general.php --section=instituciones_logo
 *   php scripts/reconciliar_cloudinary_general.php --section=cv_postulaciones --max-results=200
 *   php scripts/reconciliar_cloudinary_general.php --env-file=.env.production --delete --limit-delete=20
 *
 * Por defecto trabaja en modo solo lectura. Solo borra si se pasa --delete.
 */

$options = getopt('', ['dry-run', 'delete', 'env-file::', 'max-results::', 'limit-delete::', 'section::']);

$deleteMode = array_key_exists('delete', $options);
$dryRun = !$deleteMode || array_key_exists('dry-run', $options);
$envFile = isset($options['env-file']) ? (string)$options['env-file'] : null;
$maxResults = isset($options['max-results']) ? max(1, (int)$options['max-results']) : 500;
$limitDelete = isset($options['limit-delete']) ? max(0, (int)$options['limit-delete']) : null;
$sectionFilter = isset($options['section']) ? trim((string)$options['section']) : '';

if ($envFile !== null && $envFile !== '') {
    cargarEnvDesdeArchivo($envFile);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/CloudinaryService.php';

$mediaFolders = require __DIR__ . '/../config/media-folders.php';

$db = Database::getInstance();
$pdo = $db->getConnection();
$cloudinary = new CloudinaryService($mediaFolders['base'] ?? 'ComunidadIFTS');

$sections = buildSections($mediaFolders);

if ($sectionFilter !== '') {
    if (!isset($sections[$sectionFilter])) {
        throw new RuntimeException('Section invalida. Opciones: ' . implode(', ', array_keys($sections)));
    }

    $sections = [$sectionFilter => $sections[$sectionFilter]];
}

$dbHostActivo = (string)($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'N/A');
$dbNameEnv = (string)($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'N/A');
$dbNameReal = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();

echo 'DB_HOST activo: ' . $dbHostActivo . PHP_EOL;
echo 'DB_NAME (.env): ' . $dbNameEnv . PHP_EOL;
echo 'DB_NAME (conexion real): ' . $dbNameReal . PHP_EOL;
if (stripos($dbHostActivo, 'localhost') !== false || stripos($dbHostActivo, '127.0.0.1') !== false) {
    echo '[ADVERTENCIA] Estas apuntando a base local.' . PHP_EOL;
}
echo $dryRun ? 'Modo: SOLO LECTURA / DRY-RUN' . PHP_EOL : 'Modo: LIMPIEZA REAL' . PHP_EOL;
echo 'Secciones evaluadas: ' . implode(', ', array_keys($sections)) . PHP_EOL;
echo str_repeat('=', 72) . PHP_EOL;

$globalStats = [
    'sections' => count($sections),
    'resources_cloudinary' => 0,
    'public_ids_en_uso' => 0,
    'orphans' => 0,
    'delete_ok' => 0,
    'delete_error' => 0,
    'delete_skipped_dry_run' => 0,
];

foreach ($sections as $sectionKey => $section) {
    $usedPublicIds = $section['collector']($pdo, $cloudinary);
    $list = $cloudinary->listByFolder($section['folder'], $section['resource_type'], $maxResults);

    echo PHP_EOL . '[' . $sectionKey . ']' . PHP_EOL;
    echo 'Descripcion: ' . $section['description'] . PHP_EOL;
    echo 'Carpeta Cloudinary: ' . $section['folder'] . PHP_EOL;
    echo 'Tipo de recurso: ' . $section['resource_type'] . PHP_EOL;

    if (empty($list['success'])) {
        $msg = $list['error'] ?? $list['message'] ?? 'No se pudo listar Cloudinary.';
        echo '[ERROR] ' . $msg . PHP_EOL;
        echo str_repeat('-', 72) . PHP_EOL;
        continue;
    }

    $resources = $list['resources'] ?? [];
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

    $globalStats['resources_cloudinary'] += count($resources);
    $globalStats['public_ids_en_uso'] += count($usedPublicIds);
    $globalStats['orphans'] += count($orphans);

    echo 'Public IDs en uso: ' . count($usedPublicIds) . PHP_EOL;
    echo 'Recursos en Cloudinary: ' . count($resources) . PHP_EOL;
    echo 'Candidatos huerfanos: ' . count($orphans) . PHP_EOL;

    if (empty($orphans)) {
        echo 'Sin huerfanos detectados.' . PHP_EOL;
        echo str_repeat('-', 72) . PHP_EOL;
        continue;
    }

    $orphansToProcess = $orphans;
    if ($limitDelete !== null) {
        $orphansToProcess = array_slice($orphansToProcess, 0, $limitDelete);
        echo 'Aplicando limit-delete: ' . $limitDelete . PHP_EOL;
    }

    echo 'Primeros candidatos:' . PHP_EOL;
    foreach (array_slice($orphansToProcess, 0, 15) as $publicId) {
        echo '- ' . $publicId . PHP_EOL;
    }

    foreach ($orphansToProcess as $publicId) {
        if ($dryRun) {
            $globalStats['delete_skipped_dry_run']++;
            echo '[DRY-RUN] Se borraria: ' . $publicId . PHP_EOL;
            continue;
        }

        $deleted = $cloudinary->delete($publicId, $section['resource_type']);
        if (!empty($deleted['success'])) {
            $globalStats['delete_ok']++;
            echo '[OK] Borrado: ' . $publicId . PHP_EOL;
        } else {
            $globalStats['delete_error']++;
            $msg = $deleted['error'] ?? $deleted['message'] ?? 'error desconocido';
            echo '[ERROR] No se pudo borrar ' . $publicId . ': ' . $msg . PHP_EOL;
        }
    }

    echo str_repeat('-', 72) . PHP_EOL;
}

echo PHP_EOL . 'Resumen global:' . PHP_EOL;
echo '- sections: ' . $globalStats['sections'] . PHP_EOL;
echo '- resources_cloudinary: ' . $globalStats['resources_cloudinary'] . PHP_EOL;
echo '- public_ids_en_uso: ' . $globalStats['public_ids_en_uso'] . PHP_EOL;
echo '- orphans: ' . $globalStats['orphans'] . PHP_EOL;
echo '- delete_ok: ' . $globalStats['delete_ok'] . PHP_EOL;
echo '- delete_error: ' . $globalStats['delete_error'] . PHP_EOL;
echo '- delete_skipped_dry_run: ' . $globalStats['delete_skipped_dry_run'] . PHP_EOL;

if ($maxResults < 500) {
    echo PHP_EOL . '[Nota] max-results bajo puede dejar recursos sin revisar.' . PHP_EOL;
}

function buildSections(array $mediaFolders): array
{
    $base = $mediaFolders['base'] ?? 'ComunidadIFTS';

    return [
        'instituciones_logo' => [
            'description' => 'Logos de instituciones',
            'folder' => $mediaFolders['instituciones']['logo'] ?? ($base . '/logoIFTS'),
            'resource_type' => 'image',
            'collector' => static function (PDO $pdo, CloudinaryService $cloudinary): array {
                return collectPublicIdsFromQuery(
                    $pdo,
                    $cloudinary,
                    'institucion',
                    'SELECT logo_ifts AS asset_url, logo_cloudinary_public_id AS public_id FROM institucion WHERE logo_ifts IS NOT NULL OR logo_cloudinary_public_id IS NOT NULL'
                );
            },
        ],
        'carrusel_home' => [
            'description' => 'Slides del carrusel principal',
            'folder' => $mediaFolders['carrusel'] ?? ($base . '/carrusel'),
            'resource_type' => 'image',
            'collector' => static function (PDO $pdo, CloudinaryService $cloudinary): array {
                return collectPublicIdsFromQuery(
                    $pdo,
                    $cloudinary,
                    'carrousel',
                    'SELECT foto_perfil_url AS asset_url, foto_perfil_public_id AS public_id FROM carrousel WHERE cancelado = 0 AND (foto_perfil_url IS NOT NULL OR foto_perfil_public_id IS NOT NULL)'
                );
            },
        ],
        'navbar_logo' => [
            'description' => 'Logo del navbar',
            'folder' => $mediaFolders['navbar']['logo'] ?? ($base . '/navbar'),
            'resource_type' => 'image',
            'collector' => static function (PDO $pdo, CloudinaryService $cloudinary): array {
                return collectPublicIdsFromQuery(
                    $pdo,
                    $cloudinary,
                    'navbar',
                    'SELECT foto_perfil_url AS asset_url, foto_perfil_public_id AS public_id FROM navbar WHERE cancelado = 0 AND (foto_perfil_url IS NOT NULL OR foto_perfil_public_id IS NOT NULL)'
                );
            },
        ],
        'sidebar_logo' => [
            'description' => 'Logo del sidebar',
            'folder' => $mediaFolders['sidebar']['logo'] ?? ($base . '/sidebar'),
            'resource_type' => 'image',
            'collector' => static function (PDO $pdo, CloudinaryService $cloudinary): array {
                return collectPublicIdsFromQuery(
                    $pdo,
                    $cloudinary,
                    'sidebar',
                    'SELECT foto_perfil_url AS asset_url, foto_perfil_public_id AS public_id FROM sidebar WHERE cancelado = 0 AND (foto_perfil_url IS NOT NULL OR foto_perfil_public_id IS NOT NULL)'
                );
            },
        ],
        'footer_branding_logo' => [
            'description' => 'Logo del footer branding',
            'folder' => $mediaFolders['footer_branding']['logo'] ?? ($base . '/footer-branding'),
            'resource_type' => 'image',
            'collector' => static function (PDO $pdo, CloudinaryService $cloudinary): array {
                return collectPublicIdsFromQuery(
                    $pdo,
                    $cloudinary,
                    'footer_branding',
                    'SELECT foto_perfil_url AS asset_url, foto_perfil_public_id AS public_id FROM footer_branding WHERE cancelado = 0 AND (foto_perfil_url IS NOT NULL OR foto_perfil_public_id IS NOT NULL)'
                );
            },
        ],
        'perfiles_foto' => [
            'description' => 'Fotos de perfil de personas',
            'folder' => $mediaFolders['perfiles']['foto'] ?? ($base . '/perfiles'),
            'resource_type' => 'image',
            'collector' => static function (PDO $pdo, CloudinaryService $cloudinary): array {
                return collectPublicIdsFromQuery(
                    $pdo,
                    $cloudinary,
                    'persona',
                    'SELECT foto_perfil_url AS asset_url, foto_perfil_public_id AS public_id FROM persona WHERE cancelado = 0 AND (foto_perfil_url IS NOT NULL OR foto_perfil_public_id IS NOT NULL)'
                );
            },
        ],
        'tienda_carrusel' => [
            'description' => 'Slides del carrusel de tienda',
            'folder' => $mediaFolders['tienda']['carrusel'] ?? ($base . '/tienda/carrusel'),
            'resource_type' => 'image',
            'collector' => static function (PDO $pdo, CloudinaryService $cloudinary): array {
                return collectPublicIdsFromQuery(
                    $pdo,
                    $cloudinary,
                    'tienda_carrousel',
                    'SELECT foto_perfil_url AS asset_url, foto_perfil_public_id AS public_id FROM tienda_carrousel WHERE cancelado = 0 AND (foto_perfil_url IS NOT NULL OR foto_perfil_public_id IS NOT NULL)'
                );
            },
        ],
        'tienda_galeria' => [
            'description' => 'Productos/galeria de tienda',
            'folder' => $mediaFolders['tienda']['galeria'] ?? ($base . '/tienda/galeria'),
            'resource_type' => 'image',
            'collector' => static function (PDO $pdo, CloudinaryService $cloudinary): array {
                return collectPublicIdsFromQuery(
                    $pdo,
                    $cloudinary,
                    'tienda_producto',
                    'SELECT foto_perfil_url AS asset_url, foto_perfil_public_id AS public_id FROM tienda_producto WHERE cancelado = 0 AND (foto_perfil_url IS NOT NULL OR foto_perfil_public_id IS NOT NULL)'
                );
            },
        ],
        'cv_postulaciones' => [
            'description' => 'CVs de postulaciones',
            'folder' => $base . '/CVs/CVs',
            'resource_type' => 'raw',
            'collector' => static function (PDO $pdo, CloudinaryService $cloudinary): array {
                return collectPublicIdsFromQuery(
                    $pdo,
                    $cloudinary,
                    'postulacion',
                    'SELECT cv_url AS asset_url, cv_public_id AS public_id FROM postulacion WHERE cancelado = 0 AND (cv_url IS NOT NULL OR cv_public_id IS NOT NULL)'
                );
            },
        ],
    ];
}

function collectPublicIdsFromQuery(PDO $pdo, CloudinaryService $cloudinary, string $tableName, string $sql): array
{
    if (!tableExists($pdo, $tableName)) {
        return [];
    }

    $stmt = $pdo->query($sql);
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    $usedPublicIds = [];

    foreach ($rows as $row) {
        $storedPublicId = trim((string)($row['public_id'] ?? ''));
        if ($storedPublicId !== '') {
            $usedPublicIds[$storedPublicId] = true;
        }

        $assetUrl = trim((string)($row['asset_url'] ?? ''));
        if ($assetUrl !== '' && strpos($assetUrl, 'res.cloudinary.com') !== false) {
            $parsed = $cloudinary->extractPublicIdFromUrl($assetUrl);
            if ($parsed !== null && $parsed !== '') {
                $usedPublicIds[$parsed] = true;
            }
        }
    }

    return $usedPublicIds;
}

function tableExists(PDO $pdo, string $tableName): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$tableName]);
    return (bool)$stmt->fetchColumn();
}

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