<?php
/**
 * API: Actualizar foto de perfil de usuario autenticado
 * Endpoint: POST /api/actualizar-foto-perfil.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Persona.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../services/CloudinaryService.php';

header('Content-Type: application/json');

function uploadErrorText(int $code): string {
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metodo no permitido'
    ]);
    exit;
}

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'No autenticado'
        ]);
        exit;
    }

    $idUsuario = (int)($_SESSION['id_usuario'] ?? 0);
    $idPersona = (int)($_SESSION['id_persona'] ?? 0);

    if ($idUsuario <= 0 || $idPersona <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Sesion invalida'
        ]);
        exit;
    }

    $fotoFile = $_FILES['foto_perfil'] ?? $_FILES['foto'] ?? null;
    $uploadErrorCode = (int)($fotoFile['error'] ?? UPLOAD_ERR_NO_FILE);
    if (!$fotoFile || $uploadErrorCode !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Debes enviar un archivo valido en foto_perfil',
            'details' => [
                'upload_error_code' => $uploadErrorCode,
                'upload_error_text' => uploadErrorText($uploadErrorCode),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            ]
        ]);
        exit;
    }

    $mediaFolders = require __DIR__ . '/../config/media-folders.php';

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $fotoActual = Persona::obtenerFotoPerfilPorId($pdo, $idPersona);

    try {
        $cloudinary = new CloudinaryService($mediaFolders['base'] ?? 'ComunidadIFTS');
    } catch (Throwable $e) {
        error_log('Cloudinary init fallo (actualizar-foto-perfil): ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo inicializar Cloudinary en el servidor',
            'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
        ]);
        exit;
    }
    $folderFoto = $mediaFolders['perfiles']['foto'] ?? 'ComunidadIFTS/perfiles';

    $upload = $cloudinary->uploadFromFileArray($fotoFile, $folderFoto, 'image');
    if (empty($upload['success'])) {
        error_log('Cloudinary upload fallo (actualizar-foto-perfil): ' . ($upload['error'] ?? $upload['message'] ?? 'sin detalle'));
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $upload['message'] ?? 'No fue posible subir la foto de perfil',
            'error' => ($_ENV['APP_DEBUG'] ?? false) ? ($upload['error'] ?? null) : null,
            'details' => [
                'tmp_name_exists' => is_file((string)($fotoFile['tmp_name'] ?? '')),
                'file_size_bytes' => (int)($fotoFile['size'] ?? 0),
                'mime' => (string)($fotoFile['type'] ?? ''),
            ]
        ]);
        exit;
    }

    $nuevaUrl = (string)($upload['url'] ?? '');
    $nuevoPublicId = (string)($upload['public_id'] ?? '');

    if ($nuevaUrl === '' || $nuevoPublicId === '') {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Cloudinary no devolvio datos validos'
        ]);
        exit;
    }

    Persona::actualizarFotoPerfil($pdo, $idPersona, $nuevaUrl, $nuevoPublicId);

    $publicIdAnterior = trim((string)($fotoActual['foto_perfil_public_id'] ?? ''));
    if ($publicIdAnterior !== '' && $publicIdAnterior !== $nuevoPublicId) {
        $cloudinary->delete($publicIdAnterior, 'image');
    }

    $usuario = Usuario::buscarPorId($pdo, $idUsuario);

    echo json_encode([
        'success' => true,
        'message' => 'Foto de perfil actualizada correctamente',
        'data' => $usuario ? $usuario->toArray() : [
            'id_usuario' => $idUsuario,
            'id_persona' => $idPersona,
            'foto_perfil_url' => $nuevaUrl,
            'foto_perfil_public_id' => $nuevoPublicId,
        ]
    ]);
} catch (Throwable $e) {
    error_log('Error actualizar-foto-perfil.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
