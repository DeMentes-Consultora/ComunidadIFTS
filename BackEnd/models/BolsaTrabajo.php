<?php
/**
 * Modelo BolsaTrabajo
 *
 * Maneja toda la persistencia de la tabla `bolsadetrabajo`.
 * Estados:
 *   habilitado=0, cancelado=0 -> PENDIENTE
 *   habilitado=1, cancelado=0 -> PUBLICADA
 *   habilitado=0, cancelado=1 -> RECHAZADA/baja
 */
class BolsaTrabajo {

    // ------------------------------------------------------------------
    // LECTURA
    // ------------------------------------------------------------------

    /**
     * Ofertas pendientes de aprobación (habilitado=0, cancelado=0).
     * Solo visible para el administrador.
     */
    public static function obtenerPendientes(PDO $pdo): array {
        $sql = "SELECT
                b.id_bolsaDeTrabajo,
                b.tituloOferta,
                b.textoOferta,
                b.habilitado,
                b.cancelado,
                b.idCreate AS fecha_creacion,
                i.id_institucion,
                i.nombre_ifts,
                i.email_ifts,
                u.id_usuario AS id_usuario_creador,
                p.nombre AS nombre_creador,
                p.apellido AS apellido_creador
            FROM bolsadetrabajo b
            INNER JOIN institucion i ON b.id_institucion = i.id_institucion
            INNER JOIN usuario u ON b.id_usuario = u.id_usuario
            INNER JOIN persona p ON u.id_persona = p.id_persona
            WHERE b.habilitado = 0
              AND b.cancelado = 0
            ORDER BY b.idCreate DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ofertas publicadas (habilitado=1, cancelado=0), con cantidad de postulaciones.
     * Visible para alumnos. El admin también las ve en su panel de "Publicadas".
     */
    public static function obtenerPublicadas(PDO $pdo): array {
        $sql = "SELECT
                    b.id_bolsaDeTrabajo,
                    b.tituloOferta,
                    b.textoOferta,
                    b.habilitado,
                    b.cancelado,
                    b.idCreate AS fecha_creacion,
                    i.id_institucion,
                    i.nombre_ifts,
                    i.email_ifts,
                    i.logo_ifts,
                    (SELECT COUNT(*) FROM postulacion ps
                     WHERE ps.id_bolsaDeTrabajo = b.id_bolsaDeTrabajo
                       AND ps.cancelado = 0) AS total_postulaciones
                FROM bolsadetrabajo b
                INNER JOIN institucion i ON b.id_institucion = i.id_institucion
                                WHERE b.habilitado = 1
                  AND b.cancelado = 0
                ORDER BY b.idCreate DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Todas las publicadas enriquecidas con postulaciones para el admin.
     */
    public static function obtenerPublicadasAdmin(PDO $pdo): array {
        return self::obtenerPublicadas($pdo);
    }

    /**
     * Una oferta por ID (cualquier estado).
     */
    public static function obtenerPorId(PDO $pdo, int $id): ?array {
        $sql = "SELECT
                    b.*,
                    i.nombre_ifts,
                    i.email_ifts,
                    i.logo_ifts
                FROM bolsadetrabajo b
                INNER JOIN institucion i ON b.id_institucion = i.id_institucion
                WHERE b.id_bolsaDeTrabajo = ?
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
     * Crea una oferta laboral en estado PENDIENTE (habilitado=0, cancelado=0).
     * Retorna el ID insertado o null en caso de error.
     */
    public static function crearOferta(PDO $pdo, int $idInstitucion, int $idUsuario, string $titulo, string $texto): ?int {
        $sql = "INSERT INTO bolsadetrabajo
                    (id_institucion, id_usuario, tituloOferta, textoOferta, habilitado, cancelado)
                VALUES (?, ?, ?, ?, 0, 0)";

        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([$idInstitucion, $idUsuario, $titulo, $texto]);
        return $ok ? (int)$pdo->lastInsertId() : null;
    }

    /**
     * Publica una oferta pendiente (habilitado=1, cancelado=0).
     */
    public static function publicarOferta(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare(
            "UPDATE bolsadetrabajo
             SET habilitado = 1, cancelado = 0, idUpdate = CURRENT_TIMESTAMP
             WHERE id_bolsaDeTrabajo = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Rechaza/da de baja una oferta (habilitado=0, cancelado=1).
     */
    public static function rechazarOferta(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare(
            "UPDATE bolsadetrabajo
             SET habilitado = 0, cancelado = 1, idUpdate = CURRENT_TIMESTAMP
             WHERE id_bolsaDeTrabajo = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Deshabilita una oferta publicada (toggle: habilitado=0, cancelado=0).
     * La oferta queda "pausada" y vuelve al pool de pendientes visualmente,
     * pero con cancelado=0 para poder re-publicarla si hace falta.
     */
    public static function deshabilitarOferta(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare(
            "UPDATE bolsadetrabajo
             SET habilitado = 0, idUpdate = CURRENT_TIMESTAMP
             WHERE id_bolsaDeTrabajo = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Resumen de la actividad de ofertas PUBLICADAS de una institucion.
     */
    public static function obtenerResumenPublicadasPorInstitucion(PDO $pdo, int $idInstitucion): array {
        $sql = "SELECT
                    COUNT(DISTINCT b.id_bolsaDeTrabajo) AS total_ofertas_publicadas,
                    COUNT(ps.id_postulacion) AS total_postulantes
                FROM bolsadetrabajo b
                LEFT JOIN postulacion ps
                       ON ps.id_bolsaDeTrabajo = b.id_bolsaDeTrabajo
                      AND ps.cancelado = 0
                WHERE b.id_institucion = ?
                  AND b.habilitado = 1
                  AND b.cancelado = 0";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idInstitucion]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_ofertas_publicadas' => (int)($row['total_ofertas_publicadas'] ?? 0),
            'total_postulantes' => (int)($row['total_postulantes'] ?? 0),
        ];
    }

    /**
     * Postulaciones activas de ofertas PUBLICADAS de una institucion.
     */
    public static function obtenerPostulacionesPublicadasPorInstitucion(PDO $pdo, int $idInstitucion): array {
        $sql = "SELECT
                    ps.id_postulacion,
                    b.id_bolsaDeTrabajo,
                    b.tituloOferta,
                    p.apellido AS apellido_postulante,
                    p.nombre AS nombre_postulante,
                    i.nombre_ifts,
                    u.email AS email_postulante,
                    ps.cv_url,
                    p.foto_perfil_url
                FROM bolsadetrabajo b
                INNER JOIN institucion i ON i.id_institucion = b.id_institucion
                INNER JOIN postulacion ps
                        ON ps.id_bolsaDeTrabajo = b.id_bolsaDeTrabajo
                       AND ps.cancelado = 0
                INNER JOIN usuario u ON u.id_usuario = ps.id_usuario
                INNER JOIN persona p ON p.id_persona = u.id_persona
                WHERE b.id_institucion = ?
                  AND b.habilitado = 1
                  AND b.cancelado = 0
                ORDER BY b.idCreate DESC, ps.idCreate DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idInstitucion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Resumen global de ofertas publicadas y postulaciones activas.
     */
    public static function obtenerResumenPublicadasGlobal(PDO $pdo): array {
        $sql = "SELECT
                    COUNT(DISTINCT b.id_bolsaDeTrabajo) AS total_ofertas_publicadas,
                    COUNT(ps.id_postulacion) AS total_postulantes
                FROM bolsadetrabajo b
                LEFT JOIN postulacion ps
                       ON ps.id_bolsaDeTrabajo = b.id_bolsaDeTrabajo
                      AND ps.cancelado = 0
                WHERE b.habilitado = 1
                  AND b.cancelado = 0";

        $stmt = $pdo->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_ofertas_publicadas' => (int)($row['total_ofertas_publicadas'] ?? 0),
            'total_postulantes' => (int)($row['total_postulantes'] ?? 0),
        ];
    }

    /**
     * Postulaciones activas de todas las ofertas publicadas.
     */
    public static function obtenerPostulacionesPublicadasGlobal(PDO $pdo): array {
        $sql = "SELECT
                    ps.id_postulacion,
                    b.id_bolsaDeTrabajo,
                    b.tituloOferta,
                    p.apellido AS apellido_postulante,
                    p.nombre AS nombre_postulante,
                    i.nombre_ifts,
                    u.email AS email_postulante,
                    ps.cv_url,
                    p.foto_perfil_url
                FROM bolsadetrabajo b
                INNER JOIN institucion i ON i.id_institucion = b.id_institucion
                INNER JOIN postulacion ps
                        ON ps.id_bolsaDeTrabajo = b.id_bolsaDeTrabajo
                       AND ps.cancelado = 0
                INNER JOIN usuario u ON u.id_usuario = ps.id_usuario
                INNER JOIN persona p ON p.id_persona = u.id_persona
                WHERE b.habilitado = 1
                  AND b.cancelado = 0
                ORDER BY b.idCreate DESC, ps.idCreate DESC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
