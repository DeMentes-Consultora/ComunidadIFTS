<?php
/**
 * API: Ver, editar o eliminar un tema del foro
 * Endpoint: GET/PUT/DELETE /api/foro-tema.php
 * Roles: 1 (AdminComunidad), 2 (Alumno), 3 (AdminIFTS)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ForoTema.php';
require_once __DIR__ . '/../models/ForoAdjunto.php';

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
if (!in_array($rol, [1, 2, 3])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    if ($method === 'GET') {
        // Ver tema
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $tema = ForoTema::obtenerPorId($pdo, $id);
        if (!$tema) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Tema no encontrado']);
            exit;
        }

        // Incrementar vistas
        ForoTema::incrementarVistas($pdo, $id);

        // Registrar vista
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        ForoTema::registrarVista($pdo, $id, (int)$_SESSION['id_usuario'], $ipAddress);

        // Obtener adjuntos
        $adjuntos = ForoAdjunto::obtenerPorTema($pdo, $id);

        echo json_encode([
            'success' => true,
            'tema' => $tema,
            'adjuntos' => $adjuntos
        ]);

    } elseif ($method === 'PUT') {
        // Editar tema, cerrar/abrir, fijar/desfijar
        $payload = json_decode(file_get_contents('php://input'), true);
        $id = (int)($payload['id_tema'] ?? 0);

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $tema = ForoTema::obtenerPorId($pdo, $id);
        if (!$tema) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Tema no encontrado']);
            exit;
        }

        // Acciones especiales
        if (isset($payload['accion'])) {
            $accion = $payload['accion'];

            if ($accion === 'cerrar') {
                $motivo = $payload['motivo'] ?? null;
                ForoTema::cerrar($pdo, $id, $motivo);
                echo json_encode(['success' => true, 'message' => 'Tema cerrado']);
                exit;
            }

            if ($accion === 'abrir') {
                ForoTema::abrir($pdo, $id);
                echo json_encode(['success' => true, 'message' => 'Tema abierto']);
                exit;
            }

            if ($accion === 'fijar') {
                $fijar = (bool)($payload['fijar'] ?? true);
                ForoTema::fijar($pdo, $id, $fijar);
                echo json_encode(['success' => true, 'message' => $fijar ? 'Tema fijado' : 'Tema desfijado']);
                exit;
            }
        }

        // Edición normal
        $idUsuario = (int)$_SESSION['id_usuario'];
        $esAdmin = $rol === 1;
        $esAutor = (int)$tema['id_usuario'] === $idUsuario;

        if (!$esAdmin && !$esAutor) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No tenés permiso para editar este tema']);
            exit;
        }

        $datos = [];
        if (isset($payload['titulo'])) $datos['titulo'] = trim($payload['titulo']);
        if (isset($payload['contenido'])) $datos['contenido'] = trim($payload['contenido']);
        if (isset($payload['id_categoria']) && $esAdmin) $datos['id_categoria'] = (int)$payload['id_categoria'];

        if (empty($datos)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No hay datos para actualizar']);
            exit;
        }

        if (ForoTema::actualizar($pdo, $id, $datos)) {
            echo json_encode(['success' => true, 'message' => 'Tema actualizado']);
        } else {
            throw new RuntimeException('No se pudo actualizar el tema');
        }

    } elseif ($method === 'DELETE') {
        // Eliminar tema (solo admin)
        if ($rol !== 1) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Solo el administrador puede eliminar temas']);
            exit;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $id = (int)($payload['id_tema'] ?? 0);

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $tema = ForoTema::obtenerPorId($pdo, $id);
        if (!$tema) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Tema no encontrado']);
            exit;
        }

        if (ForoTema::eliminar($pdo, $id)) {
            echo json_encode(['success' => true, 'message' => 'Tema eliminado']);
        } else {
            throw new RuntimeException('No se pudo eliminar el tema');
        }

    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Throwable $e) {
    error_log('Error foro-tema.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
