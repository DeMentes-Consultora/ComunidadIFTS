<?php
/**
 * API: Perfil institucional/global de ofertas
 * Endpoint: GET /api/perfil-institucion.php
 * Requiere: Autenticacion + Rol 1 o 3
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Institucion.php';
require_once __DIR__ . '/../models/BolsaTrabajo.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
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
    $idInstitucion = (int)($_SESSION['id_institucion'] ?? 0);

    if (!in_array($idRol, [1, 3], true)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tiene permisos para acceder a este perfil']);
        exit;
    }

    $pdo = Database::getInstance()->getConnection();

    if ($idRol === 1) {
        $resumen = BolsaTrabajo::obtenerResumenPublicadasGlobal($pdo);
        $postulaciones = BolsaTrabajo::obtenerPostulacionesPublicadasGlobal($pdo);

        echo json_encode([
            'success' => true,
            'data' => [
                'institucion' => [
                    'id' => 0,
                    'nombre' => 'ComunidadIFTS (Global)',
                    'direccion' => null,
                    'telefono' => null,
                    'email' => null,
                    'logo' => null,
                ],
                'puede_editar_institucion' => false,
                'resumen' => $resumen,
                'postulaciones' => $postulaciones,
            ],
        ]);
        exit;
    }

    if ($idInstitucion <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No se encontro una institucion asociada al usuario']);
        exit;
    }

    $institucion = Institucion::buscarPorId($pdo, $idInstitucion);
    if (!$institucion) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Institucion no encontrada']);
        exit;
    }

    $resumen = BolsaTrabajo::obtenerResumenPublicadasPorInstitucion($pdo, $idInstitucion);
    $postulaciones = BolsaTrabajo::obtenerPostulacionesPublicadasPorInstitucion($pdo, $idInstitucion);

    echo json_encode([
        'success' => true,
        'data' => [
            'institucion' => $institucion->toArray(),
            'puede_editar_institucion' => true,
            'resumen' => $resumen,
            'postulaciones' => $postulaciones,
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
    ]);
}
