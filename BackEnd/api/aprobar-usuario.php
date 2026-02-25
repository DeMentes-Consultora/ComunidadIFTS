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

    $idUsuario = (int)($payload['id_usuario'] ?? 0);
    $aprobar = $payload['aprobar'] ?? null;
    $motivo = trim($payload['motivo'] ?? '');

    if ($idUsuario <= 0 || $aprobar === null) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Datos incompletos: id_usuario y aprobar son obligatorios'
        ]);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Verificar que el usuario existe y está pendiente
    $sqlCheck = "SELECT 
                    u.id_usuario,
                    u.email,
                    u.habilitado,
                    p.nombre,
                    p.apellido
                FROM usuario u
                INNER JOIN persona p ON u.id_persona = p.id_persona
                WHERE u.id_usuario = ?
                  AND u.cancelado = 0
                LIMIT 1";

    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$idUsuario]);
    $usuario = $stmtCheck->fetch(PDO::FETCH_ASSOC);

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
        // APROBAR: habilitado = 1
        $sqlUpdate = "UPDATE usuario SET habilitado = 1 WHERE id_usuario = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$idUsuario]);

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
        // RECHAZAR: cancelado = 1 (o se puede eliminar, según preferencia)
        // Por seguridad, mejor marcar como cancelado que eliminar
        $sqlUpdate = "UPDATE usuario SET cancelado = 1 WHERE id_usuario = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$idUsuario]);

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
