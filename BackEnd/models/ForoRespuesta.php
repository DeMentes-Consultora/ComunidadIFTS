<?php
/**
 * Modelo ForoRespuesta
 *
 * Maneja toda la persistencia de la tabla `foro_respuesta`.
 */
class ForoRespuesta {

    // ------------------------------------------------------------------
    // LECTURA
    // ------------------------------------------------------------------

    /**
     * Respuestas de un tema, paginadas.
     */
    public static function obtenerPorTema(PDO $pdo, int $idTema, int $page = 1, int $limit = 20): array {
        $offset = ($page - 1) * $limit;

        // Total
        $sqlCount = "SELECT COUNT(*) FROM foro_respuesta
                     WHERE id_tema = ? AND habilitado = 1 AND cancelado = 0";
        $stmtCount = $pdo->prepare($sqlCount);
        $stmtCount->execute([$idTema]);
        $total = (int)$stmtCount->fetchColumn();

        // Datos
        $sql = "SELECT
                    r.*,
                    p.nombre AS autor_nombre,
                    p.apellido AS autor_apellido,
                    p.foto_perfil_url AS autor_foto,
                    u.id_rol AS autor_rol,
                    cr.contenido AS citando_contenido,
                    cp.nombre AS citando_autor_nombre,
                    cp.apellido AS citando_autor_apellido
                FROM foro_respuesta r
                INNER JOIN usuario u ON r.id_usuario = u.id_usuario
                INNER JOIN persona p ON u.id_persona = p.id_persona
                LEFT JOIN foro_respuesta cr ON r.citando_id = cr.id_respuesta
                LEFT JOIN usuario cu ON cr.id_usuario = cu.id_usuario
                LEFT JOIN persona cp ON cu.id_persona = cp.id_persona
                WHERE r.id_tema = ? AND r.habilitado = 1 AND r.cancelado = 0
                ORDER BY r.idCreate ASC
                LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idTema]);

        return [
            'respuestas' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => (int)ceil($total / $limit)
        ];
    }

    /**
     * Una respuesta por ID.
     */
    public static function obtenerPorId(PDO $pdo, int $id): ?array {
        $sql = "SELECT
                    r.*,
                    p.nombre AS autor_nombre,
                    p.apellido AS autor_apellido,
                    p.foto_perfil_url AS autor_foto,
                    u.id_rol AS autor_rol
                FROM foro_respuesta r
                INNER JOIN usuario u ON r.id_usuario = u.id_usuario
                INNER JOIN persona p ON u.id_persona = p.id_persona
                WHERE r.id_respuesta = ?
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ------------------------------------------------------------------
    // ESCRITURA
    // ------------------------------------------------------------------

    /**
     * Crea una respuesta. Retorna el ID insertado o null en caso de error.
     */
    public static function crear(PDO $pdo, int $idTema, int $idUsuario, string $contenido, ?int $citandoId = null): ?int {
        $sql = "INSERT INTO foro_respuesta (id_tema, id_usuario, contenido, citando_id)
                VALUES (?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([$idTema, $idUsuario, $contenido, $citandoId]);
        return $ok ? (int)$pdo->lastInsertId() : null;
    }

    /**
     * Actualiza una respuesta existente.
     */
    public static function actualizar(PDO $pdo, int $id, string $contenido): bool {
        $stmt = $pdo->prepare(
            "UPDATE foro_respuesta
             SET contenido = ?, idUpdate = CURRENT_TIMESTAMP
             WHERE id_respuesta = ?"
        );
        return $stmt->execute([$contenido, $id]);
    }

    /**
     * Elimina (soft delete) una respuesta.
     */
    public static function eliminar(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare(
            "UPDATE foro_respuesta
             SET cancelado = 1, idUpdate = CURRENT_TIMESTAMP
             WHERE id_respuesta = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Cuenta las respuestas activas de un tema.
     */
    public static function contarPorTema(PDO $pdo, int $idTema): int {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM foro_respuesta
             WHERE id_tema = ? AND habilitado = 1 AND cancelado = 0"
        );
        $stmt->execute([$idTema]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Obtiene la última respuesta de un tema.
     */
    public static function obtenerUltimaPorTema(PDO $pdo, int $idTema): ?array {
        $sql = "SELECT
                    r.*,
                    p.nombre AS autor_nombre,
                    p.apellido AS autor_apellido
                FROM foro_respuesta r
                INNER JOIN usuario u ON r.id_usuario = u.id_usuario
                INNER JOIN persona p ON u.id_persona = p.id_persona
                WHERE r.id_tema = ? AND r.habilitado = 1 AND r.cancelado = 0
                ORDER BY r.idCreate DESC
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idTema]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
