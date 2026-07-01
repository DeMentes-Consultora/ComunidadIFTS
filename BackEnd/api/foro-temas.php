<?php
/**
 * API: Listar temas del foro
 * Endpoint: GET /api/foro-temas.php
 * Roles: 1 (AdminComunidad), 2 (Alumno), 3 (AdminIFTS)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ForoTema.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
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

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(50, max(1, (int)($_GET['limit'] ?? 15)));
    $categoriaId = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
    $busqueda = isset($_GET['q']) ? trim($_GET['q']) : null;

    $resultado = ForoTema::obtenerListado($pdo, $page, $limit, $categoriaId, $busqueda);

    echo json_encode([
        'success' => true,
        'temas' => $resultado['temas'],
        'total' => $resultado['total'],
        'page' => $resultado['page'],
        'limit' => $resultado['limit'],
        'pages' => $resultado['pages']
    ]);
} catch (Throwable $e) {
    error_log('Error foro-temas.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
