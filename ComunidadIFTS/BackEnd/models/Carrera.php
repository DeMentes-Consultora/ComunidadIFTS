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
        $stmt = $pdo->prepare("INSERT INTO carreras (nombre, descripcion) VALUES (?, ?)");
        
        if ($stmt->execute([$this->nombre, $this->descripcion])) {
            $this->id = $pdo->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Actualizar carrera existente
     */
    public function actualizar($pdo) {
        $stmt = $pdo->prepare("UPDATE carreras SET nombre = ?, descripcion = ? WHERE id = ?");
        return $stmt->execute([$this->nombre, $this->descripcion, $this->id]);
    }

    /**
     * Eliminar carrera
     */
    public function eliminar($pdo) {
        $stmt = $pdo->prepare("DELETE FROM carreras WHERE id = ?");
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
        $sql = "SELECT * FROM carreras ORDER BY nombre ASC";
        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll();

        $carreras = [];
        foreach ($resultados as $row) {
            $carreras[] = new Carrera(
                $row['nombre'],
                $row['descripcion'] ?? null,
                $row['id']
            );
        }

        return $carreras;
    }

    /**
     * Buscar carrera por ID
     */
    public static function buscarPorId($pdo, $id) {
        $stmt = $pdo->prepare("SELECT * FROM carreras WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new Carrera(
            $row['nombre'],
            $row['descripcion'] ?? null,
            $row['id']
        );
    }

    /**
     * Buscar carrera por nombre
     */
    public static function buscarPorNombre($pdo, $nombre) {
        $stmt = $pdo->prepare("SELECT * FROM carreras WHERE nombre = ?");
        $stmt->execute([$nombre]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new Carrera(
            $row['nombre'],
            $row['descripcion'] ?? null,
            $row['id']
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
}
