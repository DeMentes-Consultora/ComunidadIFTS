<?php
/**
 * API: Obtener usuarios registrados (aprobados) y roles disponibles
 * Endpoint: GET /api/usuarios-registrados.php
 * Requiere: Autenticación + Rol ID 1 (AdministradorComunidad)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Rol.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
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

    $idRol = (int)($_SESSION['id_rol'] ?? 0);
    if ($idRol !== 1) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'No tienes permisos para acceder a esta información'
        ]);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $usuarios = Usuario::obtenerRegistradosAprobados($pdo);
    $roles = Rol::obtenerActivos($pdo);

    echo json_encode([
        'success' => true,
        'data' => $usuarios,
        'roles' => $roles,
        'total' => count($usuarios)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
