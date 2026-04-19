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

    public static function obtenerEstadoGestion($pdo)
    {
        $stmtCarreras = $pdo->query(
            "SELECT id_carrera, nombre_carrera
             FROM carrera
             WHERE cancelado = 0
             ORDER BY nombre_carrera ASC"
        );
        $carrerasRows = $stmtCarreras->fetchAll();

        $materiasRows = self::obtenerTodas($pdo);
        $relaciones = self::obtenerPorCarrera($pdo);

        $materiasPorCarrera = [];
        $materiasAsignadas = [];

        foreach ($relaciones as $relacion) {
            $idCarrera = (int)$relacion['id_carrera'];
            $idMateria = (int)$relacion['id_materia'];

            if (!isset($materiasPorCarrera[$idCarrera])) {
                $materiasPorCarrera[$idCarrera] = [];
            }

            $materiasPorCarrera[$idCarrera][] = [
                'id_materia' => $idMateria,
                'nombre_materia' => $relacion['nombre_materia'],
            ];

            $materiasAsignadas[$idMateria] = true;
        }

        $carreras = [];
        foreach ($carrerasRows as $carrera) {
            $idCarrera = (int)$carrera['id_carrera'];
            $carreras[] = [
                'id_carrera' => $idCarrera,
                'nombre_carrera' => $carrera['nombre_carrera'],
                'materias' => $materiasPorCarrera[$idCarrera] ?? [],
            ];
        }

        $materiasDisponibles = [];
        foreach ($materiasRows as $materia) {
            $idMateria = (int)$materia['id_materia'];
            if (!isset($materiasAsignadas[$idMateria])) {
                $materiasDisponibles[] = [
                    'id_materia' => $idMateria,
                    'nombre_materia' => $materia['nombre_materia'],
                ];
            }
        }

        return [
            'materias' => $materiasDisponibles,
            'carreras' => $carreras,
        ];
    }

    public static function asociarACarrera($pdo, $idCarrera, $idMateria)
    {
        $stmtExiste = $pdo->prepare(
            "SELECT id_carreraMateria
             FROM carrera_materia
             WHERE id_carrera = ? AND id_materia = ?
             LIMIT 1"
        );
        $stmtExiste->execute([$idCarrera, $idMateria]);
        $row = $stmtExiste->fetch();

        if ($row) {
            $stmtReactivar = $pdo->prepare(
                "UPDATE carrera_materia
                 SET cancelado = 0
                 WHERE id_carreraMateria = ?"
            );

            return $stmtReactivar->execute([$row['id_carreraMateria']]);
        }

        $stmtInsert = $pdo->prepare(
            "INSERT INTO carrera_materia (id_carrera, id_materia, cancelado)
             VALUES (?, ?, 0)"
        );

        return $stmtInsert->execute([$idCarrera, $idMateria]);
    }

    public static function desasociarDeCarrera($pdo, $idCarrera, $idMateria)
    {
        $stmt = $pdo->prepare(
            "UPDATE carrera_materia
             SET cancelado = 1
             WHERE id_carrera = ? AND id_materia = ?"
        );

        return $stmt->execute([$idCarrera, $idMateria]);
    }

    public static function existeActivaPorNombre($pdo, $nombre)
    {
        $stmt = $pdo->prepare(
            "SELECT id_materia
             FROM materia
             WHERE cancelado = 0
               AND LOWER(TRIM(nombre_materia)) = LOWER(TRIM(?))
             LIMIT 1"
        );
        $stmt->execute([$nombre]);
        return $stmt->fetch() ?: null;
    }

    public static function existePorNombreIncluyendoCanceladas($pdo, $nombre)
    {
        $stmt = $pdo->prepare(
            "SELECT id_materia, cancelado
             FROM materia
             WHERE LOWER(TRIM(nombre_materia)) = LOWER(TRIM(?))
             LIMIT 1"
        );
        $stmt->execute([$nombre]);
        return $stmt->fetch() ?: null;
    }

    public static function reactivarPorNombre($pdo, $idMateria, $nombre)
    {
        $stmt = $pdo->prepare(
            "UPDATE materia
             SET nombre_materia = ?, cancelado = 0, habilitado = 1
             WHERE id_materia = ?"
        );
        return $stmt->execute([$nombre, $idMateria]);
    }

    public static function crear($pdo, $nombre)
    {
        $stmt = $pdo->prepare(
            "INSERT INTO materia (nombre_materia, habilitado, cancelado)
             VALUES (?, 1, 0)"
        );
        return $stmt->execute([$nombre]);
    }

    public static function actualizarNombre($pdo, $idMateria, $nombre)
    {
        $stmt = $pdo->prepare(
            "UPDATE materia
             SET nombre_materia = ?
             WHERE id_materia = ? AND cancelado = 0"
        );
        $stmt->execute([$nombre, $idMateria]);
        return $stmt->rowCount();
    }

    public static function softDeleteConRelaciones($pdo, $idMateria)
    {
        $pdo->beginTransaction();
        try {
            $stmtRelacion = $pdo->prepare(
                "UPDATE carrera_materia
                 SET cancelado = 1
                 WHERE id_materia = ?"
            );
            $stmtRelacion->execute([$idMateria]);

            $stmtMateria = $pdo->prepare(
                "UPDATE materia
                 SET cancelado = 1, habilitado = 0
                 WHERE id_materia = ? AND cancelado = 0"
            );
            $stmtMateria->execute([$idMateria]);

            if ($stmtMateria->rowCount() === 0) {
                $pdo->rollBack();
                return 0;
            }

            $pdo->commit();
            return 1;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
