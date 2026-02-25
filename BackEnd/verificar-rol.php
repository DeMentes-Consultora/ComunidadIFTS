<?php
/**
 * Script para verificar los roles en la base de datos
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "========== VERIFICACIÓN DE ROLES ==========\n\n";
    
    // Consultar todos los roles
    $stmt = $pdo->query("SELECT * FROM rol ORDER BY id_rol");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "TODOS LOS ROLES:\n";
    foreach ($roles as $rol) {
        echo sprintf(
            "ID: %d | Nombre: %s | Habilitado: %d | Cancelado: %d\n",
            $rol['id_rol'],
            $rol['nombre_rol'],
            $rol['habilitado'],
            $rol['cancelado']
        );
    }
    
    echo "\n========== ROL ALUMNO HABILITADO ==========\n";
    
    // Buscar rol Alumno habilitado
    $stmt = $pdo->prepare("SELECT id_rol FROM rol WHERE nombre_rol = 'Alumno' AND habilitado = 1 AND cancelado = 0 LIMIT 1");
    $stmt->execute();
    $rolAlumno = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($rolAlumno) {
        echo "✓ Rol Alumno encontrado con ID: " . $rolAlumno['id_rol'] . "\n";
    } else {
        echo "✗ No se encontró rol Alumno habilitado\n";
        
        // Ver si existe pero con otros estados
        $stmt = $pdo->prepare("SELECT * FROM rol WHERE nombre_rol = 'Alumno'");
        $stmt->execute();
        $rolesAlumno = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rolesAlumno) > 0) {
            echo "\nSe encontraron roles Alumno con otros estados:\n";
            foreach ($rolesAlumno as $rol) {
                echo sprintf(
                    "  ID: %d | Habilitado: %d | Cancelado: %d\n",
                    $rol['id_rol'],
                    $rol['habilitado'],
                    $rol['cancelado']
                );
            }
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
