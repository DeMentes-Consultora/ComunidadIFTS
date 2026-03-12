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
    if (!$fotoFile || (($fotoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Debes enviar un archivo valido en foto_perfil'
        ]);
        exit;
    }

    $mediaFolders = require __DIR__ . '/../config/media-folders.php';

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $fotoActual = Persona::obtenerFotoPerfilPorId($pdo, $idPersona);

    $cloudinary = new CloudinaryService($mediaFolders['base'] ?? 'ComunidadIFTS');
    $folderFoto = $mediaFolders['perfiles']['foto'] ?? 'ComunidadIFTS/perfiles';

    $upload = $cloudinary->uploadFromFileArray($fotoFile, $folderFoto, 'image');
    if (empty($upload['success'])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $upload['message'] ?? 'No fue posible subir la foto de perfil',
            'error' => ($_ENV['APP_DEBUG'] ?? false) ? ($upload['error'] ?? null) : null
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
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
