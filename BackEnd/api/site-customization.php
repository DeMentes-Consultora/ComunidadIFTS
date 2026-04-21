<?php

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SiteCustomizationModel.php';

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

function uploadErrorText(int $code): string
{
    switch ($code) {
        case UPLOAD_ERR_OK:
            return 'OK';
        case UPLOAD_ERR_INI_SIZE:
            return 'El archivo supera upload_max_filesize';
        case UPLOAD_ERR_FORM_SIZE:
            return 'El archivo supera MAX_FILE_SIZE del formulario';
        case UPLOAD_ERR_PARTIAL:
            return 'El archivo se subio parcialmente';
        case UPLOAD_ERR_NO_FILE:
            return 'No se envio archivo';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Falta carpeta temporal en servidor';
        case UPLOAD_ERR_CANT_WRITE:
            return 'No se pudo escribir archivo temporal';
        case UPLOAD_ERR_EXTENSION:
            return 'Una extension de PHP bloqueo la subida';
        default:
            return 'Error de subida desconocido';
    }
}

function procesarColeccionSlides(
    array $slidesPayload,
    array $slidesActuales,
    array $_files,
    string $filePrefix,
    string $folderDestino,
    $cloudinary
): array {
    $slidesSanitizados = [];
    $idsConservados = [];

    foreach ($slidesPayload as $index => $slide) {
        if (!is_array($slide)) {
            continue;
        }

        $idCarousel = isset($slide['id_carrousel']) ? (int)$slide['id_carrousel'] : 0;
        $slideActual = $idCarousel > 0 && isset($slidesActuales[$idCarousel]) ? $slidesActuales[$idCarousel] : null;
        $clientKey = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($slide['client_key'] ?? ('slide_' . $index)));
        $fileKey = $filePrefix . $clientKey;

        $slideImageUrl = trim((string)($slideActual['foto_perfil_url'] ?? ''));
        $slideImagePublicId = trim((string)($slideActual['foto_perfil_public_id'] ?? ''));

        if (!empty($slide['remove_image']) && $slideImagePublicId !== '') {
            $cloudinary->delete($slideImagePublicId, 'image');
            $slideImageUrl = '';
            $slideImagePublicId = '';
        }

        if (!empty($_files[$fileKey]) && (int)($_files[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $uploadSlide = $cloudinary->uploadFromFileArray($_files[$fileKey], $folderDestino, 'image');
            if (empty($uploadSlide['success'])) {
                respond(500, [
                    'success' => false,
                    'message' => $uploadSlide['message'] ?? 'No fue posible subir una imagen',
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

    return $slidesSanitizados;
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
        error_log('Error GET site-customization.php: ' . $e->getMessage());
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

    $cloudinaryServicePath = __DIR__ . '/../services/CloudinaryService.php';
    if (!is_file($cloudinaryServicePath)) {
        respond(500, [
            'success' => false,
            'message' => 'No se encontro el servicio de Cloudinary en el servidor',
        ]);
    }
    require_once $cloudinaryServicePath;

    $payload = parsePayload();
    $navbarPayload = is_array($payload['navbar'] ?? null) ? $payload['navbar'] : [];
    $sidebarPayload = is_array($payload['sidebar'] ?? null) ? $payload['sidebar'] : [];
    $footerBrandingPayload = is_array($payload['footer_branding'] ?? null) ? $payload['footer_branding'] : [];
    $carouselPayload = is_array($payload['carousel'] ?? null) ? $payload['carousel'] : [];
    $shopCarouselPayload = is_array($payload['shop_carousel'] ?? null) ? $payload['shop_carousel'] : [];
    $shopGalleryPayload = is_array($payload['shop_gallery'] ?? null) ? $payload['shop_gallery'] : [];

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $configActual = SiteCustomizationModel::obtenerConfiguracionAdmin($pdo);

    $mediaFolders = require __DIR__ . '/../config/media-folders.php';
    try {
        $cloudinary = new CloudinaryService($mediaFolders['base'] ?? 'ComunidadIFTS');
    } catch (Throwable $e) {
        error_log('Cloudinary init fallo (site-customization): ' . $e->getMessage());
        respond(500, [
            'success' => false,
            'message' => 'No se pudo inicializar Cloudinary en el servidor',
            'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
        ]);
    }

    $navbarActual = $configActual['navbar'];
    $navbarLogoPublicId = trim((string)($navbarActual['logo_public_id'] ?? ''));
    $navbarLogoUrl = trim((string)($navbarActual['logo_url'] ?? ''));
    $removeNavbarLogo = !empty($navbarPayload['remove_logo']);

    $sidebarActual = $configActual['sidebar'];
    $sidebarLogoPublicId = trim((string)($sidebarActual['logo_public_id'] ?? ''));
    $sidebarLogoUrl = trim((string)($sidebarActual['logo_url'] ?? ''));
    $removeSidebarLogo = !empty($sidebarPayload['remove_logo']);
    $navbarLogoSelected = !empty($navbarPayload['logo_selected']);
    $sidebarLogoSelected = !empty($sidebarPayload['logo_selected']);

    $footerBrandingActual = is_array($configActual['footer_branding'] ?? null) ? $configActual['footer_branding'] : [];
    $footerBrandingLogoPublicId = trim((string)($footerBrandingActual['logo_public_id'] ?? ''));
    $footerBrandingLogoUrl = trim((string)($footerBrandingActual['logo_url'] ?? ''));
    $removeFooterBrandingLogo = !empty($footerBrandingPayload['remove_logo']);
    $footerBrandingLogoSelected = !empty($footerBrandingPayload['logo_selected']);

    if ($removeNavbarLogo && $navbarLogoPublicId !== '') {
        $cloudinary->delete($navbarLogoPublicId, 'image');
        $navbarLogoPublicId = '';
        $navbarLogoUrl = '';
    }

    $navbarFileError = (int)($_FILES['navbar_logo']['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($navbarLogoSelected && $navbarFileError !== UPLOAD_ERR_OK) {
        respond(400, [
            'success' => false,
            'message' => 'Se selecciono un logo de navbar pero el servidor no recibio el archivo.',
            'details' => [
                'field' => 'navbar_logo',
                'upload_error_code' => $navbarFileError,
                'upload_error_text' => uploadErrorText($navbarFileError),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
        ]);
    }

    if (!empty($_FILES['navbar_logo']) && $navbarFileError === UPLOAD_ERR_OK) {
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

    if ($removeSidebarLogo && $sidebarLogoPublicId !== '') {
        $cloudinary->delete($sidebarLogoPublicId, 'image');
        $sidebarLogoPublicId = '';
        $sidebarLogoUrl = '';
    }

    $sidebarFileError = (int)($_FILES['sidebar_logo']['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($sidebarLogoSelected && $sidebarFileError !== UPLOAD_ERR_OK) {
        respond(400, [
            'success' => false,
            'message' => 'Se selecciono un logo de sidebar pero el servidor no recibio el archivo.',
            'details' => [
                'field' => 'sidebar_logo',
                'upload_error_code' => $sidebarFileError,
                'upload_error_text' => uploadErrorText($sidebarFileError),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
        ]);
    }

    if (!empty($_FILES['sidebar_logo']) && $sidebarFileError === UPLOAD_ERR_OK) {
        $folderSidebar = $mediaFolders['sidebar']['logo'] ?? 'ComunidadIFTS/sidebar';
        $uploadSidebarLogo = $cloudinary->uploadFromFileArray($_FILES['sidebar_logo'], $folderSidebar, 'image');
        if (empty($uploadSidebarLogo['success'])) {
            respond(500, [
                'success' => false,
                'message' => $uploadSidebarLogo['message'] ?? 'No fue posible subir el logo del sidebar',
                'error' => ($_ENV['APP_DEBUG'] ?? false) ? ($uploadSidebarLogo['error'] ?? null) : null,
            ]);
        }

        if ($sidebarLogoPublicId !== '' && $sidebarLogoPublicId !== (string)($uploadSidebarLogo['public_id'] ?? '')) {
            $cloudinary->delete($sidebarLogoPublicId, 'image');
        }

        $sidebarLogoPublicId = trim((string)($uploadSidebarLogo['public_id'] ?? ''));
        $sidebarLogoUrl = trim((string)($uploadSidebarLogo['url'] ?? ''));
    }

    if ($removeFooterBrandingLogo && $footerBrandingLogoPublicId !== '') {
        $cloudinary->delete($footerBrandingLogoPublicId, 'image');
        $footerBrandingLogoPublicId = '';
        $footerBrandingLogoUrl = '';
    }

    $footerBrandingFileError = (int)($_FILES['footer_branding_logo']['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($footerBrandingLogoSelected && $footerBrandingFileError !== UPLOAD_ERR_OK) {
        respond(400, [
            'success' => false,
            'message' => 'Se selecciono un logo de marca para footer pero el servidor no recibio el archivo.',
            'details' => [
                'field' => 'footer_branding_logo',
                'upload_error_code' => $footerBrandingFileError,
                'upload_error_text' => uploadErrorText($footerBrandingFileError),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
        ]);
    }

    if (!empty($_FILES['footer_branding_logo']) && $footerBrandingFileError === UPLOAD_ERR_OK) {
        $folderFooterBranding = $mediaFolders['footer_branding']['logo'] ?? 'ComunidadIFTS/footer-branding';
        $uploadFooterBrandingLogo = $cloudinary->uploadFromFileArray($_FILES['footer_branding_logo'], $folderFooterBranding, 'image');
        if (empty($uploadFooterBrandingLogo['success'])) {
            respond(500, [
                'success' => false,
                'message' => $uploadFooterBrandingLogo['message'] ?? 'No fue posible subir el logo del footer',
                'error' => ($_ENV['APP_DEBUG'] ?? false) ? ($uploadFooterBrandingLogo['error'] ?? null) : null,
            ]);
        }

        if ($footerBrandingLogoPublicId !== '' && $footerBrandingLogoPublicId !== (string)($uploadFooterBrandingLogo['public_id'] ?? '')) {
            $cloudinary->delete($footerBrandingLogoPublicId, 'image');
        }

        $footerBrandingLogoPublicId = trim((string)($uploadFooterBrandingLogo['public_id'] ?? ''));
        $footerBrandingLogoUrl = trim((string)($uploadFooterBrandingLogo['url'] ?? ''));
    }

    $slidesActuales = [];
    foreach ($configActual['carousel'] as $slideActual) {
        $slidesActuales[(int)$slideActual['id_carrousel']] = $slideActual;
    }

    $slidesTiendaCarruselActuales = [];
    foreach (($configActual['shop_carousel'] ?? []) as $slideActual) {
        $slidesTiendaCarruselActuales[(int)$slideActual['id_carrousel']] = $slideActual;
    }

    $slidesTiendaGaleriaActuales = [];
    foreach (($configActual['shop_gallery'] ?? []) as $slideActual) {
        $slidesTiendaGaleriaActuales[(int)$slideActual['id_carrousel']] = $slideActual;
    }

    $folderCarrusel = $mediaFolders['carrusel'] ?? 'ComunidadIFTS/carrusel';
    $folderTiendaCarrusel = $mediaFolders['tienda']['carrusel'] ?? 'ComunidadIFTS/tienda/carrusel';
    $folderTiendaGaleria = $mediaFolders['tienda']['galeria'] ?? 'ComunidadIFTS/tienda/galeria';

    $slidesSanitizados = procesarColeccionSlides(
        $carouselPayload,
        $slidesActuales,
        $_FILES,
        'carousel_image_',
        $folderCarrusel,
        $cloudinary
    );

    $slidesTiendaCarruselSanitizados = procesarColeccionSlides(
        $shopCarouselPayload,
        $slidesTiendaCarruselActuales,
        $_FILES,
        'shop_carousel_image_',
        $folderTiendaCarrusel,
        $cloudinary
    );

    $slidesTiendaGaleriaSanitizados = procesarColeccionSlides(
        $shopGalleryPayload,
        $slidesTiendaGaleriaActuales,
        $_FILES,
        'shop_gallery_image_',
        $folderTiendaGaleria,
        $cloudinary
    );

    $pdo->beginTransaction();

    SiteCustomizationModel::guardarNavbar($pdo, [
        'brand_text' => $navbarPayload['brand_text'] ?? $navbarActual['brand_text'] ?? '',
        'logo_url' => $navbarLogoUrl !== '' ? $navbarLogoUrl : null,
        'logo_public_id' => $navbarLogoPublicId !== '' ? $navbarLogoPublicId : null,
        'habilitado' => 1,
    ]);

    SiteCustomizationModel::guardarSidebar($pdo, [
        'brand_text' => $sidebarPayload['brand_text'] ?? $sidebarActual['brand_text'] ?? '',
        'logo_url' => $sidebarLogoUrl !== '' ? $sidebarLogoUrl : null,
        'logo_public_id' => $sidebarLogoPublicId !== '' ? $sidebarLogoPublicId : null,
        'habilitado' => 1,
    ]);

    SiteCustomizationModel::guardarFooterBranding($pdo, [
        'developer_text' => $footerBrandingPayload['developer_text'] ?? $footerBrandingActual['developer_text'] ?? 'Desarrollado por DeMentesConsultora',
        'link_url' => $footerBrandingPayload['link_url'] ?? $footerBrandingActual['link_url'] ?? null,
        'logo_url' => $footerBrandingLogoUrl !== '' ? $footerBrandingLogoUrl : null,
        'logo_public_id' => $footerBrandingLogoPublicId !== '' ? $footerBrandingLogoPublicId : null,
        'habilitado' => 1,
    ]);

    SiteCustomizationModel::guardarCarrusel($pdo, $slidesSanitizados);
    SiteCustomizationModel::guardarTiendaCarrusel($pdo, $slidesTiendaCarruselSanitizados);
    SiteCustomizationModel::guardarTiendaGaleria($pdo, $slidesTiendaGaleriaSanitizados);

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

    error_log('Error site-customization.php: ' . $e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
    ]);
}