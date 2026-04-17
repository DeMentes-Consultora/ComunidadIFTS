<?php
/**
 * API: Actualizar institución existente
 * Endpoint: PUT /api/actualizar-institucion.php
 * 
 * Permisos: Solo roles ID 1 (AdministradorComunidad) y ID 3 (AdministradorIFTS)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Institucion.php';
require_once __DIR__ . '/../services/CloudinaryService.php';

session_start();

header('Content-Type: application/json');

// Permitir PUT (JSON) y POST (multipart para archivos)
if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'], true)) {
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

// Verificar permisos: solo roles 1 y 3 pueden actualizar IFTS
$rolesPermitidos = [1, 3];
if (!in_array($_SESSION['id_rol'], $rolesPermitidos)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para modificar instituciones']);
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

    // Validar que se envíe el ID
    if (empty($input['id']) && empty($input['id_institucion'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El ID de la institución es requerido']);
        exit;
    }

    // Validar campos requeridos
    if (empty($input['nombre']) && empty($input['nombre_ifts'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Obtener el ID (puede venir como 'id' o 'id_institucion')
    $idInstitucion = $input['id'] ?? $input['id_institucion'];
    
    // Verificar que la institución existe y obtener logo actual
    $institucionActual = Institucion::obtenerConLogoPorId($pdo, $idInstitucion);
    if (!$institucionActual) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Institución no encontrada']);
        exit;
    }

    // Reemplazar logo si se envio nuevo archivo
    $logoFile = $_FILES['logo_file'] ?? $_FILES['logo'] ?? null;
    if ($logoFile && (($logoFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK)) {
        $cloudinary = new CloudinaryService($mediaFolders['base'] ?? 'ComunidadIFTS');
        $folderLogo = $mediaFolders['instituciones']['logo'] ?? 'ComunidadIFTS/logoIFTS';

        // Si el logo previo era de Cloudinary, intentar borrarlo
        $logoActual = (string)($institucionActual['logo_ifts'] ?? '');
        if ($logoActual !== '' && strpos($logoActual, 'res.cloudinary.com') !== false) {
            $cloudinary->deleteByUrl($logoActual, 'image');
        }

        $upload = $cloudinary->uploadFromFileArray($logoFile, $folderLogo, 'image');
        if (!$upload['success']) {
            throw new Exception($upload['message'] ?? 'No se pudo subir el logo a Cloudinary.');
        }

        $logoFinalUrl = $upload['url'] ?? null;
        $logoCloudinaryPublicId = $upload['public_id'] ?? null;
        $input['logo'] = $logoFinalUrl;
        $input['logo_ifts'] = $logoFinalUrl;
    } else {
        // Preservar logo actual cuando no se envia logo nuevo
        if (empty($input['logo']) && empty($input['logo_ifts'])) {
            $input['logo'] = $institucionActual['logo_ifts'] ?? null;
            $input['logo_ifts'] = $institucionActual['logo_ifts'] ?? null;
        }
    }
    
    // Crear objeto Institución desde los datos
    $institucion = Institucion::desdeArray($input);
    $institucion->setId($idInstitucion);
    
    // Actualizar en la base de datos
    $institucion->actualizar($pdo);

    // Actualizar metadata de Cloudinary si hubo reemplazo de archivo
    if (!empty($logoFinalUrl)) {
        Institucion::actualizarLogoCloudinaryMetadata($pdo, $idInstitucion, $logoCloudinaryPublicId);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Institución actualizada correctamente',
        'id' => $institucion->getId(),
        'data' => $institucion->toArray()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar la institución',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
