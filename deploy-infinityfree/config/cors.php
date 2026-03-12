<?php
/**
 * Configuración de CORS para permitir peticiones desde el frontend Angular
 */

require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($_ENV['APP_ENV']) || !isset($_ENV['CORS_ALLOWED_ORIGINS'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
}

// Resolver origin de la petición actual
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'development';
$defaultOrigins = 'http://localhost:4200,http://localhost:3000,https://comunidadifts.infinityfreeapp.com,http://comunidadifts.infinityfreeapp.com';

// En desarrollo, permitir frontend local con credenciales
if ($appEnv === 'production') {
    // En producción, usar configuración estricta del .env

    $allowedOriginsString = $_ENV['CORS_ALLOWED_ORIGINS'] ?? $defaultOrigins;
    $allowedOrigins = array_map('trim', explode(',', $allowedOriginsString));

    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
} else {
    // En desarrollo, devolver el origin exacto (no usar * con credenciales)
    if ($origin !== '') {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        header("Access-Control-Allow-Origin: http://localhost:4200");
    }
}

header('Vary: Origin');
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400");

// Manejar peticiones OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
