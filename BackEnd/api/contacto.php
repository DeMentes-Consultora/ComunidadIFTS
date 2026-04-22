<?php

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/Mailer.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metodo no permitido',
    ]);
    exit;
}

try {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        $payload = [];
    }

    $nombre = trim((string)($payload['nombre'] ?? ''));
    $email = trim((string)($payload['email'] ?? ''));
    $asunto = trim((string)($payload['asunto'] ?? ''));
    $mensaje = trim((string)($payload['mensaje'] ?? ''));

    if ($nombre === '' || $email === '' || $asunto === '' || $mensaje === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Completa todos los campos obligatorios',
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email invalido',
        ]);
        exit;
    }

    if (mb_strlen($mensaje) < 10) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El mensaje debe tener al menos 10 caracteres',
        ]);
        exit;
    }

    $mailer = new Mailer();

    $adminEmail = trim((string)($_ENV['ADMIN_EMAIL'] ?? getenv('ADMIN_EMAIL') ?: ''));
    if ($adminEmail === '') {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'No se encontro ADMIN_EMAIL para enviar la consulta',
        ]);
        exit;
    }

    $nombreSafe = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    $emailSafe = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $asuntoSafe = htmlspecialchars($asunto, ENT_QUOTES, 'UTF-8');
    $mensajeSafe = nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));

    $mailAsunto = 'Consulta web ComunidadIFTS: ' . $asunto;
    $mailHtml = "
        <h2>Nueva consulta desde el formulario de contacto</h2>
        <p><strong>Nombre:</strong> {$nombreSafe}</p>
        <p><strong>Email:</strong> {$emailSafe}</p>
        <p><strong>Asunto:</strong> {$asuntoSafe}</p>
        <p><strong>Mensaje:</strong></p>
        <p>{$mensajeSafe}</p>
    ";
    $mailText = "Nueva consulta desde el formulario de contacto\n\n"
        . "Nombre: {$nombre}\n"
        . "Email: {$email}\n"
        . "Asunto: {$asunto}\n\n"
        . "Mensaje:\n{$mensaje}\n";

    $enviado = $mailer->enviar($adminEmail, 'Administrador ComunidadIFTS', $mailAsunto, $mailHtml, $mailText);
    if (!$enviado) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo enviar la consulta por email',
            'error' => $mailer->getLastError(),
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Consulta enviada correctamente',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null,
    ]);
}
