<?php
/**
 * API: Crear oferta laboral
 * Endpoint: POST /api/crear-oferta.php
 * Requiere: Autenticación + Rol ID 3 (AdministradorIFTS)
 *
 * Body JSON:
 * {
 *   "tituloOferta": "Desarrollador Backend",
 *   "textoOferta": "Descripción de la oferta..."
 * }
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Mailer.php';
require_once __DIR__ . '/../models/BolsaTrabajo.php';

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

    // Solo el rol 3 (AdministradorIFTS) puede publicar ofertas
    $idRol = (int)($_SESSION['id_rol'] ?? 0);
    if ($idRol !== 3) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Solo las instituciones registradas pueden publicar ofertas laborales']);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true);

    $titulo = trim((string)($payload['tituloOferta'] ?? ''));
    $texto  = trim((string)($payload['textoOferta'] ?? ''));

    if ($titulo === '' || $texto === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El título y el texto de la oferta son obligatorios']);
        exit;
    }

    if (mb_strlen($titulo) > 512 || mb_strlen($texto) > 512) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El título y el texto no pueden superar los 512 caracteres']);
        exit;
    }

    $idInstitucion = (int)($_SESSION['id_institucion'] ?? 0);
    $idUsuario     = (int)($_SESSION['id_usuario'] ?? 0);

    if ($idInstitucion <= 0 || $idUsuario <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos de sesión inválidos']);
        exit;
    }

    $db  = Database::getInstance();
    $pdo = $db->getConnection();

    $idOferta = BolsaTrabajo::crearOferta($pdo, $idInstitucion, $idUsuario, $titulo, $texto);

    if (!$idOferta) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'No se pudo guardar la oferta']);
        exit;
    }

    // Notificar a la institución que la oferta fue recibida y está en revisión
    $emailIFTS  = $_SESSION['email'] ?? '';
    $nombreIFTS = $_SESSION['nombre'] ?? $_SESSION['email'] ?? '';
    $mailEnviado = false;

    if ($emailIFTS !== '') {
        try {
            $mailer = new Mailer();
            $mailEnviado = $mailer->notificarOfertaRecibida($emailIFTS, $nombreIFTS, $titulo);
        } catch (Exception $e) {
            error_log("Error enviando mail oferta recibida: " . $e->getMessage());
        }
    }

    echo json_encode([
        'success'          => true,
        'message'          => 'Oferta enviada correctamente. Será revisada por el administrador antes de publicarse.',
        'id_bolsaDeTrabajo' => $idOferta,
        'mail_enviado'     => $mailEnviado
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error'   => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
