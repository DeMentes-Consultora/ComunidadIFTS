<?php
/**
 * API: Obtener todas las instituciones
 * Endpoint: GET /api/instituciones.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Institucion.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Obtener todas las instituciones (método estático)
    $instituciones = Institucion::obtenerTodas($pdo);

    // Convertir objetos a arrays para JSON
    $data = array_map(function($inst) {
        return $inst->toArray();
    }, $instituciones);

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener instituciones',
        'error' => $_ENV['APP_DEBUG'] ? $e->getMessage() : null
    ]);
}
