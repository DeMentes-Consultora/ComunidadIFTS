<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../models/BolsaTrabajo.php';

$pdo = Database::getInstance()->getConnection();

echo "=== SIMULACIÓN COMPLETA DEL FLUJO FRONTEND ===\n\n";

// 1. Obtener ofertas publicadas (lo que ve el admin al cargar la página)
echo "PASO 1: Admin carga la página de PUBLICADAS\n";
$publicadas = BolsaTrabajo::obtenerPublicadas($pdo);
echo "  - Cantidad de ofertas: " . count($publicadas) . "\n";
$ofertaParaPrueba = $publicadas[0] ?? null;

if (!$ofertaParaPrueba) {
    echo "ERROR: No hay ofertas publicadas para probar\n";
    exit(1);
}

$idOferta = $ofertaParaPrueba['id_bolsaDeTrabajo'];
echo "  - Primera oferta: ID=" . $idOferta . ", Título=" . $ofertaParaPrueba['tituloOferta'] . "\n";

// Verificar estado antes en BD
$stmt = $pdo->query("SELECT habilitado, cancelado FROM bolsadetrabajo WHERE id_bolsaDeTrabajo = $idOferta");
$estadoAntes = $stmt->fetch(PDO::FETCH_ASSOC);
echo "  - Estado en BD: habilitado=" . $estadoAntes['habilitado'] . ", cancelado=" . $estadoAntes['cancelado'] . "\n\n";

// 2. Usuario hace click en toggle para DESHABILITAR
echo "PASO 2: Usuario desliza toggle para DESHABILITAR (OFF)\n";
echo "  - Frontend envía: accion='deshabilitar', id=" . $idOferta . "\n";

// Simular la transacción que hace el backend
$pdo->beginTransaction();
try {
    $result = BolsaTrabajo::deshabilitarOferta($pdo, $idOferta);
    $pdo->commit();
    echo "  - Backend ejecuta: BolsaTrabajo::deshabilitarOferta() → " . ($result ? "SUCCESS" : "FAIL") . "\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "  - ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Verificar estado en BD después del update
$stmt = $pdo->query("SELECT habilitado, cancelado FROM bolsadetrabajo WHERE id_bolsaDeTrabajo = $idOferta");
$estadoDespues = $stmt->fetch(PDO::FETCH_ASSOC);
echo "  - Estado en BD DESPUÉS de UPDATE: habilitado=" . $estadoDespues['habilitado'] . ", cancelado=" . $estadoDespues['cancelado'] . "\n\n";

// 3. Frontend recibe success y recarga la lista
echo "PASO 3: Frontend recibe success y refresca PUBLICADAS\n";
$publicadas2 = BolsaTrabajo::obtenerPublicadas($pdo);
echo "  - Query: SELECT FROM bolsadetrabajo WHERE habilitado=1 AND cancelado=0\n";
echo "  - Resultado: " . count($publicadas2) . " ofertas\n";

if (count($publicadas2) === count($publicadas) - 1) {
    echo "  ✓ CORRECTO: La oferta deshabilitada desapareció\n";
} else {
    echo "  ✗ ERROR: La oferta NO desapareció (tenía " . count($publicadas) . ", ahora tiene " . count($publicadas2) . ")\n";
}

echo "\n  Ofertas restantes:\n";
foreach ($publicadas2 as $o) {
    echo "    - ID=" . $o['id_bolsaDeTrabajo'] . ", Título=" . $o['tituloOferta'] . "\n";
}

// 4. Simular que el usuario refresca la página (F5)
echo "\nPASO 4: Usuario refresca la página (F5)\n";
echo "  - Frontend hace GET /api/ofertas-pendientes?seccion=publicadas\n";

// Esperar un momento para simular latencia
usleep(100000);

$publicadas3 = BolsaTrabajo::obtenerPublicadas($pdo);
echo "  - Resultado: " . count($publicadas3) . " ofertas publicadas\n";

if (in_array($idOferta, array_column($publicadas3, 'id_bolsaDeTrabajo'))) {
    echo "  ✗ PROBLEMA ENCONTRADO: La oferta VUELVE A APARECER después de refresh\n";
} else {
    echo "  ✓ CORRECTO: La oferta sigue deshabilitada después de refresh\n";
}

// Restaurar estado original
echo "\nRESTAURACIÓN: Volviendo a habilitar la oferta para no afectar datos reales\n";
$pdo->prepare("UPDATE bolsadetrabajo SET habilitado=1 WHERE id_bolsaDeTrabajo=?")->execute([$idOferta]);
echo "  ✓ Oferta restaurada a habilitado=1\n";

// Resumán final
echo "\n=== RESUMEN ===\n";
echo "Si viste ✗ PROBLEMA, el issue está en:\n";
echo "  1. Base de datos no está guardando el UPDATE\n";
echo "  2. O hay múltiples conexiones con diferente estado de transacción\n";
echo "  3. O hay un problem con réplica/caché de BD\n";
