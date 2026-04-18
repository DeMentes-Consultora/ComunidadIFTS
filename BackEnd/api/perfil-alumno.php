<?php
/**
 * API: Perfil de alumno autenticado
 * Endpoint: GET /api/perfil-alumno.php
 * Requiere: Autenticación + Rol alumno (2)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Postulacion.php';

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

    $idUsuario = (int)($_SESSION['id_usuario'] ?? 0);
    $idRol = (int)($_SESSION['id_rol'] ?? 0);

    if ($idRol !== 2) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Solo los alumnos pueden acceder a este perfil']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $usuario = Usuario::buscarPorId($pdo, $idUsuario);
    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }

    $postulaciones = Postulacion::obtenerOfertasDeAlumno($pdo, $idUsuario);

    echo json_encode([
        'success' => true,
        'data' => [
            'usuario' => $usuario->toArray(),
            'postulaciones' => $postulaciones,
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
    ]);
}