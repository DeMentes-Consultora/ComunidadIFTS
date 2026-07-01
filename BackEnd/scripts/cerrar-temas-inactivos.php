<?php
/**
 * Script: Cerrar temas inactivos del foro
 * 
 * Cierra automáticamente temas que no recibieron respuesta en N días
 * y envía un email de notificación al creador.
 * 
 * Uso:
 *   php BackEnd/scripts/cerrar-temas-inactivos.php
 *   php BackEnd/scripts/cerrar-temas-inactivos.php --dias=10
 * 
 * Cron job recomendado (diario a las 3 AM):
 *   0 3 * * * cd /path/to/BackEnd && php scripts/cerrar-temas-inactivos.php
 */

// Cargar entorno
$dotenvPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($dotenvPath)) {
    fwrite(STDERR, "Error: vendor/autoload.php no encontrado. Ejecutá composer install.\n");
    exit(1);
}
require_once $dotenvPath;

$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ForoTema.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../services/ForoEmailService.php';

// Parsear argumentos
$dias = 7;
foreach ($argv as $arg) {
    if (strpos($arg, '--dias=') === 0) {
        $dias = max(1, (int)substr($arg, 7));
    }
}

echo "=== Cierre automático de temas inactivos ===\n";
echo "Días de inactividad: $dias\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $temasInactivos = ForoTema::obtenerTemasInactivos($pdo, $dias);
    $total = count($temasInactivos);

    if ($total === 0) {
        echo "No se encontraron temas inactivos.\n";
        exit(0);
    }

    echo "Temas inactivos encontrados: $total\n\n";

    $emailService = new ForoEmailService();
    $cerrados = 0;
    $emailsEnviados = 0;
    $emailsFallidos = 0;

    foreach ($temasInactivos as $tema) {
        $idTema = (int)$tema['id_tema'];
        $titulo = $tema['titulo'];
        $email = $tema['autor_email'] ?? '';
        $nombre = $tema['autor_nombre'] ?? 'Usuario';

        echo "Procesando tema #$idTema: $titulo\n";

        // Cerrar tema
        $motivo = "Cerrado automáticamente por inactividad ($dias días sin respuesta)";
        ForoTema::cerrar($pdo, $idTema, $motivo);
        $cerrados++;
        echo "  -> Tema cerrado\n";

        // Enviar email
        if (!empty($email)) {
            $exito = $emailService->notificarTemaCerradoPorInactividad(
                $email,
                $nombre,
                $titulo,
                $dias
            );

            if ($exito) {
                $emailsEnviados++;
                echo "  -> Email enviado a $email\n";
            } else {
                $emailsFallidos++;
                echo "  -> Error al enviar email a $email\n";
            }
        } else {
            echo "  -> Sin email, se omite notificación\n";
        }
    }

    echo "\n=== Resumen ===\n";
    echo "Temas cerrados: $cerrados\n";
    echo "Emails enviados: $emailsEnviados\n";
    echo "Emails fallidos: $emailsFallidos\n";

} catch (Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
