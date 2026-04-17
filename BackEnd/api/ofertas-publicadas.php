<?php
/**
 * API: Listado de ofertas publicadas (visibles para alumnos)
 * Endpoint: GET /api/ofertas-publicadas.php
 * Requiere: Autenticación + Rol alumno (2)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/BolsaTrabajo.php';
require_once __DIR__ . '/../models/Postulacion.php';

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

    // Solo alumnos pueden ver las ofertas publicadas
    $idRol            = (int)($_SESSION['id_rol'] ?? 0);
    $rolesPermitidos  = [2];

    if (!in_array($idRol, $rolesPermitidos)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Solo los alumnos pueden acceder a las ofertas laborales']);
        exit;
    }

    $idUsuario = (int)($_SESSION['id_usuario'] ?? 0);

    $db  = Database::getInstance();
    $pdo = $db->getConnection();

    $ofertas = BolsaTrabajo::obtenerPublicadas($pdo);

    // Marcar si el alumno ya se postuló a cada oferta
    foreach ($ofertas as &$oferta) {
        $oferta['ya_postulado'] = Postulacion::yaPostulado($pdo, (int)$oferta['id_bolsaDeTrabajo'], $idUsuario);

        // Formatear fecha
        if (!empty($oferta['fecha_creacion'])) {
            $oferta['fecha_creacion_formateada'] = (new DateTime($oferta['fecha_creacion']))->format('d/m/Y');
        }
    }
    unset($oferta);

    echo json_encode([
        'success' => true,
        'data'    => $ofertas,
        'total'   => count($ofertas)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error'   => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}
