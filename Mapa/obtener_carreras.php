<?php
header('Content-Type: application/json');

// Incluir archivo de conexión centralizado
require 'conexion.php';

try {
    // Consulta para obtener todas las carreras existentes, ordenadas alfabéticamente
    $stmt = $pdo->query("SELECT nombre FROM carreras ORDER BY nombre ASC");
    $carreras = $stmt->fetchAll(PDO::FETCH_COLUMN); // Obtener solo la columna 'nombre'

    // Devolvemos un array simple de nombres, que es lo que espera nuestro script de guardado
    // y lo que facilita el uso en el frontend.
    echo json_encode([
        'success' => true, 
        'data' => $carreras
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cargar carreras: ' . $e->getMessage()]);
}
?>