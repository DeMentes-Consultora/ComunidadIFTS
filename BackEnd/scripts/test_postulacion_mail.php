<?php
/**
 * Test rapido para validar envio de mail de confirmacion al alumno.
 *
 * Uso:
 *   php scripts/test_postulacion_mail.php --to=alumno@correo.com --nombre="Alumno Test"
 *
 * Parametros opcionales:
 *   --titulo="Puesto QA"
 *   --ifts="IFTS 15"
 *   --env-file=.env.production
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/Mailer.php';

$options = getopt('', ['to:', 'nombre::', 'titulo::', 'ifts::', 'env-file::']);

$to = trim((string)($options['to'] ?? ''));
if ($to === '') {
    fwrite(STDERR, "Falta parametro obligatorio --to\n");
    fwrite(STDERR, "Ejemplo: php scripts/test_postulacion_mail.php --to=alumno@correo.com\n");
    exit(1);
}

$envFile = trim((string)($options['env-file'] ?? '.env'));
$envPath = __DIR__ . '/../' . ltrim($envFile, '/\\');

if (file_exists($envPath)) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname($envPath), basename($envPath));
    $dotenv->safeLoad();
} elseif (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

$nombre = trim((string)($options['nombre'] ?? 'Alumno Test'));
$titulo = trim((string)($options['titulo'] ?? 'Oferta Laboral de Prueba'));
$ifts = trim((string)($options['ifts'] ?? 'IFTS Test'));

try {
    $mailer = new Mailer();
    $enviado = $mailer->notificarPostulacionAlumno($to, $nombre, $titulo, $ifts);

    $result = [
        'success' => $enviado,
        'to' => $to,
        'nombre' => $nombre,
        'titulo' => $titulo,
        'ifts' => $ifts,
        'error' => $enviado ? null : $mailer->getLastError()
    ];

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit($enviado ? 0 : 2);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(3);
}
