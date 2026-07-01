<?php
/**
 * API: Búsqueda en el foro
 * Endpoint: GET /api/foro-buscar.php
 * Roles: 1 (AdminComunidad), 2 (Alumno), 3 (AdminIFTS)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ForoBusqueda.php';

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

    $termino = trim((string)($_GET['q'] ?? ''));
    if ($termino === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El término de búsqueda es obligatorio']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(50, max(1, (int)($_GET['limit'] ?? 15)));
    $categoriaId = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
    $tipo = $_GET['tipo'] ?? 'temas'; // 'temas' o 'respuestas'

    if ($tipo === 'respuestas') {
        $resultado = ForoBusqueda::buscarRespuestas($pdo, $termino, $page, $limit);
    } else {
        $resultado = ForoBusqueda::buscarTemas($pdo, $termino, $categoriaId, $page, $limit);
    }

    echo json_encode([
        'success' => true,
        $tipo => $resultado[$tipo] ?? [],
        'total' => $resultado['total'],
        'page' => $resultado['page'],
        'limit' => $resultado['limit'],
        'pages' => $resultado['pages'],
        'termino' => $resultado['termino']
    ]);
} catch (Throwable $e) {
    error_log('Error foro-buscar.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
