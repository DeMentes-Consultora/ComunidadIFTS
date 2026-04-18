<?php
/**
 * API: Ocultar una postulación del perfil del alumno
 * Endpoint: POST /api/cancelar-postulacion.php
 * Requiere: Autenticación + Rol alumno (2)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Postulacion.php';

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

    if ($idRol !== 2) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Solo los alumnos pueden modificar sus postulaciones']);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    $idPostulacion = (int)($payload['id_postulacion'] ?? 0);

    if ($idPostulacion <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'id_postulacion es obligatorio']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $ok = Postulacion::cancelarDeAlumno($pdo, $idPostulacion, $idUsuario);
    if (!$ok) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'La postulación no existe o ya fue eliminada']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'La oferta se quitó de tu perfil',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
    ]);
}