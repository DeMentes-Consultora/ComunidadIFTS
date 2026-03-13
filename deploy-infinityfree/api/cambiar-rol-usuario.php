<?php
/**
 * API: Cambiar rol de un usuario registrado
 * Endpoint: PUT /api/cambiar-rol-usuario.php
 * Requiere: Autenticación + Rol ID 1 (AdministradorComunidad)
 *
 * Body JSON:
 * {
 *   "id_usuario": 123,
 *   "id_rol": 2
 * }
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Rol.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
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

    $idRolSesion = (int)($_SESSION['id_rol'] ?? 0);
    if ($idRolSesion !== 1) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'No tienes permisos para realizar esta acción'
        ]);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $idUsuario = (int)($payload['id_usuario'] ?? 0);
    $idRolNuevo = (int)($payload['id_rol'] ?? 0);

    if ($idUsuario <= 0 || $idRolNuevo <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Datos incompletos o inválidos: id_usuario e id_rol son obligatorios'
        ]);
        exit;
    }

    $idUsuarioSesion = (int)($_SESSION['id_usuario'] ?? 0);
    if ($idUsuarioSesion > 0 && $idUsuarioSesion === $idUsuario) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No puedes cambiar tu propio rol'
        ]);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $usuario = Usuario::obtenerRegistradoPorId($pdo, $idUsuario);
    if (!$usuario) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario registrado no encontrado'
        ]);
        exit;
    }

    $rolActivo = Rol::obtenerActivoPorId($pdo, $idRolNuevo);
    if ($rolActivo === null) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El rol seleccionado no existe o no está habilitado'
        ]);
        exit;
    }

    if ((int)$usuario['id_rol'] === $idRolNuevo) {
        $nombreRolActual = Rol::obtenerNombreActivoPorId($pdo, $idRolNuevo);

        echo json_encode([
            'success' => true,
            'message' => 'El usuario ya tiene ese rol',
            'data' => [
                'id_usuario' => $idUsuario,
                'id_rol' => $idRolNuevo,
                'nombre_rol' => $nombreRolActual
            ]
        ]);
        exit;
    }

    $ok = Usuario::cambiarRolPorId($pdo, $idUsuario, $idRolNuevo);

    if (!$ok) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo actualizar el rol del usuario'
        ]);
        exit;
    }

    $nombreRol = Rol::obtenerNombreActivoPorId($pdo, $idRolNuevo);

    echo json_encode([
        'success' => true,
        'message' => 'Rol actualizado exitosamente',
        'data' => [
            'id_usuario' => $idUsuario,
            'id_rol' => $idRolNuevo,
            'nombre_rol' => $nombreRol
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
