<?php
/**
 * API: Obtener todas las carreras
 * Endpoint: GET /api/carreras.php
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Carrera.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Obtener todas las carreras (método estático)
    $carreras = Carrera::obtenerTodas($pdo);

    // Convertir objetos a arrays para JSON
    $data = array_map(function($carrera) {
        return $carrera->toArray();
    }, $carreras);

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener carreras',
        'error' => $_ENV['APP_DEBUG'] ? $e->getMessage() : null
    ]);
}
