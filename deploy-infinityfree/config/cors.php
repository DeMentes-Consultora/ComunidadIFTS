<?php
/**
 * Configuración de CORS para permitir peticiones desde el frontend Angular
 */

// Resolver origin de la petición actual
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// En desarrollo, permitir frontend local con credenciales
if (getenv('APP_ENV') === 'production') {
    // En producción, usar configuración estricta del .env
    if (!isset($_ENV['CORS_ALLOWED_ORIGINS'])) {
        require_once __DIR__ . '/../vendor/autoload.php';
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->safeLoad();
    }
    
    $allowedOriginsString = $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'https://comunidadifts.infinityfreeapp.com';
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

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400");

// Manejar peticiones OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
