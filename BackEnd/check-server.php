<?php
/**
 * Script de verificación del servidor
 * Verifica que el entorno esté configurado correctamente
 * ELIMINAR DESPUÉS DEL DESPLIEGUE POR SEGURIDAD
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Servidor - ComunidadIFTS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .header p { opacity: 0.9; font-size: 14px; }
        .content { padding: 30px; }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .section h2 { color: #333; margin-bottom: 15px; font-size: 20px; }
        .check-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin: 8px 0;
            background: white;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .check-item .icon { width: 24px; height: 24px; margin-right: 12px; flex-shrink: 0; }
        .check-item .label { flex: 1; font-weight: 500; color: #333; }
        .check-item .value { color: #666; font-family: 'Courier New', monospace; font-size: 13px; }
        .success { border-left: 3px solid #28a745; }
        .success .icon {
            background: #28a745;
            border-radius: 50%;
            position: relative;
        }
        .success .icon::after {
            content: '✓';
            color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 16px;
        }
        .error { border-left: 3px solid #dc3545; }
        .error .icon {
            background: #dc3545;
            border-radius: 50%;
            position: relative;
        }
        .error .icon::after {
            content: '✗';
            color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 16px;
        }
        .warning {
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .warning strong { color: #856404; }
        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Verificación del Servidor</h1>
            <p>ComunidadIFTS - InfinityFree Deployment Check</p>
        </div>

        <div class="content">
            <?php
            $allGood = true;

            // Verificar versión de PHP
            echo '<div class="section"><h2>📦 Información del Sistema</h2>';
            
            $phpVersion = phpversion();
            $phpOk = version_compare($phpVersion, '7.4.0', '>=');
            $class = $phpOk ? 'success' : 'error';
            if (!$phpOk) $allGood = false;
            
            echo "<div class='check-item $class'>";
            echo "<div class='icon'></div>";
            echo "<div class='label'>PHP Version</div>";
            echo "<div class='value'>$phpVersion " . ($phpOk ? '(OK)' : '(Requiere >= 7.4)') . "</div>";
            echo "</div>";

            // Verificar extensiones de PHP
            $extensions = ['mysqli', 'pdo', 'pdo_mysql', 'json', 'mbstring'];
            foreach ($extensions as $ext) {
                $loaded = extension_loaded($ext);
                $class = $loaded ? 'success' : 'error';
                if (!$loaded) $allGood = false;
                
                echo "<div class='check-item $class'>";
                echo "<div class='icon'></div>";
                echo "<div class='label'>Extensión: $ext</div>";
                echo "<div class='value'>" . ($loaded ? 'Cargada' : 'No disponible') . "</div>";
                echo "</div>";
            }
            echo '</div>';

            // Verificar archivos y carpetas
            echo '<div class="section"><h2>📁 Archivos y Carpetas</h2>';
            
            $paths = [
                '.env' => 'Archivo de configuración',
                'vendor/autoload.php' => 'Composer autoload',
                'config/database.php' => 'Configuración de BD',
                'config/cors.php' => 'Configuración CORS',
                'api' => 'Carpeta API',
            ];

            foreach ($paths as $path => $description) {
                $exists = file_exists(__DIR__ . '/' . $path);
                $class = $exists ? 'success' : 'error';
                if (!$exists) $allGood = false;
                
                echo "<div class='check-item $class'>";
                echo "<div class='icon'></div>";
                echo "<div class='label'>$description</div>";
                echo "<div class='value'>$path " . ($exists ? '(Existe)' : '(No encontrado)') . "</div>";
                echo "</div>";
            }
            echo '</div>';

            // Verificar conexión a base de datos
            echo '<div class="section"><h2>🗄️ Base de Datos</h2>';
            
            try {
                if (file_exists(__DIR__ . '/.env')) {
                    require_once __DIR__ . '/vendor/autoload.php';
                    
                    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
                    $dotenv->load();
                    
                    $host = $_ENV['DB_HOST'] ?? 'not set';
                    $dbname = $_ENV['DB_NAME'] ?? 'not set';
                    $user = $_ENV['DB_USER'] ?? 'not set';
                    $pass = $_ENV['DB_PASS'] ?? '';
                    
                    echo "<div class='check-item'>";
                    echo "<div class='icon'></div>";
                    echo "<div class='label'>DB Host</div>";
                    echo "<div class='value'>$host</div>";
                    echo "</div>";
                    
                    echo "<div class='check-item'>";
                    echo "<div class='icon'></div>";
                    echo "<div class='label'>DB Name</div>";
                    echo "<div class='value'>$dbname</div>";
                    echo "</div>";
                    
                    echo "<div class='check-item'>";
                    echo "<div class='icon'></div>";
                    echo "<div class='label'>DB User</div>";
                    echo "<div class='value'>$user</div>";
                    echo "</div>";
                    
                    try {
                        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
                        $pdo = new PDO($dsn, $user, $pass, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                        ]);
                        
                        echo "<div class='check-item success'>";
                        echo "<div class='icon'></div>";
                        echo "<div class='label'>Conexión a BD</div>";
                        echo "<div class='value'>Exitosa ✓</div>";
                        echo "</div>";
                        
                        $stmt = $pdo->query("SHOW TABLES");
                        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        echo "<div class='check-item success'>";
                        echo "<div class='icon'></div>";
                        echo "<div class='label'>Tablas encontradas</div>";
                        echo "<div class='value'>" . count($tables) . " tablas</div>";
                        echo "</div>";
                        
                    } catch (PDOException $e) {
                        $allGood = false;
                        echo "<div class='check-item error'>";
                        echo "<div class='icon'></div>";
                        echo "<div class='label'>Conexión a BD</div>";
                        echo "<div class='value'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                        echo "</div>";
                    }
                } else {
                    $allGood = false;
                    echo "<div class='check-item error'>";
                    echo "<div class='icon'></div>";
                    echo "<div class='label'>Archivo .env</div>";
                    echo "<div class='value'>No encontrado</div>";
                    echo "</div>";
                }
            } catch (Exception $e) {
                $allGood = false;
                echo "<div class='check-item error'>";
                echo "<div class='icon'></div>";
                echo "<div class='label'>Error general</div>";
                echo "<div class='value'>" . htmlspecialchars($e->getMessage()) . "</div>";
                echo "</div>";
            }
            
            echo '</div>';

            // Resumen final
            echo '<div class="section"><h2>📊 Resumen</h2>';
            
            if ($allGood) {
                echo "<div class='check-item success'>";
                echo "<div class='icon'></div>";
                echo "<div class='label'>Estado General</div>";
                echo "<div class='value'>¡Todo configurado correctamente! ✓</div>";
                echo "</div>";
            } else {
                echo "<div class='check-item error'>";
                echo "<div class='icon'></div>";
                echo "<div class='label'>Estado General</div>";
                echo "<div class='value'>Se encontraron errores que deben corregirse</div>";
                echo "</div>";
            }
            
            echo '</div>';

            echo '<div class="warning">';
            echo '<strong>⚠️ IMPORTANTE:</strong> Este archivo muestra información sensible. ';
            echo 'Elimínalo después de verificar que todo funciona correctamente.';
            echo '</div>';
            ?>
        </div>

        <div class="footer">
            <p>ComunidadIFTS © 2026 - DeMentes Consultora</p>
            <p style="margin-top: 5px; font-size: 12px;">Generado: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
