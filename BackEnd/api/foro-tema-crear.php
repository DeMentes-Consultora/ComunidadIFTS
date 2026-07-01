<?php
/**
 * API: Crear nuevo tema en el foro
 * Endpoint: POST /api/foro-tema-crear.php
 * Roles: 1 (AdminComunidad), 2 (Alumno), 3 (AdminIFTS)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ForoCategoria.php';
require_once __DIR__ . '/../models/ForoTema.php';

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

    $rol = (int)($_SESSION['id_rol'] ?? 0);
    if (!in_array($rol, [1, 2, 3])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true);

    $idCategoria = (int)($payload['id_categoria'] ?? 0);
    $titulo = trim((string)($payload['titulo'] ?? ''));
    $contenido = trim((string)($payload['contenido'] ?? ''));

    if ($idCategoria <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La categoría es obligatoria']);
        exit;
    }

    if ($titulo === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El título es obligatorio']);
        exit;
    }

    if (mb_strlen($titulo) > 255) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El título no puede superar los 255 caracteres']);
        exit;
    }

    if ($contenido === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El contenido es obligatorio']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Verificar que la categoría exista y esté habilitada
    $categoria = ForoCategoria::obtenerPorId($pdo, $idCategoria);
    if (!$categoria || $categoria['habilitado'] != 1 || $categoria['cancelado'] != 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Categoría no válida']);
        exit;
    }

    $idUsuario = (int)$_SESSION['id_usuario'];
    $idTema = ForoTema::crear($pdo, $idCategoria, $idUsuario, $titulo, $contenido);

    if ($idTema === null) {
        throw new RuntimeException('No se pudo crear el tema');
    }

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Tema creado correctamente',
        'id_tema' => $idTema
    ]);
} catch (Throwable $e) {
    error_log('Error foro-tema-crear.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
