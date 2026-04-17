<?php
/**
 * API: Gestionar oferta laboral (aprobar / rechazar / toggle habilitar)
 * Endpoint: PUT /api/gestionar-oferta.php
 * Requiere: Autenticación + Rol ID 1 (AdministradorComunidad)
 *
 * Body JSON:
 * {
 *   "id_bolsaDeTrabajo": 5,
 *   "accion": "aprobar" | "rechazar" | "deshabilitar",
 *   "motivo": "texto opcional, solo para rechazar"
 * }
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Mailer.php';
require_once __DIR__ . '/../models/BolsaTrabajo.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
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

    $idRol = (int)($_SESSION['id_rol'] ?? 0);
    if ($idRol !== 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para gestionar ofertas laborales']);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $idOferta = (int)($payload['id_bolsaDeTrabajo'] ?? 0);
    $accion   = trim((string)($payload['accion'] ?? ''));
    $motivo   = trim((string)($payload['motivo'] ?? ''));

    if ($idOferta <= 0 || !in_array($accion, ['aprobar', 'rechazar', 'deshabilitar'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos inválidos: id_bolsaDeTrabajo y accion son obligatorios']);
        exit;
    }

    $db  = Database::getInstance();
    $pdo = $db->getConnection();

    $oferta = BolsaTrabajo::obtenerPorId($pdo, $idOferta);
    if (!$oferta) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Oferta no encontrada']);
        exit;
    }

    $emailIFTS  = $oferta['email_ifts'] ?? '';
    $nombreIFTS = $oferta['nombre_ifts'] ?? '';
    $titulo     = $oferta['tituloOferta'] ?? '';

    $pdo->beginTransaction();

    switch ($accion) {
        case 'aprobar':
            BolsaTrabajo::publicarOferta($pdo, $idOferta);
            $pdo->commit();

            try {
                $mailer = new Mailer();
                $mailer->notificarOfertaPublicada($emailIFTS, $nombreIFTS, $titulo);
            } catch (Exception $e) {
                error_log("Error mail oferta publicada: " . $e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => 'Oferta aprobada y publicada correctamente',
                'accion'  => 'aprobar'
            ]);
            break;

        case 'rechazar':
            BolsaTrabajo::rechazarOferta($pdo, $idOferta);
            $pdo->commit();

            try {
                $mailer = new Mailer();
                $mailer->notificarOfertaRechazada($emailIFTS, $nombreIFTS, $titulo, $motivo);
            } catch (Exception $e) {
                error_log("Error mail oferta rechazada: " . $e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => 'Oferta rechazada',
                'accion'  => 'rechazar'
            ]);
            break;

        case 'deshabilitar':
            BolsaTrabajo::deshabilitarOferta($pdo, $idOferta);
            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Oferta deshabilitada',
                'accion'  => 'deshabilitar'
            ]);
            break;
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error'   => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
