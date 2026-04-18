<?php
/**
 * API: Listado de ofertas para el panel admin
 * Endpoint: GET /api/ofertas-pendientes.php
 * Requiere: Autenticación + Rol ID 1 (AdministradorComunidad)
 *
 * Query params opcionales:
 *   ?seccion=pendientes  -> habilitado=0, cancelado=0 (default)
 *   ?seccion=publicadas  -> habilitado=1, cancelado=0
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/BolsaTrabajo.php';

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

    $idRol = (int)($_SESSION['id_rol'] ?? 0);
    if ($idRol !== 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para gestionar ofertas laborales']);
        exit;
    }

    $seccion = $_GET['seccion'] ?? 'pendientes';
    $db      = Database::getInstance();
    $pdo     = $db->getConnection();

    if ($seccion === 'publicadas') {
        $ofertas = BolsaTrabajo::obtenerPublicadasAdmin($pdo);
    } else {
        $ofertas = BolsaTrabajo::obtenerPendientes($pdo);
    }

    // Formatear fechas
    foreach ($ofertas as &$oferta) {
        if (!empty($oferta['fecha_creacion'])) {
            $oferta['fecha_creacion_formateada'] = (new DateTime($oferta['fecha_creacion']))->format('d/m/Y H:i');
        }
    }
    unset($oferta);

    echo json_encode([
        'success' => true,
        'seccion' => $seccion,
        'data'    => $ofertas,
        'total'   => count($ofertas)
    ]);
} catch (Throwable $e) {
    error_log('Error ofertas-pendientes.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error'   => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
