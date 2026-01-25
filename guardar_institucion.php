<?php
header('Content-Type: application/json');

// Incluir archivo de conexión centralizado
require 'conexion.php';

// Recibir JSON del frontend
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Insertar Institución
    $stmt = $pdo->prepare("INSERT INTO instituciones (nombre, direccion, telefono, email, sitio_web, observaciones, latitud, longitud, logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $input['nombre'] ?? '',
        $input['direccion'] ?? '',
        $input['telefono'] ?? '',
        $input['email'] ?? '',
        $input['sitio_web'] ?? '',
        $input['observaciones'] ?? '',
        !empty($input['latitud']) ? $input['latitud'] : null,
        !empty($input['longitud']) ? $input['longitud'] : null,
        $input['logo'] ?? ''
    ]);
    
    $institucion_id = $pdo->lastInsertId();

    // 2. Procesar Carreras (Muchos a Muchos)
    if (!empty($input['carreras'])) {
        foreach ($input['carreras'] as $nombre_carrera) {
            $nombre_carrera = trim($nombre_carrera);
            if (empty($nombre_carrera)) continue;

            // Verificar si la carrera ya existe en el catálogo
            $stmtCheck = $pdo->prepare("SELECT id FROM carreras WHERE nombre = ?");
            $stmtCheck->execute([$nombre_carrera]);
            $carrera = $stmtCheck->fetch();

            if ($carrera) {
                // Si la carrera ya existe, usamos su ID
                $carrera_id = $carrera['id'];
            } else {
                // Si no existe, la insertamos en la tabla 'carreras' y obtenemos su nuevo ID
                $stmtNew = $pdo->prepare("INSERT INTO carreras (nombre) VALUES (?)");
                $stmtNew->execute([$nombre_carrera]);
                $carrera_id = $pdo->lastInsertId();
            }

            // Finalmente, creamos la relación en la tabla intermedia 'instituciones_carreras'.
            // Como el ID de la institución es nuevo, no hay riesgo de duplicar la relación aquí.
            $stmtRel = $pdo->prepare("INSERT INTO instituciones_carreras (institucion_id, carrera_id) VALUES (?, ?)");
            $stmtRel->execute([$institucion_id, $carrera_id]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Institución guardada correctamente', 'id' => $institucion_id]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
}
?>