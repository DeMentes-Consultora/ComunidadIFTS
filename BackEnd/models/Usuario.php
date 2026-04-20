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
    private $idCarrera;
    private $anioCursada;
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
    private $fotoPerfilUrl;
    private $fotoPerfilPublicId;
    private $nombreInstitucion;
    private $logoInstitucion;
    private $nombreCarrera;

    public function __construct(
        $email,
        $clave,
        $idPersona,
        $idRol,
        $idInstitucion,
        $idCarrera = null,
        $anioCursada = null,
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
        $this->idCarrera = $idCarrera !== null ? (int)$idCarrera : null;
        $this->anioCursada = $anioCursada !== null ? (int)$anioCursada : null;
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
    public function getIdCarrera() { return $this->idCarrera; }
    public function getAnioCursada() { return $this->anioCursada; }
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
    public function getFotoPerfilUrl() { return $this->fotoPerfilUrl; }
    public function getFotoPerfilPublicId() { return $this->fotoPerfilPublicId; }
    public function getNombreInstitucion() { return $this->nombreInstitucion; }
    public function getLogoInstitucion() { return $this->logoInstitucion; }
    public function getNombreCarrera() { return $this->nombreCarrera; }

    public function setEmail($email) { $this->email = $email; }
    public function setClave($clave) { $this->clave = password_hash($clave, PASSWORD_DEFAULT); }
    public function setIdRol($idRol) { $this->idRol = (int)$idRol; }
    public function setIdPersona($idPersona) { $this->idPersona = (int)$idPersona; }
    public function setIdInstitucion($idInstitucion) { $this->idInstitucion = (int)$idInstitucion; }
    public function setIdCarrera($idCarrera) { $this->idCarrera = $idCarrera !== null ? (int)$idCarrera : null; }
    public function setAnioCursada($anioCursada) { $this->anioCursada = $anioCursada !== null ? (int)$anioCursada : null; }
    public function setHabilitado($habilitado) { $this->habilitado = (int)$habilitado; }
    public function setCancelado($cancelado) { $this->cancelado = (int)$cancelado; }

    public function verificarClave($clavePlano) {
        return password_verify($clavePlano, $this->clave);
    }

    public function guardar($pdo) {
        $sql = "INSERT INTO usuario (email, clave, id_rol, id_persona, id_institucion, id_carrera, anio_cursada, habilitado, cancelado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            $this->email,
            $this->clave,
            $this->idRol,
            $this->idPersona,
            $this->idInstitucion,
            $this->idCarrera,
            $this->anioCursada,
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
            'id_carrera' => $this->idCarrera,
            'anio_cursada' => $this->anioCursada,
            'nombre_institucion' => $this->nombreInstitucion,
            'nombre_carrera' => $this->nombreCarrera,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'dni' => $this->dni,
            'telefono' => $this->telefono,
            'edad' => $this->edad,
            'fecha_nacimiento' => $this->fechaNacimiento,
            'foto_perfil_url' => $this->fotoPerfilUrl,
            'foto_perfil_public_id' => $this->fotoPerfilPublicId,
            'logo_ifts' => $this->logoInstitucion,
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
                    p.foto_perfil_url,
                    p.foto_perfil_public_id,
                    i.nombre_ifts,
                    i.logo_ifts,
                    c.nombre_carrera
                FROM usuario u
                INNER JOIN rol r ON u.id_rol = r.id_rol
                INNER JOIN persona p ON u.id_persona = p.id_persona
                INNER JOIN institucion i ON u.id_institucion = i.id_institucion
                LEFT JOIN carrera c ON u.id_carrera = c.id_carrera
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
            $row['id_carrera'] ?? null,
            $row['anio_cursada'] ?? null,
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
        $usuario->fotoPerfilUrl = $row['foto_perfil_url'] ?? null;
        $usuario->fotoPerfilPublicId = $row['foto_perfil_public_id'] ?? null;
        $usuario->nombreInstitucion = $row['nombre_ifts'] ?? null;
        $usuario->logoInstitucion = $row['logo_ifts'] ?? null;
        $usuario->nombreCarrera = $row['nombre_carrera'] ?? null;

        return $usuario;
    }

    public static function emailExiste($pdo, $email) {
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return (bool)$stmt->fetch();
    }

    public static function obtenerEstadoPorEmail($pdo, $email) {
        $sql = "SELECT id_usuario, habilitado, cancelado
                FROM usuario
                WHERE email = ?
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function autenticar($pdo, $email, $clavePlano) {
        $usuario = self::buscarPorEmail($pdo, $email);

        if (!$usuario) {
            return null;
        }

        return $usuario->verificarClave($clavePlano) ? $usuario : null;
    }

    public static function obtenerPendientesAprobacion($pdo) {
        $sql = "SELECT
                    u.id_usuario,
                    u.email,
                    u.habilitado,
                    u.idCreate as fecha_registro,
                    p.nombre,
                    p.apellido,
                    p.dni,
                    p.telefono,
                    i.nombre_ifts as nombre_institucion,
                    i.id_institucion,
                    r.nombre_rol,
                    r.id_rol
                FROM usuario u
                INNER JOIN persona p ON u.id_persona = p.id_persona
                INNER JOIN institucion i ON u.id_institucion = i.id_institucion
                INNER JOIN rol r ON u.id_rol = r.id_rol
                WHERE u.habilitado = 0
                  AND u.cancelado = 0
                ORDER BY u.idCreate DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerPendientePorId($pdo, $idUsuario) {
        $sql = "SELECT
                    u.id_usuario,
                    u.email,
                    u.habilitado,
                    p.nombre,
                    p.apellido
                FROM usuario u
                INNER JOIN persona p ON u.id_persona = p.id_persona
                WHERE u.id_usuario = ?
                  AND u.cancelado = 0
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idUsuario]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function buscarPorId($pdo, $idUsuario) {
        $sql = "SELECT
                    u.*,
                    r.nombre_rol,
                    p.nombre,
                    p.apellido,
                    p.dni,
                    p.telefono,
                    p.edad,
                    p.fecha_nacimiento,
                    p.foto_perfil_url,
                    p.foto_perfil_public_id,
                    i.nombre_ifts,
                    i.logo_ifts,
                    c.nombre_carrera
                FROM usuario u
                INNER JOIN rol r ON u.id_rol = r.id_rol
                INNER JOIN persona p ON u.id_persona = p.id_persona
                INNER JOIN institucion i ON u.id_institucion = i.id_institucion
                LEFT JOIN carrera c ON u.id_carrera = c.id_carrera
                WHERE u.id_usuario = ?
                  AND u.habilitado = 1
                  AND u.cancelado = 0
                  AND r.habilitado = 1
                  AND r.cancelado = 0
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idUsuario]);
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
            $row['id_carrera'] ?? null,
            $row['anio_cursada'] ?? null,
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
        $usuario->fotoPerfilUrl = $row['foto_perfil_url'] ?? null;
        $usuario->fotoPerfilPublicId = $row['foto_perfil_public_id'] ?? null;
        $usuario->nombreInstitucion = $row['nombre_ifts'] ?? null;
        $usuario->logoInstitucion = $row['logo_ifts'] ?? null;
        $usuario->nombreCarrera = $row['nombre_carrera'] ?? null;

        return $usuario;
    }

    public static function aprobarPorId($pdo, $idUsuario) {
        $stmt = $pdo->prepare("UPDATE usuario SET habilitado = 1 WHERE id_usuario = ?");
        return $stmt->execute([$idUsuario]);
    }

    public static function rechazarPorId($pdo, $idUsuario) {
        $stmt = $pdo->prepare("UPDATE usuario SET cancelado = 1 WHERE id_usuario = ?");
        return $stmt->execute([$idUsuario]);
    }

    public static function actualizarDatosAcademicos($pdo, $idUsuario, $idCarrera, $anioCursada) {
        $stmt = $pdo->prepare(
            "UPDATE usuario
             SET id_carrera = ?, anio_cursada = ?
             WHERE id_usuario = ? AND cancelado = 0"
        );

        return $stmt->execute([$idCarrera, $anioCursada, $idUsuario]);
    }

    public static function obtenerRegistradosAprobados($pdo) {
        $sql = "SELECT
                    u.id_usuario,
                    u.email,
                    p.apellido,
                    p.nombre,
                    p.dni,
                    u.id_rol,
                    r.nombre_rol
                FROM usuario u
                INNER JOIN persona p ON u.id_persona = p.id_persona
                INNER JOIN rol r ON u.id_rol = r.id_rol
                WHERE u.habilitado = 1
                  AND u.cancelado = 0
                  AND r.habilitado = 1
                  AND r.cancelado = 0
                ORDER BY p.apellido ASC, p.nombre ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerRegistradoPorId($pdo, $idUsuario) {
        $sql = "SELECT
                    u.id_usuario,
                    u.id_rol,
                    p.nombre,
                    p.apellido
                FROM usuario u
                INNER JOIN persona p ON u.id_persona = p.id_persona
                WHERE u.id_usuario = ?
                  AND u.habilitado = 1
                  AND u.cancelado = 0
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idUsuario]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function cambiarRolPorId($pdo, $idUsuario, $idRolNuevo) {
        $sql = "UPDATE usuario
                SET id_rol = ?, idUpdate = CURRENT_TIMESTAMP
                WHERE id_usuario = ?
                  AND habilitado = 1
                  AND cancelado = 0";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([(int)$idRolNuevo, (int)$idUsuario]);
    }
}
