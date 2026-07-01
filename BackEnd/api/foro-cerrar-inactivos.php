<?php
/**
 * API: Cerrar temas inactivos automáticamente
 * Endpoint: GET /api/foro-cerrar-inactivos.php
 * Roles: 1 (AdminComunidad)
 * 
 * Cierra temas que no recibieron respuesta en 7 días y notifica por email.
 * Puede ejecutarse vía cron job o manualmente.
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ForoTema.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../services/ForoEmailService.php';

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

    $rol = (int)($_SESSION['id_rol'] ?? 0);
    if ($rol !== 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Solo el administrador puede ejecutar esta acción']);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $dias = isset($_GET['dias']) ? max(1, (int)$_GET['dias']) : 7;
    $temasInactivos = ForoTema::obtenerTemasInactivos($pdo, $dias);

    $emailService = new ForoEmailService();
    $cerrados = 0;
    $emailsEnviados = 0;
    $emailsFallidos = 0;

    foreach ($temasInactivos as $tema) {
        // Cerrar tema
        $motivo = "Cerrado automáticamente por inactividad ($dias días sin respuesta)";
        ForoTema::cerrar($pdo, $tema['id_tema'], $motivo);
        $cerrados++;

        // Enviar email de notificación
        if (!empty($tema['autor_email']) && !empty($tema['autor_nombre'])) {
            $exito = $emailService->notificarTemaCerradoPorInactividad(
                $tema['autor_email'],
                $tema['autor_nombre'],
                $tema['titulo'],
                $dias
            );

            if ($exito) {
                $emailsEnviados++;
            } else {
                $emailsFallidos++;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Se cerraron $cerrados temas inactivos",
        'temas_cerrados' => $cerrados,
        'emails_enviados' => $emailsEnviados,
        'emails_fallidos' => $emailsFallidos,
        'dias_inactividad' => $dias
    ]);
} catch (Throwable $e) {
    error_log('Error foro-cerrar-inactivos.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
