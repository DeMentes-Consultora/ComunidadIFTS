<?php
/**
 * Modelo de Rol - consultas de soporte para autenticacion y registro
 */

class Rol {
    const ID_ALUMNO_REGULAR = 2;

    public static function obtenerActivoPorId($pdo, $idRol) {
        $sql = "SELECT id_rol
                FROM rol
                WHERE id_rol = ?
                  AND habilitado = 1
                  AND cancelado = 0
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idRol]);

        $row = $stmt->fetch();
        return $row ? (int)$row['id_rol'] : null;
    }

    public static function obtenerRolAlumnoActivo($pdo) {
        return self::obtenerActivoPorId($pdo, self::ID_ALUMNO_REGULAR);
    }
}
