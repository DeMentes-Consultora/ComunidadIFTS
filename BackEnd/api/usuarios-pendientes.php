<?php
/**
 * API: Obtener usuarios pendientes de aprobación
 * Endpoint: GET /api/usuarios-pendientes.php
 * Requiere: Autenticación + Rol ID 1 (AdministradorComunidad)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

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

    // Verificar autenticación
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'No autenticado'
        ]);
        exit;
    }

    // Verificar que sea administrador (rol ID 1)
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

        $usuarios = Usuario::obtenerPendientesAprobacion($pdo);

    // Formatear fechas
    foreach ($usuarios as &$usuario) {
        if ($usuario['fecha_registro']) {
            $fecha = new DateTime($usuario['fecha_registro']);
            $usuario['fecha_registro_formateada'] = $fecha->format('d/m/Y H:i');
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $usuarios,
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
