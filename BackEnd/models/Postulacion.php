<?php
/**
 * Modelo Postulacion
 *
 * Maneja toda la persistencia de la tabla `postulacion`.
 * Una postulación representa que un alumno se postuló a una oferta laboral.
 */
class Postulacion {

    // ------------------------------------------------------------------
    // LECTURA
    // ------------------------------------------------------------------

    /**
     * Verifica si un alumno ya está postulado a una oferta.
     */
    public static function yaPostulado(PDO $pdo, int $idOferta, int $idUsuario): bool {
        $stmt = $pdo->prepare(
            "SELECT id_postulacion FROM postulacion
             WHERE id_bolsaDeTrabajo = ? AND id_usuario = ? AND cancelado = 0
             LIMIT 1"
        );
        $stmt->execute([$idOferta, $idUsuario]);
        return (bool)$stmt->fetch();
    }

    /**
     * Obtiene todas las postulaciones de una oferta con datos del alumno.
     */
    public static function obtenerPorOferta(PDO $pdo, int $idOferta): array {
        $sql = "SELECT
                    ps.id_postulacion,
                    ps.cv_url,
                    ps.idCreate AS fecha_postulacion,
                    u.id_usuario,
                    u.email,
                    p.nombre,
                    p.apellido,
                    p.telefono
                FROM postulacion ps
                INNER JOIN usuario u ON ps.id_usuario = u.id_usuario
                INNER JOIN persona p ON u.id_persona = p.id_persona
                WHERE ps.id_bolsaDeTrabajo = ?
                  AND ps.cancelado = 0
                ORDER BY ps.idCreate DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idOferta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las ofertas a las que se postuló un alumno (solo publicadas activas).
     * Usado en el perfil de alumno: desaparece si la oferta fue deshabilitada.
     */
    public static function obtenerOfertasDeAlumno(PDO $pdo, int $idUsuario): array {
        $sql = "SELECT
                    b.id_bolsaDeTrabajo,
                    b.tituloOferta,
                    b.textoOferta,
                    i.nombre_ifts,
                    i.email_ifts,
                    i.logo_ifts,
                    ps.idCreate AS fecha_postulacion,
                    ps.cv_url
                FROM postulacion ps
                INNER JOIN bolsadetrabajo b ON ps.id_bolsaDeTrabajo = b.id_bolsaDeTrabajo
                INNER JOIN institucion i ON b.id_institucion = i.id_institucion
                WHERE ps.id_usuario = ?
                  AND ps.cancelado = 0
                  AND b.habilitado = 1
                  AND b.cancelado = 0
                ORDER BY ps.idCreate DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta las postulaciones activas de una oferta.
     */
    public static function contarPorOferta(PDO $pdo, int $idOferta): int {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM postulacion
             WHERE id_bolsaDeTrabajo = ? AND cancelado = 0"
        );
        $stmt->execute([$idOferta]);
        return (int)$stmt->fetchColumn();
    }

    // ------------------------------------------------------------------
    // ESCRITURA
    // ------------------------------------------------------------------

    /**
     * Registra una postulación.
     * Retorna el ID insertado o null en caso de error.
     */
    public static function crearPostulacion(PDO $pdo, int $idOferta, int $idUsuario, ?string $cvUrl, ?string $cvPublicId): ?int {
        $sql = "INSERT INTO postulacion
                    (id_bolsaDeTrabajo, id_usuario, cv_url, cv_public_id, cancelado)
                VALUES (?, ?, ?, ?, 0)";

        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([$idOferta, $idUsuario, $cvUrl, $cvPublicId]);
        return $ok ? (int)$pdo->lastInsertId() : null;
    }
}
