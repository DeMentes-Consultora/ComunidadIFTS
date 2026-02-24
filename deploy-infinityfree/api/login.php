<?php
/**
 * API: Login de usuario
 * Endpoint: POST /api/login.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

    $payload = json_decode(file_get_contents('php://input'), true);

    $email = trim($payload['email'] ?? $_POST['email'] ?? '');
    $clave = trim($payload['clave'] ?? $_POST['clave'] ?? '');

    if ($email === '' || $clave === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email y contraseña son obligatorios'
        ]);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $usuario = Usuario::autenticar($pdo, $email, $clave);

    if (!$usuario) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario o contraseña incorrectos'
        ]);
        exit;
    }

    $_SESSION['logged_in'] = true;
    $_SESSION['id_usuario'] = $usuario->getIdUsuario();
    $_SESSION['email'] = $usuario->getEmail();
    $_SESSION['id_rol'] = $usuario->getIdRol();
    $_SESSION['id_persona'] = $usuario->getIdPersona();
    $_SESSION['id_institucion'] = $usuario->getIdInstitucion();
    $_SESSION['nombre'] = $usuario->getNombre();
    $_SESSION['apellido'] = $usuario->getApellido();

    echo json_encode([
        'success' => true,
        'message' => 'Login correcto',
        'data' => $usuario->toArray()
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
