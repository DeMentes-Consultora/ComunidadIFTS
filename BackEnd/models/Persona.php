<?php
/**
 * Modelo de Persona - Programación Orientada a Objetos
 */

class Persona {
    private $idPersona;
    private $apellido;
    private $nombre;
    private $edad;
    private $dni;
    private $fechaNacimiento;
    private $telefono;
    private $habilitado;
    private $cancelado;
    private $idCreate;
    private $idUpdate;

    public function __construct(
        $apellido,
        $nombre,
        $edad,
        $dni,
        $fechaNacimiento,
        $telefono,
        $idPersona = null,
        $habilitado = 1,
        $cancelado = 0,
        $idCreate = null,
        $idUpdate = null
    ) {
        $this->idPersona = $idPersona;
        $this->apellido = $apellido;
        $this->nombre = $nombre;
        $this->edad = (int)$edad;
        $this->dni = $dni;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->telefono = $telefono;
        $this->habilitado = (int)$habilitado;
        $this->cancelado = (int)$cancelado;
        $this->idCreate = $idCreate;
        $this->idUpdate = $idUpdate;
    }

    public function getIdPersona() { return $this->idPersona; }
    public function getApellido() { return $this->apellido; }
    public function getNombre() { return $this->nombre; }
    public function getEdad() { return $this->edad; }
    public function getDni() { return $this->dni; }
    public function getFechaNacimiento() { return $this->fechaNacimiento; }
    public function getTelefono() { return $this->telefono; }
    public function getHabilitado() { return $this->habilitado; }
    public function getCancelado() { return $this->cancelado; }

    public function setApellido($apellido) { $this->apellido = $apellido; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setEdad($edad) { $this->edad = (int)$edad; }
    public function setDni($dni) { $this->dni = $dni; }
    public function setFechaNacimiento($fechaNacimiento) { $this->fechaNacimiento = $fechaNacimiento; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }
    public function setHabilitado($habilitado) { $this->habilitado = (int)$habilitado; }
    public function setCancelado($cancelado) { $this->cancelado = (int)$cancelado; }

    public function guardar($pdo) {
        $sql = "INSERT INTO persona (apellido, nombre, edad, dni, fecha_nacimiento, telefono, habilitado, cancelado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            $this->apellido,
            $this->nombre,
            $this->edad,
            $this->dni,
            $this->fechaNacimiento,
            $this->telefono,
            $this->habilitado,
            $this->cancelado
        ]);

        if ($ok) {
            $this->idPersona = (int)$pdo->lastInsertId();
        }

        return $ok;
    }

    public static function dniExiste($pdo, $dni) {
        $stmt = $pdo->prepare("SELECT id_persona FROM persona WHERE dni = ? LIMIT 1");
        $stmt->execute([$dni]);
        return (bool)$stmt->fetch();
    }
}
