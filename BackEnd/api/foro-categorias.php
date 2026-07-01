<?php
/**
 * API: Listar categorías del foro
 * Endpoint: GET /api/foro-categorias.php
 * Roles: 1 (AdminComunidad), 2 (Alumno), 3 (AdminIFTS)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ForoCategoria.php';

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

    $admin = isset($_GET['admin']) && $_GET['admin'] === '1' && $rol === 1;
    $categorias = $admin
        ? ForoCategoria::obtenerTodasAdmin($pdo)
        : ForoCategoria::obtenerTodas($pdo);

    echo json_encode([
        'success' => true,
        'categorias' => $categorias
    ]);
} catch (Throwable $e) {
    error_log('Error foro-categorias.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
