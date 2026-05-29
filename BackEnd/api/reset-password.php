<?php

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/PasswordReset.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'CLI';

    if ($requestMethod === 'GET') {
        $token = trim((string)($_GET['token'] ?? ''));
        if ($token === '') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'El token es obligatorio'
            ]);
            exit;
        }

        $reset = PasswordReset::obtenerPorToken($pdo, $token);
        if (!$reset) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'El enlace de recuperación es inválido o expiró.'
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Token válido'
        ]);
        exit;
    }

    if ($requestMethod !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido'
        ]);
        exit;
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    $token = trim((string)($payload['token'] ?? $_POST['token'] ?? ''));
    $password = (string)($payload['password'] ?? $_POST['password'] ?? '');
    $confirmPassword = (string)($payload['confirm_password'] ?? $_POST['confirm_password'] ?? '');

    if ($token === '' || $password === '' || $confirmPassword === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Token y contraseñas son obligatorios'
        ]);
        exit;
    }

    if ($password !== $confirmPassword) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Las contraseñas no coinciden'
        ]);
        exit;
    }

    $passwordMinLength = (int)($_ENV['PASSWORD_MIN_LENGTH'] ?? getenv('PASSWORD_MIN_LENGTH') ?: 6);
    if (mb_strlen($password) < $passwordMinLength) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'La contraseña debe tener al menos ' . $passwordMinLength . ' caracteres'
        ]);
        exit;
    }

    $reset = PasswordReset::obtenerPorToken($pdo, $token);
    if (!$reset) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'El enlace de recuperación es inválido o expiró.'
        ]);
        exit;
    }

    $pdo->beginTransaction();

    if (!Usuario::actualizarClavePorId($pdo, (int)$reset['user_id'], $password)) {
        $pdo->rollBack();
        throw new RuntimeException('No se pudo actualizar la contraseña del usuario.');
    }

    if (!PasswordReset::marcarComoUsado($pdo, (int)$reset['id'])) {
        $pdo->rollBack();
        throw new RuntimeException('No se pudo invalidar el token utilizado.');
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Tu contraseña fue actualizada correctamente.'
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'No fue posible restablecer la contraseña.',
        'error' => ($_ENV['APP_DEBUG'] ?? false) ? $e->getMessage() : null
    ]);
}