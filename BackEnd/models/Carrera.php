<?php
/**
 * Modelo de Carrera - Programación Orientada a Objetos
 */

class Carrera {
    // Propiedades privadas
    private $id;
    private $nombre;
    private $descripcion;

    /**
     * Constructor
     */
    public function __construct($nombre, $descripcion = null, $id = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }

    // ============= GETTERS =============
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getDescripcion() { return $this->descripcion; }

    // ============= SETTERS =============
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }

    // ============= MÉTODOS DE INSTANCIA =============

    /**
     * Guardar carrera en la base de datos
     */
    public function guardar($pdo) {
        $stmt = $pdo->prepare("INSERT INTO carrera (nombre_carrera) VALUES (?)");
        
        if ($stmt->execute([$this->nombre])) {
            $this->id = $pdo->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Actualizar carrera existente
     */
    public function actualizar($pdo) {
        $stmt = $pdo->prepare("UPDATE carrera SET nombre_carrera = ? WHERE id_carrera = ?");
        return $stmt->execute([$this->nombre, $this->id]);
    }

    /**
     * Eliminar carrera
     */
    public function eliminar($pdo) {
        $stmt = $pdo->prepare("DELETE FROM carrera WHERE id_carrera = ?");
        return $stmt->execute([$this->id]);
    }

    /**
     * Convertir objeto a array (para JSON)
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion
        ];
    }

    // ============= MÉTODOS ESTÁTICOS (CRUD) =============

    /**
     * Obtener todas las carreras
     */
    public static function obtenerTodas($pdo) {
        $sql = "SELECT * FROM carrera ORDER BY nombre_carrera ASC";
        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll();

        $carreras = [];
        foreach ($resultados as $row) {
            $carreras[] = new Carrera(
                $row['nombre_carrera'],
                $row['descripcion'] ?? null,
                $row['id_carrera']
            );
        }

        return $carreras;
    }

    /**
     * Buscar carrera por ID
     */
    public static function buscarPorId($pdo, $id) {
        $stmt = $pdo->prepare("SELECT * FROM carrera WHERE id_carrera = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new Carrera(
            $row['nombre_carrera'],
            $row['descripcion'] ?? null,
            $row['id_carrera']
        );
    }

    /**
     * Buscar carrera por nombre
     */
    public static function buscarPorNombre($pdo, $nombre) {
        $stmt = $pdo->prepare("SELECT * FROM carrera WHERE nombre_carrera = ?");
        $stmt->execute([$nombre]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new Carrera(
            $row['nombre_carrera'],
            $row['descripcion'] ?? null,
            $row['id_carrera']
        );
    }

    /**
     * Crear carrera desde array de datos
     */
    public static function desdeArray($data) {
        return new Carrera(
            $data['nombre'] ?? '',
            $data['descripcion'] ?? null,
            $data['id'] ?? null
        );
    }

    public static function existeActivaPorNombre($pdo, $nombre) {
        $stmt = $pdo->prepare(
            "SELECT id_carrera
             FROM carrera
             WHERE cancelado = 0
               AND LOWER(TRIM(nombre_carrera)) = LOWER(TRIM(?))
             LIMIT 1"
        );
        $stmt->execute([$nombre]);
        return $stmt->fetch() ?: null;
    }

    public static function existePorNombreIncluyendoCanceladas($pdo, $nombre) {
        $stmt = $pdo->prepare(
            "SELECT id_carrera, cancelado
             FROM carrera
             WHERE LOWER(TRIM(nombre_carrera)) = LOWER(TRIM(?))
             LIMIT 1"
        );
        $stmt->execute([$nombre]);
        return $stmt->fetch() ?: null;
    }

    public static function reactivarPorNombre($pdo, $idCarrera, $nombre) {
        $stmt = $pdo->prepare(
            "UPDATE carrera
             SET nombre_carrera = ?, cancelado = 0, habilitado = 1
             WHERE id_carrera = ?"
        );
        return $stmt->execute([$nombre, $idCarrera]);
    }

    public static function actualizarNombre($pdo, $idCarrera, $nombre) {
        $stmt = $pdo->prepare(
            "UPDATE carrera
             SET nombre_carrera = ?
             WHERE id_carrera = ? AND cancelado = 0"
        );
        $stmt->execute([$nombre, $idCarrera]);
        return $stmt->rowCount();
    }

    public static function softDeleteConRelaciones($pdo, $idCarrera) {
        $pdo->beginTransaction();
        try {
            $stmtRelacion = $pdo->prepare(
                "UPDATE carrera_materia
                 SET cancelado = 1
                 WHERE id_carrera = ?"
            );
            $stmtRelacion->execute([$idCarrera]);

            $stmtInstitucion = $pdo->prepare(
                "UPDATE institucion_carrera
                 SET cancelado = 1
                 WHERE id_carrera = ?"
            );
            $stmtInstitucion->execute([$idCarrera]);

            $stmtCarrera = $pdo->prepare(
                "UPDATE carrera
                 SET cancelado = 1, habilitado = 0
                 WHERE id_carrera = ? AND cancelado = 0"
            );
            $stmtCarrera->execute([$idCarrera]);

            if ($stmtCarrera->rowCount() === 0) {
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
