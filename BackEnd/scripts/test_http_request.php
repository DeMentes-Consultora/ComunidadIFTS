<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../models/BolsaTrabajo.php';

echo "=== TEST HTTP PUT A gestionar-oferta.php ===\n\n";

$pdo = Database::getInstance()->getConnection();

// Obtener una oferta para probar
$publicadas = BolsaTrabajo::obtenerPublicadas($pdo);
if (empty($publicadas)) {
    echo "ERROR: No hay ofertas publicadas\n";
    exit(1);
}

$oferta = $publicadas[0];
$idOferta = $oferta['id_bolsaDeTrabajo'];

echo "Oferta de prueba: ID=" . $idOferta . " - " . $oferta['tituloOferta'] . "\n";
echo "Estado inicial: habilitado=1, cancelado=0\n\n";

// SIMULAR: $_SERVER configuration para una solicitud PUT
$_SERVER['REQUEST_METHOD'] = 'PUT';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// SIMULAR: Payload JSON que envía el frontend
$payload = [
    'id_bolsaDeTrabajo' => $idOferta,
    'accion' => 'deshabilitar'
];

$input = json_encode($payload);
echo "Simulando solicitud PUT:\n";
echo "  Endpoint: /api/gestionar-oferta.php\n";
echo "  Body: " . json_encode($payload) . "\n";
echo "  Headers: Content-Type: application/json\n\n";

// SIMULAR: Verificar permisos (id_rol = 1)
if (!isset($_SESSION)) {
    @session_start();
}
$_SESSION['id_rol'] = 1;  // Admin
$_SESSION['logged_in'] = true;

echo "Sesión simulada: id_rol=1 (Admin), logged_in=true\n\n";

// ========== EJECUTAR: Simular todo el contenido de gestionar-oferta.php ==========
echo "EJECUTANDO: Código de gestionar-oferta.php...\n\n";

$statusCode = 200;
$responseData = ['success' => false];

// Verificar autenticación
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo "✗ ERROR: Usuario no autenticado\n";
    exit(1);
}

// Verificar rol de admin
if ($_SESSION['id_rol'] != 1) {
    echo "✗ ERROR: Usuario no es administrador (rol=" . $_SESSION['id_rol'] . ")\n";
    exit(1);
}

echo "✓ Autenticación OK (rol=1)\n";

// Obtener payload
$payload = json_decode($input, true);

if (!$payload) {
    echo "✗ ERROR: JSON inválido\n";
    exit(1);
}

$idOferta = intval($payload['id_bolsaDeTrabajo'] ?? 0);
$accion = trim((string)($payload['accion'] ?? ''));

if ($idOferta <= 0 || !in_array($accion, ['aprobar', 'rechazar', 'deshabilitar'])) {
    echo "✗ ERROR: id_bolsaDeTrabajo=" . $idOferta . ", accion=" . $accion . "\n";
    exit(1);
}

echo "✓ Validación OK (id=$idOferta, accion=$accion)\n\n";

// Ejecutar la acción
try {
    $pdo->beginTransaction();
    
    switch ($accion) {
        case 'deshabilitar':
            echo "Ejecutando: BolsaTrabajo::deshabilitarOferta($idOferta)...\n";
            $result = BolsaTrabajo::deshabilitarOferta($pdo, $idOferta);
            
            if (!$result) {
                throw new Exception("deshabilitarOferta devolvió false");
            }
            echo "  ✓ UPDATE ejecutado correctamente\n";
            
            // Verificar en BD
            $stmt = $pdo->query("SELECT habilitado, cancelado FROM bolsadetrabajo WHERE id_bolsaDeTrabajo = $idOferta");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "  ✓ Estado en BD después de UPDATE: habilitado=" . $row['habilitado'] . ", cancelado=" . $row['cancelado'] . "\n";
            
            $pdo->commit();
            echo "\n✓ Transacción completada\n";
            
            $responseData = [
                'success' => true,
                'message' => 'Oferta deshabilitada correctamente',
                'accion' => 'deshabilitar'
            ];
            break;
            
        default:
            throw new Exception("Acción no soportada en este test");
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    $responseData = ['success' => false, 'message' => $e->getMessage()];
    $statusCode = 400;
}

// SIMULAR: Respuesta del servidor
echo "\nRespuesta HTTP al cliente:\n";
echo "  Status Code: " . $statusCode . "\n";
echo "  Body: " . json_encode($responseData) . "\n\n";

// ========== VERIFICACIÓN FINAL ==========
echo "=== VERIFICACIÓN FINAL ===\n";

// Recargar la lista de publicadas
echo "\nRealizando SELECT para obtenerPublicadas():\n";
$publicadas2 = BolsaTrabajo::obtenerPublicadas($pdo);
echo "  Cantidad: " . count($publicadas2) . "\n";
echo "  ¿Desapareció la oferta? " . (!in_array($idOferta, array_column($publicadas2, 'id_bolsaDeTrabajo')) ? "✓ SÍ" : "✗ NO") . "\n";

// Restaurar
$restore = $pdo->prepare("UPDATE bolsadetrabajo SET habilitado=1 WHERE id_bolsaDeTrabajo=?");
$restore->execute([$idOferta]);
echo "\n✓ Oferta restaurada para no afectar datos\n";
