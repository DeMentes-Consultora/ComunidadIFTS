<?php

/**
 * Configuración de conexión a la base de datos
 * Usa variables de entorno para seguridad
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        // Cargar variables de entorno
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
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

    private function handleError(\PDOException $e)
    {
        // En producción, no mostrar detalles del error
        $debug = $_ENV['APP_DEBUG'] ?? false;

        if ($debug) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error de conexión a la base de datos',
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
