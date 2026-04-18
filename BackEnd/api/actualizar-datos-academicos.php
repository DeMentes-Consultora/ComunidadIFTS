<?php
/**
 * API: Actualizar carrera y año de cursada del alumno autenticado
 * Endpoint: POST /api/actualizar-datos-academicos.php
 * Requiere: Autenticación + Rol alumno (2)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Institucion.php';
require_once __DIR__ . '/../models/Usuario.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    $idInstitucion = (int)($_SESSION['id_institucion'] ?? 0);

    if ($idRol !== 2) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Solo los alumnos pueden modificar estos datos']);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    $idCarrera = (int)($payload['id_carrera'] ?? 0);
    $anioCursada = (int)($payload['anio_cursada'] ?? 0);

    if ($idCarrera <= 0 || $anioCursada <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Carrera y año de cursada son obligatorios']);
        exit;
    }

    if ($anioCursada < 1 || $anioCursada > 5) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El año de cursada no es válido']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    if (!Institucion::tieneCarreraActiva($pdo, $idInstitucion, $idCarrera)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La carrera no pertenece a tu institución']);
        exit;
    }

    Usuario::actualizarDatosAcademicos($pdo, $idUsuario, $idCarrera, $anioCursada);

    $usuario = Usuario::buscarPorId($pdo, $idUsuario);

    echo json_encode([
        'success' => true,
        'message' => 'Datos académicos actualizados',
        'data' => $usuario ? $usuario->toArray() : null,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
    ]);
}
