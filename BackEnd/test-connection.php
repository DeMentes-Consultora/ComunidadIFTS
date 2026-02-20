<?php
/**
 * Script de prueba de conexión a la base de datos
 * Ejecutar: php test-connection.php
 */

require_once __DIR__ . '/config/database.php';

echo "===================================\n";
echo "Test de Conexión a Base de Datos\n";
echo "===================================\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "✅ Conexión exitosa!\n\n";
    
    // Probar consulta simple
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    
    echo "📊 Base de datos actual: " . $result['db_name'] . "\n\n";
    
    // Verificar tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Tablas encontradas:\n";
    foreach ($tables as $table) {
        echo "   - $table\n";
    }
    
    echo "\n";
    
    // Contar instituciones
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM institucion");
    $count = $stmt->fetch();
    echo "🏫 Total de instituciones: " . $count['total'] . "\n";
    
    // Contar carreras
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM carrera");
    $count = $stmt->fetch();
    echo "📚 Total de carreras: " . $count['total'] . "\n";
    
    echo "\n✅ Test completado exitosamente!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
