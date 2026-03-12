<?php
/**
 * API: Aprobar o rechazar usuario
 * Endpoint: PUT /api/aprobar-usuario.php
 * Requiere: Autenticación + Rol ID 1 (AdministradorComunidad)
 * 
 * Body JSON:
 * {
 *   "id_usuario": 123,
 *   "aprobar": true/false,  // true = aprobar, false = rechazar
 *   "motivo": "texto" // opcional, solo para rechazo
 * }
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Mailer.php';
require_once __DIR__ . '/../models/Usuario.php';

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
            'message' => 'No tienes permisos para realizar esta acción'
        ]);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $idUsuarioRaw = $payload['id_usuario'] ?? 0;
    $aprobarRaw = $payload['aprobar'] ?? null;
    $motivo = trim((string)($payload['motivo'] ?? ''));

    $idUsuario = (int)$idUsuarioRaw;

    if (is_bool($aprobarRaw)) {
        $aprobar = $aprobarRaw;
    } elseif ($aprobarRaw === null || $aprobarRaw === '') {
        $aprobar = null;
    } else {
        $aprobar = filter_var($aprobarRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    if ($idUsuario <= 0 || $aprobar === null) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Datos incompletos o inválidos: id_usuario y aprobar son obligatorios'
        ]);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

        $usuario = Usuario::obtenerPendientePorId($pdo, $idUsuario);

    if (!$usuario) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
        exit;
    }

    if ($usuario['habilitado'] == 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El usuario ya está aprobado'
        ]);
        exit;
    }

    $pdo->beginTransaction();

    if ($aprobar === true) {
        Usuario::aprobarPorId($pdo, $idUsuario);

        $pdo->commit();

        // Enviar email de aprobación
        try {
            $mailer = new Mailer();
            $mailer->notificarAprobacion($usuario['email'], $usuario['nombre']);
        } catch (Exception $e) {
            error_log("Error enviando email de aprobación: " . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Usuario aprobado exitosamente',
            'aprobado' => true
        ]);
    } else {
        Usuario::rechazarPorId($pdo, $idUsuario);

        $pdo->commit();

        // Enviar email de rechazo
        try {
            $mailer = new Mailer();
            $mailer->notificarRechazo($usuario['email'], $usuario['nombre'], $motivo);
        } catch (Exception $e) {
            error_log("Error enviando email de rechazo: " . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Usuario rechazado',
            'aprobado' => false
        ]);
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
