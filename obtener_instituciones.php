<?php
header('Content-Type: application/json');

// Incluir archivo de conexión centralizado
require 'conexion.php';

try {
    // Consulta para obtener instituciones y sus carreras concatenadas
    // Usamos un separador '|||' para evitar conflictos con comas en los nombres
    $sql = "SELECT i.*, GROUP_CONCAT(c.nombre SEPARATOR '|||') as lista_carreras 
            FROM instituciones i 
            LEFT JOIN instituciones_carreras ic ON i.id = ic.institucion_id 
            LEFT JOIN carreras c ON ic.carrera_id = c.id 
            GROUP BY i.id";

    $stmt = $pdo->query($sql);
    $instituciones = $stmt->fetchAll();

    // Procesar la lista de carreras para convertirla en array
    foreach ($instituciones as &$inst) {
        if ($inst['lista_carreras']) {
            $inst['carreras'] = explode('|||', $inst['lista_carreras']);
        } else {
            $inst['carreras'] = [];
        }
        unset($inst['lista_carreras']); // Limpiar el campo auxiliar
    }

    echo json_encode(['success' => true, 'data' => $instituciones]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al cargar datos: ' . $e->getMessage()]);
}
?>