<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../models/BolsaTrabajo.php';

$pdo = Database::getInstance()->getConnection();

echo "=== Ofertas PUBLICADAS (habilitado=1, cancelado=0) ANTES ===\n";
$ofertas = BolsaTrabajo::obtenerPublicadas($pdo);
echo "Cantidad: " . count($ofertas) . "\n";
foreach ($ofertas as $o) {
    echo "  ID: {$o['id_bolsaDeTrabajo']} | {$o['tituloOferta']}\n";
}

if (count($ofertas) > 0) {
    $ofertaTest = $ofertas[0];
    $idTest = $ofertaTest['id_bolsaDeTrabajo'];
    
    echo "\n=== TEST: Deshabilitar oferta $idTest ===\n";
    $ok = BolsaTrabajo::deshabilitarOferta($pdo, $idTest);
    echo "Resultado: " . var_export($ok, true) . "\n";
    
    echo "\n=== Verificar estado en BD ===\n";
    $stmt = $pdo->query("SELECT id_bolsaDeTrabajo, habilitado, cancelado FROM bolsadetrabajo WHERE id_bolsaDeTrabajo = $idTest");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Oferta $idTest: habilitado=" . $row['habilitado'] . ", cancelado=" . $row['cancelado'] . "\n";
    
    echo "\n=== Ofertas PUBLICADAS DESPUÉS (debería estar vacío) ===\n";
    $ofertas2 = BolsaTrabajo::obtenerPublicadas($pdo);
    echo "Cantidad: " . count($ofertas2) . "\n";
    foreach ($ofertas2 as $o) {
        echo "  ID: {$o['id_bolsaDeTrabajo']} | {$o['tituloOferta']}\n";
    }
    
    // Re-habilitar para no afectar datos reales
    echo "\n=== Re-habilitando oferta $idTest ===\n";
    $restore = $pdo->prepare("UPDATE bolsadetrabajo SET habilitado = 1 WHERE id_bolsaDeTrabajo = ?");
    $restore->execute([$idTest]);
}
