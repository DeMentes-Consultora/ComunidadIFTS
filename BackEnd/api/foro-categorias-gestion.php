<?php
/**
 * API: Gestión de categorías del foro (CRUD)
 * Endpoint: POST/PUT/DELETE /api/foro-categorias-gestion.php
 * Roles: 1 (AdminComunidad) solamente
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ForoCategoria.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$rol = (int)($_SESSION['id_rol'] ?? 0);
if ($rol !== 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Solo el administrador puede gestionar categorías']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    if ($method === 'POST') {
        // Crear categoría
        $payload = json_decode(file_get_contents('php://input'), true);

        $nombre = trim((string)($payload['nombre'] ?? ''));
        if ($nombre === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            exit;
        }

        if (ForoCategoria::existeNombre($pdo, $nombre)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Ya existe una categoría con ese nombre']);
            exit;
        }

        $id = ForoCategoria::crear(
            $pdo,
            $nombre,
            $payload['descripcion'] ?? null,
            $payload['icono'] ?? null,
            $payload['color'] ?? null,
            (int)($payload['orden'] ?? 0)
        );

        if ($id === null) {
            throw new RuntimeException('No se pudo crear la categoría');
        }

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Categoría creada correctamente',
            'id_categoria' => $id
        ]);

    } elseif ($method === 'PUT') {
        // Actualizar categoría
        $payload = json_decode(file_get_contents('php://input'), true);
        $id = (int)($payload['id_categoria'] ?? 0);

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de categoría inválido']);
            exit;
        }

        $existente = ForoCategoria::obtenerPorId($pdo, $id);
        if (!$existente) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
            exit;
        }

        // Verificar nombre duplicado si se está cambiando
        if (isset($payload['nombre']) && $payload['nombre'] !== $existente['nombre']) {
            if (ForoCategoria::existeNombre($pdo, $payload['nombre'], $id)) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Ya existe una categoría con ese nombre']);
                exit;
            }
        }

        $datos = array_filter($payload, fn($k) => in_array($k, ['nombre', 'descripcion', 'icono', 'color', 'orden', 'habilitado']), ARRAY_FILTER_USE_KEY);

        if (ForoCategoria::actualizar($pdo, $id, $datos)) {
            echo json_encode(['success' => true, 'message' => 'Categoría actualizada correctamente']);
        } else {
            throw new RuntimeException('No se pudo actualizar la categoría');
        }

    } elseif ($method === 'DELETE') {
        // Eliminar categoría (soft delete)
        $payload = json_decode(file_get_contents('php://input'), true);
        $id = (int)($payload['id_categoria'] ?? 0);

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de categoría inválido']);
            exit;
        }

        $existente = ForoCategoria::obtenerPorId($pdo, $id);
        if (!$existente) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
            exit;
        }

        if (ForoCategoria::eliminar($pdo, $id)) {
            echo json_encode(['success' => true, 'message' => 'Categoría eliminada correctamente']);
        } else {
            throw new RuntimeException('No se pudo eliminar la categoría');
        }

    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Throwable $e) {
    error_log('Error foro-categorias-gestion.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
