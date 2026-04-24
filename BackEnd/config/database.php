<?php

/**
 * Configuración de conexión a la base de datos
 * Usa variables de entorno para seguridad
 */

$autoloadCandidates = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
];

$autoloadLoaded = false;
foreach ($autoloadCandidates as $autoloadFile) {
    if (is_file($autoloadFile)) {
        require_once $autoloadFile;
        $autoloadLoaded = true;
        break;
    }
}

use Dotenv\Dotenv;

class Database
{
    private static $instance = null;
    private $connection;

    private static function envValue(array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
                return $_ENV[$key];
            }

            $serverKey = getenv($key);
            if ($serverKey !== false && $serverKey !== '') {
                return $serverKey;
            }
        }

        return $default;
    }

    private static function loadEnvFile(string $envPath): void
    {
        if (!is_file($envPath)) {
            return;
        }

        $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $value = trim($value, "\"'");

            if ($key !== '' && !isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                @putenv($key . '=' . $value);
            }
        }
    }

    private function __construct()
    {
        try {
            // Cargar variables de entorno desde backend o raíz del deploy
            $envPaths = [
                __DIR__ . '/../',
                __DIR__ . '/../../',
            ];

            foreach ($envPaths as $envPath) {
                if (is_file($envPath . '.env')) {
                    if (class_exists('Dotenv\\Dotenv')) {
                        $dotenv = Dotenv::createImmutable($envPath);
                        if (method_exists($dotenv, 'safeLoad')) {
                            $dotenv->safeLoad();
                        } else {
                            $dotenv->load();
                        }
                    } else {
                        self::loadEnvFile($envPath . '.env');
                    }
                    break;
                }
            }

            $host = (string) self::envValue(['DB_HOST'], 'localhost');
            $dbname = (string) self::envValue(['DB_NAME'], '');
            $user = (string) self::envValue(['DB_USER', 'DB_USERNAME'], '');
            $pass = (string) self::envValue(['DB_PASS', 'DB_PASSWORD'], '');
            $charset = (string) self::envValue(['DB_CHARSET'], 'utf8mb4');

            if ($dbname === '' || $user === '') {
                throw new \RuntimeException('Faltan variables DB_NAME y/o DB_USER(DB_USERNAME) en .env');
            }

            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function handleError(\Throwable $e)
    {
        // En producción, no mostrar detalles del error
        $debug = $_ENV['APP_DEBUG'] ?? false;
        $debugEnabled = $debug === true || $debug === 'true' || $debug === '1' || $debug === 1;
        error_log('Database bootstrap error: ' . $e->getMessage());

        if ($debugEnabled) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error de conexión a la base de datos o configuración',
                'error' => $e->getMessage()
            ]);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error de conexión al servidor'
            ]);
        }
        exit;
    }
    public function getConnection()
    {
        return $this->connection;
    }
    // Prevenir clonación del singleton
    private function __clone() {}

    // Prevenir unserialize del singleton
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
