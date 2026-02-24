<?php
/**
 * Modelo de Usuario - Programación Orientada a Objetos
 */

class Usuario {
    private $idUsuario;
    private $email;
    private $clave;
    private $idRol;
    private $idPersona;
    private $idInstitucion;
    private $habilitado;
    private $cancelado;
    private $idCreate;
    private $idUpdate;

    private $nombreRol;
    private $nombre;
    private $apellido;
    private $dni;
    private $telefono;
    private $edad;
    private $fechaNacimiento;
    private $nombreInstitucion;

    public function __construct(
        $email,
        $clave,
        $idPersona,
        $idRol,
        $idInstitucion,
        $idUsuario = null,
        $habilitado = 1,
        $cancelado = 0,
        $idCreate = null,
        $idUpdate = null,
        $hashClave = true
    ) {
        $this->idUsuario = $idUsuario;
        $this->email = $email;
        $this->clave = $hashClave ? password_hash($clave, PASSWORD_DEFAULT) : $clave;
        $this->idRol = $idRol;
        $this->idPersona = $idPersona;
        $this->idInstitucion = $idInstitucion;
        $this->habilitado = (int)$habilitado;
        $this->cancelado = (int)$cancelado;
        $this->idCreate = $idCreate;
        $this->idUpdate = $idUpdate;
    }

    public function getIdUsuario() { return $this->idUsuario; }
    public function getEmail() { return $this->email; }
    public function getClave() { return $this->clave; }
    public function getIdRol() { return $this->idRol; }
    public function getIdPersona() { return $this->idPersona; }
    public function getIdInstitucion() { return $this->idInstitucion; }
    public function getHabilitado() { return $this->habilitado; }
    public function getCancelado() { return $this->cancelado; }
    public function getIdCreate() { return $this->idCreate; }
    public function getIdUpdate() { return $this->idUpdate; }

    public function getNombreRol() { return $this->nombreRol; }
    public function getNombre() { return $this->nombre; }
    public function getApellido() { return $this->apellido; }
    public function getDni() { return $this->dni; }
    public function getTelefono() { return $this->telefono; }
    public function getEdad() { return $this->edad; }
    public function getFechaNacimiento() { return $this->fechaNacimiento; }
    public function getNombreInstitucion() { return $this->nombreInstitucion; }

    public function setEmail($email) { $this->email = $email; }
    public function setClave($clave) { $this->clave = password_hash($clave, PASSWORD_DEFAULT); }
    public function setIdRol($idRol) { $this->idRol = (int)$idRol; }
    public function setIdPersona($idPersona) { $this->idPersona = (int)$idPersona; }
    public function setIdInstitucion($idInstitucion) { $this->idInstitucion = (int)$idInstitucion; }
    public function setHabilitado($habilitado) { $this->habilitado = (int)$habilitado; }
    public function setCancelado($cancelado) { $this->cancelado = (int)$cancelado; }

    public function verificarClave($clavePlano) {
        return password_verify($clavePlano, $this->clave);
    }

    public function guardar($pdo) {
        $sql = "INSERT INTO usuario (email, clave, id_rol, id_persona, id_institucion, habilitado, cancelado)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            $this->email,
            $this->clave,
            $this->idRol,
            $this->idPersona,
            $this->idInstitucion,
            $this->habilitado,
            $this->cancelado
        ]);

        if ($ok) {
            $this->idUsuario = (int)$pdo->lastInsertId();
        }

        return $ok;
    }

    public function toArray() {
        return [
            'id_usuario' => $this->idUsuario,
            'email' => $this->email,
            'id_rol' => $this->idRol,
            'nombre_rol' => $this->nombreRol,
            'id_persona' => $this->idPersona,
            'id_institucion' => $this->idInstitucion,
            'nombre_institucion' => $this->nombreInstitucion,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'dni' => $this->dni,
            'telefono' => $this->telefono,
            'edad' => $this->edad,
            'fecha_nacimiento' => $this->fechaNacimiento,
            'habilitado' => $this->habilitado,
            'cancelado' => $this->cancelado
        ];
    }

    public static function buscarPorEmail($pdo, $email) {
        $sql = "SELECT 
                    u.*, 
                    r.nombre_rol,
                    p.nombre,
                    p.apellido,
                    p.dni,
                    p.telefono,
                    p.edad,
                    p.fecha_nacimiento,
                    i.nombre_ifts
                FROM usuario u
                INNER JOIN rol r ON u.id_rol = r.id_rol
                INNER JOIN persona p ON u.id_persona = p.id_persona
                INNER JOIN institucion i ON u.id_institucion = i.id_institucion
                WHERE u.email = ?
                  AND u.habilitado = 1
                  AND u.cancelado = 0
                  AND r.habilitado = 1
                  AND r.cancelado = 0
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $usuario = new self(
            $row['email'],
            $row['clave'],
            $row['id_persona'],
            $row['id_rol'],
            $row['id_institucion'],
            $row['id_usuario'],
            $row['habilitado'],
            $row['cancelado'],
            $row['idCreate'] ?? null,
            $row['idUpdate'] ?? null,
            false
        );

        $usuario->nombreRol = $row['nombre_rol'] ?? null;
        $usuario->nombre = $row['nombre'] ?? null;
        $usuario->apellido = $row['apellido'] ?? null;
        $usuario->dni = $row['dni'] ?? null;
        $usuario->telefono = $row['telefono'] ?? null;
        $usuario->edad = $row['edad'] ?? null;
        $usuario->fechaNacimiento = $row['fecha_nacimiento'] ?? null;
        $usuario->nombreInstitucion = $row['nombre_ifts'] ?? null;

        return $usuario;
    }

    public static function emailExiste($pdo, $email) {
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return (bool)$stmt->fetch();
    }

    public static function autenticar($pdo, $email, $clavePlano) {
        $usuario = self::buscarPorEmail($pdo, $email);

        if (!$usuario) {
            return null;
        }

        return $usuario->verificarClave($clavePlano) ? $usuario : null;
    }
}
