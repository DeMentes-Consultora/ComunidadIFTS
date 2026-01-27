<?php
/**
 * Configuración de CORS para permitir peticiones desde el frontend Angular
 */

// Cargar variables de entorno si no están cargadas
if (!isset($_ENV['CORS_ALLOWED_ORIGINS'])) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Obtener orígenes permitidos desde .env
$allowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:4200');

// Obtener el origen de la petición
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Verificar si el origen está permitido
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Por defecto permitir localhost en desarrollo
    if ($_ENV['APP_ENV'] === 'development') {
        header("Access-Control-Allow-Origin: *");
    }
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400"); // Cache preflight por 24 horas

// Manejar peticiones OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
