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

        public static function obtenerActivos($pdo) {
                $sql = "SELECT id_rol, nombre_rol
                                FROM rol
                                WHERE habilitado = 1
                                    AND cancelado = 0
                                ORDER BY nombre_rol ASC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public static function obtenerNombreActivoPorId($pdo, $idRol) {
                $sql = "SELECT nombre_rol
                                FROM rol
                                WHERE id_rol = ?
                                    AND habilitado = 1
                                    AND cancelado = 0
                                LIMIT 1";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([(int)$idRol]);

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['nombre_rol'] ?? null;
        }
}
