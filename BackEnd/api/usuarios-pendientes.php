<?php
/**
 * API: Obtener usuarios pendientes de aprobación
 * Endpoint: GET /api/usuarios-pendientes.php
 * Requiere: Autenticación + Rol ID 1 (AdministradorComunidad)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

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

    // Obtener usuarios con habilitado = 0 (pendientes)
    $sql = "SELECT 
                u.id_usuario,
                u.email,
                u.habilitado,
                u.idCreate as fecha_registro,
                p.nombre,
                p.apellido,
                p.dni,
                p.telefono,
                i.nombre_institucion,
                i.id_institucion,
                r.nombre_rol,
                r.id_rol
            FROM usuario u
            INNER JOIN persona p ON u.id_persona = p.id_persona
            INNER JOIN institucion i ON u.id_institucion = i.id_institucion
            INNER JOIN rol r ON u.id_rol = r.id_rol
            WHERE u.habilitado = 0
              AND u.cancelado = 0
            ORDER BY u.idCreate DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
