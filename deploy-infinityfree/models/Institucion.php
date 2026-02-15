<?php
/**
 * Modelo de Institución - Programación Orientada a Objetos
 */

class Institucion {
    // Propiedades privadas
    private $id;
    private $nombre;
    private $direccion;
    private $telefono;
    private $email;
    private $sitio_web;
    private $observaciones;
    private $latitud;
    private $longitud;
    private $logo;
    private $likes;
    private $carreras;

    /**
     * Constructor
     */
    public function __construct(
        $nombre, 
        $direccion = null, 
        $telefono = null, 
        $email = null, 
        $sitio_web = null, 
        $observaciones = null,
        $latitud = null,
        $longitud = null,
        $logo = null,
        $id = null,
        $likes = 0,
        $carreras = []
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->direccion = $direccion;
        $this->telefono = $telefono;
        $this->email = $email;
        $this->sitio_web = $sitio_web;
        $this->observaciones = $observaciones;
        $this->latitud = $latitud;
        $this->longitud = $longitud;
        $this->logo = $logo;
        $this->likes = $likes;
        $this->carreras = $carreras;
    }

    // ============= GETTERS =============
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getDireccion() { return $this->direccion; }
    public function getTelefono() { return $this->telefono; }
    public function getEmail() { return $this->email; }
    public function getSitioWeb() { return $this->sitio_web; }
    public function getObservaciones() { return $this->observaciones; }
    public function getLatitud() { return $this->latitud; }
    public function getLongitud() { return $this->longitud; }
    public function getLogo() { return $this->logo; }
    public function getLikes() { return $this->likes; }
    public function getCarreras() { return $this->carreras; }

    // ============= SETTERS =============
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setDireccion($direccion) { $this->direccion = $direccion; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }
    public function setEmail($email) { $this->email = $email; }
    public function setSitioWeb($sitio_web) { $this->sitio_web = $sitio_web; }
    public function setObservaciones($observaciones) { $this->observaciones = $observaciones; }
    public function setLatitud($latitud) { $this->latitud = $latitud; }
    public function setLongitud($longitud) { $this->longitud = $longitud; }
    public function setLogo($logo) { $this->logo = $logo; }
    public function setCarreras($carreras) { $this->carreras = $carreras; }

    // ============= MÉTODOS DE INSTANCIA =============

