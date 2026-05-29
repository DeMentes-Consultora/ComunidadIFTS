<?php

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Mailer.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/PasswordReset.php';

header('Content-Type: application/json');

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'CLI';

if ($requestMethod !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

function requestPasswordResetResponse(): array {
    return [
        'success' => true,
        'message' => 'Si el email corresponde a una cuenta activa, te enviaremos un enlace para restablecer tu contraseña.'
    ];
}

function isLocalPasswordResetDebugEnabled(): bool {
    $appEnv = strtolower(trim((string)($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? '')));
    $appDebug = $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?? false;
    $debugEnabled = $appDebug === true || $appDebug === 'true' || $appDebug === '1' || $appDebug === 1;

    return $debugEnabled || ($appEnv !== '' && $appEnv !== 'production');
}

try {
    $payload = json_decode(file_get_contents('php://input'), true);
    $email = trim((string)($payload['email'] ?? $_POST['email'] ?? ''));

    if ($email === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El email es obligatorio'
        ]);
        exit;
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $usuario = Usuario::buscarPorEmailParaRecupero($pdo, $email);
    if (!$usuario) {
        echo json_encode(requestPasswordResetResponse());
        exit;
    }

    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    if (!PasswordReset::crear($pdo, (int)$usuario->getIdUsuario(), $token, $expiresAt)) {
        throw new RuntimeException('No se pudo guardar el token de recupero.');
    }

    $appUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?? ''), '/');
    if ($appUrl === '') {
        throw new RuntimeException('APP_URL no está configurada.');
    }

    $resetLink = $appUrl . '/resetear?token=' . urlencode($token);
    $nombre = trim((string)$usuario->getNombre());
    $nombreDestinatario = $nombre !== '' ? $nombre : $usuario->getEmail();
    $asunto = 'Recuperación de contraseña - ComunidadIFTS';
    $cuerpoHTML = "<p>Hola {$nombreDestinatario},</p>"
        . "<p>Recibimos una solicitud para restablecer tu contraseña en ComunidadIFTS.</p>"
        . "<p><a href=\"{$resetLink}\">Haz clic aquí para crear una nueva contraseña</a></p>"
        . "<p>El enlace expirará en 1 hora. Si no solicitaste este cambio, podés ignorar este mensaje.</p>";
    $cuerpoTexto = "Hola {$nombreDestinatario},\n\n"
        . "Recibimos una solicitud para restablecer tu contraseña en ComunidadIFTS.\n"
        . "Usá este enlace para crear una nueva contraseña:\n{$resetLink}\n\n"
        . "El enlace expirará en 1 hora. Si no solicitaste este cambio, podés ignorar este mensaje.";

    $debugEnabled = isLocalPasswordResetDebugEnabled();
    $response = requestPasswordResetResponse();

    if ($debugEnabled) {
        $response['reset_link'] = $resetLink;
        $response['warning'] = 'Modo desarrollo: no se envía email y se devuelve el enlace de prueba para validar el flujo local.';
        echo json_encode($response);
        exit;
    }

    $mailer = new Mailer();
    if (!$mailer->enviar($usuario->getEmail(), $nombreDestinatario, $asunto, $cuerpoHTML, $cuerpoTexto)) {
        throw new RuntimeException($mailer->getLastError() ?: 'No fue posible enviar el email de recupero.');
    }

    echo json_encode($response);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'No fue posible iniciar el recupero de contraseña.',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}