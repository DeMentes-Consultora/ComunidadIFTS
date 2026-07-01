<?php
/**
 * Modelo ForoAdjunto
 *
 * Maneja toda la persistencia de la tabla `foro_adjunto`.
 * Tipos soportados: imagen (jpg/jpeg/png), pdf, video (mp4/webm/mov).
 */
class ForoAdjunto {

    // ------------------------------------------------------------------
    // LECTURA
    // ------------------------------------------------------------------

    /**
     * Adjuntos de un tema.
     */
    public static function obtenerPorTema(PDO $pdo, int $idTema): array {
        $sql = "SELECT * FROM foro_adjunto
                WHERE id_tema = ? AND habilitado = 1
                ORDER BY idCreate ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idTema]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adjuntos de una respuesta.
     */
    public static function obtenerPorRespuesta(PDO $pdo, int $idRespuesta): array {
        $sql = "SELECT * FROM foro_adjunto
                WHERE id_respuesta = ? AND habilitado = 1
                ORDER BY idCreate ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idRespuesta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Un adjunto por ID.
     */
    public static function obtenerPorId(PDO $pdo, int $id): ?array {
        $sql = "SELECT * FROM foro_adjunto WHERE id_adjunto = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ------------------------------------------------------------------
    // ESCRITURA
    // ------------------------------------------------------------------

    /**
     * Registra un adjunto. Retorna el ID insertado o null en caso de error.
     */
    public static function crear(PDO $pdo, string $tipo, string $url, ?string $publicId, string $nombreOriginal, int $tamanoBytes, ?int $idTema = null, ?int $idRespuesta = null): ?int {
        $sql = "INSERT INTO foro_adjunto
                    (id_tema, id_respuesta, tipo, archivo_url, archivo_public_id, archivo_nombre_original, archivo_tamano_bytes)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([$idTema, $idRespuesta, $tipo, $url, $publicId, $nombreOriginal, $tamanoBytes]);
        return $ok ? (int)$pdo->lastInsertId() : null;
    }

    /**
     * Elimina (soft delete) un adjunto.
     */
    public static function eliminar(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare(
            "UPDATE foro_adjunto
             SET habilitado = 0
             WHERE id_adjunto = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Elimina todos los adjuntos de un tema (soft delete).
     */
    public static function eliminarPorTema(PDO $pdo, int $idTema): bool {
        $stmt = $pdo->prepare(
            "UPDATE foro_adjunto
             SET habilitado = 0
             WHERE id_tema = ?"
        );
        return $stmt->execute([$idTema]);
    }

    /**
     * Elimina todos los adjuntos de una respuesta (soft delete).
     */
    public static function eliminarPorRespuesta(PDO $pdo, int $idRespuesta): bool {
        $stmt = $pdo->prepare(
            "UPDATE foro_adjunto
             SET habilitado = 0
             WHERE id_respuesta = ?"
        );
        return $stmt->execute([$idRespuesta]);
    }
}
