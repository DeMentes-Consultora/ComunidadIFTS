<?php
/**
 * API: Dar like a una institución
 * Endpoint: POST /api/like-institucion.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Institucion.php';

header('Content-Type: application/json');

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de institución requerido']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Buscar la institución por ID
    $institucion = Institucion::buscarPorId($pdo, $input['id']);
    
    if (!$institucion) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Institución no encontrada']);
        exit;
    }
    
    // Incrementar likes usando el método de instancia
    $likes = $institucion->incrementarLikes($pdo);

    echo json_encode([
        'success' => true,
        'likes' => $likes
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar like',
        'error' => $_ENV['APP_DEBUG'] ? $e->getMessage() : null
    ]);
}
