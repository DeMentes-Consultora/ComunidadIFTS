<?php
/**
 * API pública: estadísticas generales del sitio (sin autenticación)
 * Endpoint: GET /api/stats-publicas.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $db  = Database::getInstance();
    $pdo = $db->getConnection();

    $sql = 'SELECT
                (SELECT COUNT(*) FROM institucion   WHERE cancelado = 0)                           AS instituciones,
                (SELECT COUNT(*) FROM carrera        WHERE cancelado = 0)                           AS carreras,
                (SELECT COUNT(*) FROM usuario        WHERE cancelado = 0 AND id_rol = 2)            AS alumnos,
                (SELECT COUNT(*) FROM bolsadetrabajo WHERE habilitado = 1 AND cancelado = 0)        AS ofertas_publicadas,
                (SELECT COUNT(*) FROM postulacion    WHERE cancelado = 0)                           AS postulantes';

    $stmt = $pdo->query($sql);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    echo json_encode([
        'success' => true,
        'data'    => [
            'instituciones'      => (int)($row['instituciones']      ?? 0),
            'carreras'           => (int)($row['carreras']           ?? 0),
            'alumnos'            => (int)($row['alumnos']            ?? 0),
            'ofertas_publicadas' => (int)($row['ofertas_publicadas'] ?? 0),
            'postulantes'        => (int)($row['postulantes']        ?? 0),
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error'   => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
    ]);
}
