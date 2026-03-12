<?php
/**
 * API: Eliminar institución
 * Endpoint: POST /api/eliminar-institucion.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Institucion.php';

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$rolesPermitidos = [1, 3, 7];
if (!isset($_SESSION['id_rol']) || !in_array((int)$_SESSION['id_rol'], $rolesPermitidos, true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para eliminar instituciones']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $idInstitucion = (int)($input['id_institucion'] ?? 0);

    if ($idInstitucion <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de institución inválido']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    if (!Institucion::existePorId($pdo, $idInstitucion)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Institución no encontrada']);
        exit;
    }

    Institucion::eliminarConRelaciones($pdo, $idInstitucion);

    echo json_encode([
        'success' => true,
        'message' => 'Institución eliminada correctamente',
    ]);
} catch (\Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar institución',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
    ]);
}
