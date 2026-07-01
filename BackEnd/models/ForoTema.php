<?php
/**
 * Modelo ForoTema
 *
 * Maneja toda la persistencia de la tabla `foro_tema`.
 * Estados:
 *   habilitado=1, cancelado=0, cerrado=0 -> ACTIVO
 *   habilitado=1, cancelado=0, cerrado=1 -> CERRADO
 *   habilitado=0, cancelado=1 -> ELIMINADO (soft delete)
 */
class ForoTema {

    // ------------------------------------------------------------------
    // LECTURA
    // ------------------------------------------------------------------

    /**
     * Listado paginado de temas activos, con filtros opcionales.
     */
    public static function obtenerListado(PDO $pdo, int $page = 1, int $limit = 15, ?int $categoriaId = null, ?string $busqueda = null): array {
        $offset = ($page - 1) * $limit;
        $where = ["t.habilitado = 1", "t.cancelado = 0"];
        $params = [];

        if ($categoriaId !== null) {
            $where[] = "t.id_categoria = ?";
            $params[] = $categoriaId;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $where[] = "(MATCH(t.titulo, t.contenido) AGAINST(? IN BOOLEAN MODE) OR t.titulo LIKE ?)";
            $params[] = $busqueda;
            $params[] = "%$busqueda%";
        }

        $whereSQL = implode(' AND ', $where);

        // Total
        $sqlCount = "SELECT COUNT(*) FROM foro_tema t WHERE $whereSQL";
        $stmtCount = $pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        // Datos
        $sql = "SELECT
                    t.id_tema,
                    t.id_categoria,
                    t.id_usuario,
                    t.titulo,
                    t.vistas,
                    t.cantidad_respuestas,
                    t.cerrado,
                    t.motivo_cierre,
                    t.fijo,
                    t.idCreate,
                    c.nombre AS nombre_categoria,
                    c.icono AS icono_categoria,
                    c.color AS color_categoria,
                    p.nombre AS autor_nombre,
                    p.apellido AS autor_apellido,
                    p.foto_perfil_url AS autor_foto,
                    u.id_rol AS autor_rol
                FROM foro_tema t
                INNER JOIN foro_categoria c ON t.id_categoria = c.id_categoria
                INNER JOIN usuario u ON t.id_usuario = u.id_usuario
                INNER JOIN persona p ON u.id_persona = p.id_persona
                WHERE $whereSQL
                ORDER BY t.fijo DESC, t.idCreate DESC
                LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'temas' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => (int)ceil($total / $limit)
        ];
    }

    /**
     * Un tema por ID (para vista detallada).
     */
    public static function obtenerPorId(PDO $pdo, int $id): ?array {
        $sql = "SELECT
                    t.*,
                    c.nombre AS nombre_categoria,
                    c.icono AS icono_categoria,
                    c.color AS color_categoria,
                    p.nombre AS autor_nombre,
                    p.apellido AS autor_apellido,
                    p.foto_perfil_url AS autor_foto,
                    u.id_rol AS autor_rol,
                    u.email AS autor_email
                FROM foro_tema t
                INNER JOIN foro_categoria c ON t.id_categoria = c.id_categoria
                INNER JOIN usuario u ON t.id_usuario = u.id_usuario
                INNER JOIN persona p ON u.id_persona = p.id_persona
                WHERE t.id_tema = ?
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ------------------------------------------------------------------
    // ESCRITURA
    // ------------------------------------------------------------------

    /**
     * Crea un tema. Retorna el ID insertado o null en caso de error.
     */
    public static function crear(PDO $pdo, int $idCategoria, int $idUsuario, string $titulo, string $contenido): ?int {
        $sql = "INSERT INTO foro_tema (id_categoria, id_usuario, titulo, contenido)
                VALUES (?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([$idCategoria, $idUsuario, $titulo, $contenido]);
        return $ok ? (int)$pdo->lastInsertId() : null;
    }

    /**
     * Actualiza un tema existente.
     */
    public static function actualizar(PDO $pdo, int $id, array $datos): bool {
        $campos = [];
        $valores = [];

        if (array_key_exists('titulo', $datos)) {
            $campos[] = 'titulo = ?';
            $valores[] = $datos['titulo'];
        }
        if (array_key_exists('contenido', $datos)) {
            $campos[] = 'contenido = ?';
            $valores[] = $datos['contenido'];
        }
        if (array_key_exists('id_categoria', $datos)) {
            $campos[] = 'id_categoria = ?';
            $valores[] = $datos['id_categoria'];
        }
        if (array_key_exists('fijo', $datos)) {
            $campos[] = 'fijo = ?';
            $valores[] = $datos['fijo'];
        }
        if (array_key_exists('cerrado', $datos)) {
            $campos[] = 'cerrado = ?';
            $valores[] = $datos['cerrado'];
        }
        if (array_key_exists('motivo_cierre', $datos)) {
            $campos[] = 'motivo_cierre = ?';
            $valores[] = $datos['motivo_cierre'];
        }

        if (empty($campos)) {
            return false;
        }

        $valores[] = $id;
        $sql = "UPDATE foro_tema SET " . implode(', ', $campos) . " WHERE id_tema = ?";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($valores);
    }

    /**
     * Cierra un tema con motivo opcional.
     */
    public static function cerrar(PDO $pdo, int $id, ?string $motivo = null): bool {
        $stmt = $pdo->prepare(
            "UPDATE foro_tema
             SET cerrado = 1, motivo_cierre = ?, idUpdate = CURRENT_TIMESTAMP
             WHERE id_tema = ?"
        );
        return $stmt->execute([$motivo, $id]);
    }

    /**
     * Abre un tema que estaba cerrado.
     */
    public static function abrir(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare(
            "UPDATE foro_tema
             SET cerrado = 0, motivo_cierre = NULL, idUpdate = CURRENT_TIMESTAMP
             WHERE id_tema = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Fija/desfija un tema.
     */
    public static function fijar(PDO $pdo, int $id, bool $fijar): bool {
        $stmt = $pdo->prepare(
            "UPDATE foro_tema
             SET fijo = ?, idUpdate = CURRENT_TIMESTAMP
             WHERE id_tema = ?"
        );
        return $stmt->execute([$fijar ? 1 : 0, $id]);
    }

    /**
     * Elimina (soft delete) un tema.
     */
    public static function eliminar(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare(
            "UPDATE foro_tema
             SET cancelado = 1, idUpdate = CURRENT_TIMESTAMP
             WHERE id_tema = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Incrementa el contador de vistas.
     */
    public static function incrementarVistas(PDO $pdo, int $id): void {
        $stmt = $pdo->prepare(
            "UPDATE foro_tema
             SET vistas = vistas + 1
             WHERE id_tema = ?"
        );
        $stmt->execute([$id]);
    }

    /**
     * Registra una vista del tema.
     */
    public static function registrarVista(PDO $pdo, int $idTema, ?int $idUsuario, ?string $ipAddress): void {
        $sql = "INSERT INTO foro_vista (id_tema, id_usuario, ip_address) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idTema, $idUsuario, $ipAddress]);
    }

    /**
     * Incrementa el contador de respuestas.
     */
    public static function incrementarRespuestas(PDO $pdo, int $id): void {
        $stmt = $pdo->prepare(
            "UPDATE foro_tema
             SET cantidad_respuestas = cantidad_respuestas + 1, idUpdate = CURRENT_TIMESTAMP
             WHERE id_tema = ?"
        );
        $stmt->execute([$id]);
    }

    /**
     * Decrementa el contador de respuestas.
     */
    public static function decrementarRespuestas(PDO $pdo, int $id): void {
        $stmt = $pdo->prepare(
            "UPDATE foro_tema
             SET cantidad_respuestas = GREATEST(cantidad_respuestas - 1, 0), idUpdate = CURRENT_TIMESTAMP
             WHERE id_tema = ?"
        );
        $stmt->execute([$id]);
    }

    // ------------------------------------------------------------------
    // CIERRE AUTOMÁTICO
    // ------------------------------------------------------------------

    /**
     * Obtiene temas abiertos sin respuesta en los últimos N días.
     */
    public static function obtenerTemasInactivos(PDO $pdo, int $dias = 7): array {
        $sql = "SELECT
                    t.id_tema,
                    t.titulo,
                    t.id_usuario,
                    t.idCreate,
                    u.email AS autor_email,
                    p.nombre AS autor_nombre
                FROM foro_tema t
                INNER JOIN usuario u ON t.id_usuario = u.id_usuario
                INNER JOIN persona p ON u.id_persona = p.id_persona
                WHERE t.habilitado = 1
                  AND t.cancelado = 0
                  AND t.cerrado = 0
                  AND t.cantidad_respuestas = 0
                  AND t.idCreate <= DATE_SUB(NOW(), INTERVAL ? DAY)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
