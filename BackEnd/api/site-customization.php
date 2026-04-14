<?php

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SiteCustomizationModel.php';
require_once __DIR__ . '/../services/CloudinaryService.php';

header('Content-Type: application/json');

function respond(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function requireAdminSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        respond(401, [
            'success' => false,
            'message' => 'No autenticado',
        ]);
    }

    $idRol = (int)($_SESSION['id_rol'] ?? 0);
    if ($idRol !== 1) {
        respond(403, [
            'success' => false,
            'message' => 'No tienes permisos para administrar esta seccion',
        ]);
    }
}

function parsePayload(): array
{
    $rawPayload = $_POST['payload'] ?? null;
    if (is_string($rawPayload) && trim($rawPayload) !== '') {
        $decoded = json_decode($rawPayload, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
    }

    $rawBody = file_get_contents('php://input');
    if (is_string($rawBody) && trim($rawBody) !== '') {
        $decoded = json_decode($rawBody, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
    }

    return [];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $scope = trim((string)($_GET['scope'] ?? 'public'));

        if ($scope === 'admin') {
            requireAdminSession();
        }

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        respond(200, [
            'success' => true,
            'data' => $scope === 'admin'
                ? SiteCustomizationModel::obtenerConfiguracionAdmin($pdo)
                : SiteCustomizationModel::obtenerConfiguracionPublica($pdo),
        ]);
    } catch (Throwable $e) {
        respond(500, [
            'success' => false,
            'message' => 'Error interno del servidor',
            'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, [
        'success' => false,
        'message' => 'Metodo no permitido',
    ]);
}

try {
    requireAdminSession();

    $payload = parsePayload();
    $navbarPayload = is_array($payload['navbar'] ?? null) ? $payload['navbar'] : [];
    $carouselPayload = is_array($payload['carousel'] ?? null) ? $payload['carousel'] : [];

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $configActual = SiteCustomizationModel::obtenerConfiguracionAdmin($pdo);

    $mediaFolders = require __DIR__ . '/../config/media-folders.php';
    $cloudinary = new CloudinaryService($mediaFolders['base'] ?? 'ComunidadIFTS');

    $navbarActual = $configActual['navbar'];
    $navbarLogoPublicId = trim((string)($navbarActual['logo_public_id'] ?? ''));
    $navbarLogoUrl = trim((string)($navbarActual['logo_url'] ?? ''));
    $removeNavbarLogo = !empty($navbarPayload['remove_logo']);

    if ($removeNavbarLogo && $navbarLogoPublicId !== '') {
        $cloudinary->delete($navbarLogoPublicId, 'image');
        $navbarLogoPublicId = '';
        $navbarLogoUrl = '';
    }

    if (!empty($_FILES['navbar_logo']) && (int)($_FILES['navbar_logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $folderNavbar = $mediaFolders['navbar']['logo'] ?? 'ComunidadIFTS/navbar';
        $uploadLogo = $cloudinary->uploadFromFileArray($_FILES['navbar_logo'], $folderNavbar, 'image');
        if (empty($uploadLogo['success'])) {
            respond(500, [
                'success' => false,
                'message' => $uploadLogo['message'] ?? 'No fue posible subir el logo del navbar',
                'error' => ($_ENV['APP_DEBUG'] ?? false) ? ($uploadLogo['error'] ?? null) : null,
            ]);
        }

        if ($navbarLogoPublicId !== '' && $navbarLogoPublicId !== (string)($uploadLogo['public_id'] ?? '')) {
            $cloudinary->delete($navbarLogoPublicId, 'image');
        }

        $navbarLogoPublicId = trim((string)($uploadLogo['public_id'] ?? ''));
        $navbarLogoUrl = trim((string)($uploadLogo['url'] ?? ''));
    }

    $slidesActuales = [];
    foreach ($configActual['carousel'] as $slideActual) {
        $slidesActuales[(int)$slideActual['id_carrousel']] = $slideActual;
    }

    $slidesSanitizados = [];
    $idsConservados = [];

    foreach ($carouselPayload as $index => $slide) {
        if (!is_array($slide)) {
            continue;
        }

        $idCarousel = isset($slide['id_carrousel']) ? (int)$slide['id_carrousel'] : 0;
        $slideActual = $idCarousel > 0 && isset($slidesActuales[$idCarousel]) ? $slidesActuales[$idCarousel] : null;
        $clientKey = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($slide['client_key'] ?? ('slide_' . $index)));
        $fileKey = 'carousel_image_' . $clientKey;

        $slideImageUrl = trim((string)($slideActual['foto_perfil_url'] ?? ''));
        $slideImagePublicId = trim((string)($slideActual['foto_perfil_public_id'] ?? ''));

        if (!empty($slide['remove_image']) && $slideImagePublicId !== '') {
            $cloudinary->delete($slideImagePublicId, 'image');
            $slideImageUrl = '';
            $slideImagePublicId = '';
        }

        if (!empty($_FILES[$fileKey]) && (int)($_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $folderCarrusel = $mediaFolders['carrusel'] ?? 'ComunidadIFTS/carrusel';
            $uploadSlide = $cloudinary->uploadFromFileArray($_FILES[$fileKey], $folderCarrusel, 'image');
            if (empty($uploadSlide['success'])) {
                respond(500, [
                    'success' => false,
                    'message' => $uploadSlide['message'] ?? 'No fue posible subir una imagen del carrusel',
                    'error' => ($_ENV['APP_DEBUG'] ?? false) ? ($uploadSlide['error'] ?? null) : null,
                ]);
            }

            if ($slideImagePublicId !== '' && $slideImagePublicId !== (string)($uploadSlide['public_id'] ?? '')) {
                $cloudinary->delete($slideImagePublicId, 'image');
            }

            $slideImageUrl = trim((string)($uploadSlide['url'] ?? ''));
            $slideImagePublicId = trim((string)($uploadSlide['public_id'] ?? ''));
        }

        $slidesSanitizados[] = [
            'id_carrousel' => $idCarousel > 0 ? $idCarousel : null,
            'titulo' => trim((string)($slide['titulo'] ?? '')),
            'descripcion' => trim((string)($slide['descripcion'] ?? '')),
            'enlace' => trim((string)($slide['enlace'] ?? '')),
            'orden_visual' => isset($slide['orden_visual']) ? (int)$slide['orden_visual'] : ($index + 1),
            'foto_perfil_url' => $slideImageUrl !== '' ? $slideImageUrl : null,
            'foto_perfil_public_id' => $slideImagePublicId !== '' ? $slideImagePublicId : null,
            'habilitado' => !empty($slide['habilitado']) ? 1 : 0,
        ];

        if ($idCarousel > 0) {
            $idsConservados[] = $idCarousel;
        }
    }

    foreach ($slidesActuales as $existingId => $existingSlide) {
        if (in_array($existingId, $idsConservados, true)) {
            continue;
        }

        $publicId = trim((string)($existingSlide['foto_perfil_public_id'] ?? ''));
        if ($publicId !== '') {
            $cloudinary->delete($publicId, 'image');
        }
    }

    $pdo->beginTransaction();

    SiteCustomizationModel::guardarNavbar($pdo, [
        'brand_text' => $navbarPayload['brand_text'] ?? $navbarActual['brand_text'] ?? '',
        'logo_url' => $navbarLogoUrl !== '' ? $navbarLogoUrl : null,
        'logo_public_id' => $navbarLogoPublicId !== '' ? $navbarLogoPublicId : null,
        'habilitado' => 1,
    ]);

    SiteCustomizationModel::guardarCarrusel($pdo, $slidesSanitizados);

    $pdo->commit();

    respond(200, [
        'success' => true,
        'message' => 'Configuracion del sitio actualizada correctamente',
        'data' => SiteCustomizationModel::obtenerConfiguracionAdmin($pdo),
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    respond(500, [
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
    ]);
}