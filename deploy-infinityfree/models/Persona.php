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
    private $fotoPerfilUrl;
    private $fotoPerfilPublicId;
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
        $fotoPerfilUrl = null,
        $fotoPerfilPublicId = null,
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
        $this->fotoPerfilUrl = $fotoPerfilUrl;
        $this->fotoPerfilPublicId = $fotoPerfilPublicId;
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
    public function getFotoPerfilUrl() { return $this->fotoPerfilUrl; }
    public function getFotoPerfilPublicId() { return $this->fotoPerfilPublicId; }
    public function getHabilitado() { return $this->habilitado; }
    public function getCancelado() { return $this->cancelado; }

    public function setApellido($apellido) { $this->apellido = $apellido; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setEdad($edad) { $this->edad = (int)$edad; }
    public function setDni($dni) { $this->dni = $dni; }
    public function setFechaNacimiento($fechaNacimiento) { $this->fechaNacimiento = $fechaNacimiento; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }
    public function setFotoPerfilUrl($fotoPerfilUrl) { $this->fotoPerfilUrl = $fotoPerfilUrl; }
    public function setFotoPerfilPublicId($fotoPerfilPublicId) { $this->fotoPerfilPublicId = $fotoPerfilPublicId; }
    public function setHabilitado($habilitado) { $this->habilitado = (int)$habilitado; }
    public function setCancelado($cancelado) { $this->cancelado = (int)$cancelado; }

    public function guardar($pdo) {
        $sql = "INSERT INTO persona (apellido, nombre, edad, dni, fecha_nacimiento, telefono, foto_perfil_url, foto_perfil_public_id, habilitado, cancelado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            $this->apellido,
            $this->nombre,
            $this->edad,
            $this->dni,
            $this->fechaNacimiento,
            $this->telefono,
            $this->fotoPerfilUrl,
            $this->fotoPerfilPublicId,
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

    public static function obtenerFotoPerfilPorId($pdo, $idPersona) {
        $stmt = $pdo->prepare(
            "SELECT foto_perfil_url, foto_perfil_public_id
             FROM persona
             WHERE id_persona = ?
             LIMIT 1"
        );
        $stmt->execute([$idPersona]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function actualizarFotoPerfil($pdo, $idPersona, $fotoPerfilUrl, $fotoPerfilPublicId = null) {
        $stmt = $pdo->prepare(
            "UPDATE persona
             SET foto_perfil_url = ?, foto_perfil_public_id = ?
             WHERE id_persona = ?"
        );
        return $stmt->execute([$fotoPerfilUrl, $fotoPerfilPublicId, $idPersona]);
    }
}
