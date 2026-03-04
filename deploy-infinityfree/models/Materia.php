<?php
/**
 * Modelo de Materia para gestión de carreras
 */

class Materia
{
    public static function obtenerTodas($pdo)
    {
        $stmt = $pdo->query(
            "SELECT id_materia, nombre_materia
             FROM materia
             WHERE cancelado = 0
             ORDER BY nombre_materia ASC"
        );

        return $stmt->fetchAll();
    }

    public static function obtenerPorCarrera($pdo)
    {
        $stmt = $pdo->query(
            "SELECT
                cm.id_carrera,
                m.id_materia,
                m.nombre_materia
             FROM carrera_materia cm
             INNER JOIN materia m ON m.id_materia = cm.id_materia
             WHERE cm.cancelado = 0
               AND m.cancelado = 0"
        );

        return $stmt->fetchAll();
    }

    public static function asociarACarrera($pdo, $idCarrera, $idMateria)
    {
        $stmtExiste = $pdo->prepare(
            "SELECT id_carrera_materia
             FROM carrera_materia
             WHERE id_carrera = ? AND id_materia = ?
             LIMIT 1"
        );
        $stmtExiste->execute([$idCarrera, $idMateria]);
        $row = $stmtExiste->fetch();

        if ($row) {
            $stmtReactivar = $pdo->prepare(
                "UPDATE carrera_materia
                 SET cancelado = 0, habilitado = 1
                 WHERE id_carrera_materia = ?"
            );

            return $stmtReactivar->execute([$row['id_carrera_materia']]);
        }

        $stmtInsert = $pdo->prepare(
            "INSERT INTO carrera_materia (id_carrera, id_materia, habilitado, cancelado)
             VALUES (?, ?, 1, 0)"
        );

        return $stmtInsert->execute([$idCarrera, $idMateria]);
    }

    public static function desasociarDeCarrera($pdo, $idCarrera, $idMateria)
    {
        $stmt = $pdo->prepare(
            "UPDATE carrera_materia
             SET cancelado = 1, habilitado = 0
             WHERE id_carrera = ? AND id_materia = ?"
        );

        return $stmt->execute([$idCarrera, $idMateria]);
    }
}
