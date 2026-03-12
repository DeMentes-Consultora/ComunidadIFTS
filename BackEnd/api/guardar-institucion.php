<?php
/**
 * API: Crear nueva institución
 * Endpoint: POST /api/guardar-institucion.php
 * 
 * Permisos: Solo roles ID 1 (AdministradorComunidad) y ID 7 (AdministradorIFTS)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Institucion.php';
require_once __DIR__ . '/../services/CloudinaryService.php';

session_start();

header('Content-Type: application/json');

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar permisos: solo roles 1 y 7 pueden crear IFTS
$rolesPermitidos = [1, 7];
if (!in_array($_SESSION['id_rol'], $rolesPermitidos)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para crear instituciones']);
    exit;
}

try {
    $mediaFolders = require __DIR__ . '/../config/media-folders.php';
    $logoFinalUrl = null;
    $logoCloudinaryPublicId = null;

    // Soporte dual: JSON legacy y multipart/form-data
    $esMultipart = !empty($_POST) || !empty($_FILES);
    if ($esMultipart) {
        $input = $_POST;
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
    }

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
        exit;
    }

    // Normalizar carreras cuando llega como JSON string en FormData
    if (isset($input['carreras']) && is_string($input['carreras'])) {
        $carrerasDecoded = json_decode($input['carreras'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $input['carreras'] = $carrerasDecoded;
        }
    }

    // Subir logo a Cloudinary si se envio archivo
    $logoFile = $_FILES['logo_file'] ?? $_FILES['logo'] ?? null;
    if ($logoFile && (($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK)) {
        $cloudinary = new CloudinaryService($mediaFolders['base'] ?? 'ComunidadIFTS');
        $folderLogo = $mediaFolders['instituciones']['logo'] ?? 'ComunidadIFTS/logoIFTS';

        $upload = $cloudinary->uploadFromFileArray($logoFile, $folderLogo, 'image');
        if (!$upload['success']) {
            throw new Exception($upload['message'] ?? 'No se pudo subir el logo a Cloudinary.');
        }

        $logoFinalUrl = $upload['url'] ?? null;
        $logoCloudinaryPublicId = $upload['public_id'] ?? null;
        $input['logo'] = $logoFinalUrl;
        $input['logo_ifts'] = $logoFinalUrl;
    }

    // Validar campos requeridos
    if (empty($input['nombre']) && empty($input['nombre_ifts'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Crear objeto Institución desde los datos
    $institucion = Institucion::desdeArray($input);
    
    // Guardar en la base de datos
    $institucion->guardar($pdo);

    // Guardar metadata de Cloudinary si corresponde
    if (!empty($logoFinalUrl)) {
        Institucion::actualizarLogoCloudinaryMetadata($pdo, $institucion->getId(), $logoCloudinaryPublicId);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Institución guardada correctamente',
        'id' => $institucion->getId(),
        'data' => $institucion->toArray()
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar institución',
        'error' => $_ENV['APP_DEBUG'] ? $e->getMessage() : null
    ]);
}
