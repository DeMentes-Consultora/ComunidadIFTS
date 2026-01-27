<?php
// Configuración de conexión para InfinityFree
// Estos datos se obtienen del panel de control (VistaPanel) -> MySQL Databases

$host = 'sql302.infinityfree.com';
$db   = 'if0_40899760_comunidad_ifts'; // Verifica que este sea el nombre exacto creado en el panel
$user = 'if0_40899760';
$pass = 'MapaPassIfts'; // REEMPLAZAR: Pon aquí tu contraseña real del panel (la que estaba oculta)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si falla la conexión, devolvemos un error JSON y terminamos
    if (!headers_sent()) header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit;
}
?>