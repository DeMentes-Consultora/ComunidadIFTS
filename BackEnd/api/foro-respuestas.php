<?php
/**
 * API: Listar y crear respuestas de un tema del foro
 * Endpoint: GET/POST /api/foro-respuestas.php
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

    if ($method === 'GET') {
        // Listar respuestas de un tema
        $idTema = (int)($_GET['id_tema'] ?? 0);
        if ($idTema <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de tema inválido']);
            exit;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));

        $resultado = ForoRespuesta::obtenerPorTema($pdo, $idTema, $page, $limit);

        echo json_encode([
            'success' => true,
            'respuestas' => $resultado['respuestas'],
            'total' => $resultado['total'],
            'page' => $resultado['page'],
            'limit' => $resultado['limit'],
            'pages' => $resultado['pages']
        ]);

    } elseif ($method === 'POST') {
        // Crear respuesta
        $payload = json_decode(file_get_contents('php://input'), true);

        $idTema = (int)($payload['id_tema'] ?? 0);
        $contenido = trim((string)($payload['contenido'] ?? ''));
        $citandoId = isset($payload['citando_id']) ? (int)$payload['citando_id'] : null;

        if ($idTema <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de tema inválido']);
            exit;
        }

        if ($contenido === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El contenido es obligatorio']);
            exit;
        }

        // Verificar que el tema exista y esté abierto
        $tema = ForoTema::obtenerPorId($pdo, $idTema);
        if (!$tema) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Tema no encontrado']);
            exit;
        }

        if ($tema['cerrado'] == 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No se puede responder a un tema cerrado']);
            exit;
        }

        $idUsuario = (int)$_SESSION['id_usuario'];
        $idRespuesta = ForoRespuesta::crear($pdo, $idTema, $idUsuario, $contenido, $citandoId);

        if ($idRespuesta === null) {
            throw new RuntimeException('No se pudo crear la respuesta');
        }

        // Incrementar contador de respuestas del tema
        ForoTema::incrementarRespuestas($pdo, $idTema);

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Respuesta creada correctamente',
            'id_respuesta' => $idRespuesta
        ]);

    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Throwable $e) {
    error_log('Error foro-respuestas.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