    /**
     * Guardar institución en la base de datos
     */
    public function guardar($pdo) {
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO institucion (nombre_ifts, direccion_ifts, telefono_ifts, email_ifts, sitio_web_ifts, observaciones_ifts, latitud_ifts, longitud_ifts, logo_ifts, likes_ifts) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $this->nombre,
                $this->direccion,
                $this->telefono,
                $this->email,
                $this->sitio_web,
                $this->observaciones,
                $this->latitud,
                $this->longitud,
                $this->logo,
                $this->likes
            ]);

            $this->id = $pdo->lastInsertId();

            // Guardar carreras asociadas
            if (!empty($this->carreras)) {
                $this->guardarCarreras($pdo);
            }

            $pdo->commit();
            return true;

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Actualizar institución existente
     */
    public function actualizar($pdo) {
        $stmt = $pdo->prepare(
            "UPDATE institucion 
             SET nombre_ifts = ?, direccion_ifts = ?, telefono_ifts = ?, email_ifts = ?, sitio_web_ifts = ?, 
                 observaciones_ifts = ?, latitud_ifts = ?, longitud_ifts = ?, logo_ifts = ? 
             WHERE id_institucion = ?"
        );
        
        return $stmt->execute([
            $this->nombre,
            $this->direccion,
            $this->telefono,
            $this->email,
            $this->sitio_web,
            $this->observaciones,
            $this->latitud,
            $this->longitud,
            $this->logo,
            $this->id
        ]);
    }

    /**
     * Incrementar contador de likes
     */
    public function incrementarLikes($pdo) {
        $stmt = $pdo->prepare("UPDATE institucion SET likes_ifts = likes_ifts + 1 WHERE id_institucion = ?");
        $stmt->execute([$this->id]);
        
        // Actualizar el valor en la instancia
        $this->likes++;
        return $this->likes;
    }

    /**
     * Eliminar institución
     */
    public function eliminar($pdo) {
        $stmt = $pdo->prepare("DELETE FROM institucion WHERE id_institucion = ?");
        return $stmt->execute([$this->id]);
    }

    /**
     * Convertir objeto a array (para JSON)
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'sitio_web' => $this->sitio_web,
            'observaciones' => $this->observaciones,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'logo' => $this->logo,
            'likes' => $this->likes,
            'carreras' => $this->carreras
        ];
    }

    // ============= MÉTODOS PRIVADOS =============

    /**
     * Guardar carreras asociadas a la institución
     */
    private function guardarCarreras($pdo) {
        foreach ($this->carreras as $nombre_carrera) {
            $nombre_carrera = trim($nombre_carrera);
            if (empty($nombre_carrera)) continue;

            // Buscar o crear carrera
            $carrera_id = $this->obtenerOCrearCarrera($pdo, $nombre_carrera);

            // Crear relación
            $stmt = $pdo->prepare("INSERT INTO institucion_carrera (id_institucion, id_carrera) VALUES (?, ?)");
            $stmt->execute([$this->id, $carrera_id]);
        }
    }

    /**
     * Obtener ID de carrera o crearla si no existe
     */
    private function obtenerOCrearCarrera($pdo, $nombre_carrera) {
        $stmt = $pdo->prepare("SELECT id_carrera FROM carrera WHERE nombre_carrera = ?");
        $stmt->execute([$nombre_carrera]);
        $carrera = $stmt->fetch();

        if ($carrera) {
            return $carrera['id_carrera'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO carrera (nombre_carrera) VALUES (?)");
            $stmt->execute([$nombre_carrera]);
            return $pdo->lastInsertId();
        }
    }

    // ============= MÉTODOS ESTÁTICOS (CRUD) =============

    /**
     * Obtener todas las instituciones con sus carreras
     */
    public static function obtenerTodas($pdo) {
        $sql = "SELECT i.*, GROUP_CONCAT(c.nombre_carrera SEPARATOR '|||') as lista_carreras 
                FROM institucion i 
                LEFT JOIN institucion_carrera ic ON i.id_institucion = ic.id_institucion 
                LEFT JOIN carrera c ON ic.id_carrera = c.id_carrera 
                GROUP BY i.id_institucion";
        
        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll();

        $instituciones = [];
        foreach ($resultados as $row) {
            $carreras = [];
            if ($row['lista_carreras']) {
                $carreras = explode('|||', $row['lista_carreras']);
            }

            $instituciones[] = new Institucion(
                $row['nombre_ifts'],
                $row['direccion_ifts'],
                $row['telefono_ifts'],
                $row['email_ifts'],
                $row['sitio_web_ifts'],
                $row['observaciones_ifts'],
                $row['latitud_ifts'],
                $row['longitud_ifts'],
                $row['logo_ifts'],
                $row['id_institucion'],
                $row['likes_ifts'] ?? 0,
                $carreras
            );
        }

        return $instituciones;
    }

    /**
     * Buscar institución por ID
     */
    public static function buscarPorId($pdo, $id) {
        $stmt = $pdo->prepare("SELECT * FROM institucion WHERE id_institucion = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        // Obtener carreras
        $stmt = $pdo->prepare(
            "SELECT c.nombre_carrera FROM carrera c 
             INNER JOIN institucion_carrera ic ON c.id_carrera = ic.id_carrera 
             WHERE ic.id_institucion = ?"
        );
        $stmt->execute([$id]);
        $carreras = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return new Institucion(
            $row['nombre_ifts'],
            $row['direccion_ifts'],
            $row['telefono_ifts'],
            $row['email_ifts'],
            $row['sitio_web_ifts'],
            $row['observaciones_ifts'],
            $row['latitud_ifts'],
            $row['longitud_ifts'],
            $row['logo_ifts'],
            $row['id_institucion'],
            $row['likes_ifts'] ?? 0,
            $carreras
        );
    }

    /**
     * Crear institución desde array de datos
     */
    public static function desdeArray($data) {
        return new Institucion(
            $data['nombre'] ?? $data['nombre_ifts'] ?? '',
            $data['direccion'] ?? $data['direccion_ifts'] ?? null,
            $data['telefono'] ?? $data['telefono_ifts'] ?? null,
            $data['email'] ?? $data['email_ifts'] ?? null,
            $data['sitio_web'] ?? $data['sitio_web_ifts'] ?? null,
            $data['observaciones'] ?? $data['observaciones_ifts'] ?? null,
            $data['latitud'] ?? $data['latitud_ifts'] ?? null,
            $data['longitud'] ?? $data['longitud_ifts'] ?? null,
            $data['logo'] ?? $data['logo_ifts'] ?? null,
            $data['id'] ?? $data['id_institucion'] ?? null,
            $data['likes'] ?? $data['likes_ifts'] ?? 0,
            $data['carreras'] ?? []
        );
    }
}
