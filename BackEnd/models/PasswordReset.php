<?php

class PasswordReset {
    public static function crear(PDO $pdo, int $idUsuario, string $token, string $expiresAt): bool {
        self::eliminarExpirados($pdo);

        $deleteStmt = $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?');
        $deleteStmt->execute([$idUsuario]);

        $stmt = $pdo->prepare(
            'INSERT INTO password_resets (user_id, token, expires_at, used) VALUES (?, ?, ?, 0)'
        );

        return $stmt->execute([$idUsuario, $token, $expiresAt]);
    }

    public static function obtenerPorToken(PDO $pdo, string $token): ?array {
        self::eliminarExpirados($pdo);

        $stmt = $pdo->prepare(
            'SELECT id, user_id, token, expires_at, used
             FROM password_resets
             WHERE token = ?
               AND expires_at > NOW()
               AND used = 0
             LIMIT 1'
        );
        $stmt->execute([$token]);

        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        return $reset ?: null;
    }

    public static function marcarComoUsado(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare('UPDATE password_resets SET used = 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function eliminarExpirados(PDO $pdo): bool {
        $stmt = $pdo->prepare('DELETE FROM password_resets WHERE expires_at <= NOW() OR used = 1');
        return $stmt->execute();
    }
}