<?php
/**
 * API: Editar o eliminar una respuesta del foro
 * Endpoint: PUT/DELETE /api/foro-respuesta.php
 * Roles: 1 (AdminComunidad), 2 (Alumno), 3 (AdminIFTS)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ForoTema.php';
require_once __DIR__ . '/../models/ForoRespuesta.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$rol = (int)($_SESSION['id_rol'] ?? 0);
if (!in_array($rol, [1, 2, 3])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    if ($method === 'PUT') {
        // Editar respuesta
        $payload = json_decode(file_get_contents('php://input'), true);
        $id = (int)($payload['id_respuesta'] ?? 0);
        $contenido = trim((string)($payload['contenido'] ?? ''));

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        if ($contenido === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El contenido es obligatorio']);
            exit;
        }

        $respuesta = ForoRespuesta::obtenerPorId($pdo, $id);
        if (!$respuesta) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Respuesta no encontrada']);
            exit;
        }

        $idUsuario = (int)$_SESSION['id_usuario'];
        $esAdmin = $rol === 1;
        $esAutor = (int)$respuesta['id_usuario'] === $idUsuario;

        if (!$esAdmin && !$esAutor) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No tenés permiso para editar esta respuesta']);
            exit;
        }

        if (ForoRespuesta::actualizar($pdo, $id, $contenido)) {
            echo json_encode(['success' => true, 'message' => 'Respuesta actualizada']);
        } else {
            throw new RuntimeException('No se pudo actualizar la respuesta');
        }

    } elseif ($method === 'DELETE') {
        // Eliminar respuesta (solo admin)
        if ($rol !== 1) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Solo el administrador puede eliminar respuestas']);
            exit;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $id = (int)($payload['id_respuesta'] ?? 0);

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $respuesta = ForoRespuesta::obtenerPorId($pdo, $id);
        if (!$respuesta) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Respuesta no encontrada']);
            exit;
        }

        $idTema = (int)$respuesta['id_tema'];

        if (ForoRespuesta::eliminar($pdo, $id)) {
            // Decrementar contador de respuestas del tema
            ForoTema::decrementarRespuestas($pdo, $idTema);
            echo json_encode(['success' => true, 'message' => 'Respuesta eliminada']);
        } else {
            throw new RuntimeException('No se pudo eliminar la respuesta');
        }

    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Throwable $e) {
    error_log('Error foro-respuesta.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
