<?php
/**
 * Modelo ForoCategoria
 *
 * Maneja toda la persistencia de la tabla `foro_categoria`.
 * Solo el administrador (rol=1) puede gestionar categorías.
 */
class ForoCategoria {

    // ------------------------------------------------------------------
    // LECTURA
    // ------------------------------------------------------------------

    /**
     * Todas las categorías habilitadas, ordenadas por `orden`.
     */
    public static function obtenerTodas(PDO $pdo): array {
        $sql = "SELECT
                    c.*,
                    (SELECT COUNT(*) FROM foro_tema t
                     WHERE t.id_categoria = c.id_categoria
                       AND t.habilitado = 1 AND t.cancelado = 0) AS cantidad_temas
                FROM foro_categoria c
                WHERE c.habilitado = 1 AND c.cancelado = 0
                ORDER BY c.orden ASC, c.nombre ASC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Todas las categorías (incluyendo deshabilitadas) para el admin.
     */
    public static function obtenerTodasAdmin(PDO $pdo): array {
        $sql = "SELECT
                    c.*,
                    (SELECT COUNT(*) FROM foro_tema t
                     WHERE t.id_categoria = c.id_categoria
                       AND t.habilitado = 1 AND t.cancelado = 0) AS cantidad_temas
                FROM foro_categoria c
                ORDER BY c.orden ASC, c.nombre ASC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Una categoría por ID.
     */
    public static function obtenerPorId(PDO $pdo, int $id): ?array {
        $sql = "SELECT * FROM foro_categoria WHERE id_categoria = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ------------------------------------------------------------------
    // ESCRITURA
    // ------------------------------------------------------------------

    /**
     * Crea una categoría. Retorna el ID insertado o null en caso de error.
     */
    public static function crear(PDO $pdo, string $nombre, ?string $descripcion, ?string $icono, ?string $color, int $orden = 0): ?int {
        $sql = "INSERT INTO foro_categoria (nombre, descripcion, icono, color, orden)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            $nombre,
            $descripcion,
            $icono,
            $color ?? '#3f51b5',
            $orden
        ]);
        return $ok ? (int)$pdo->lastInsertId() : null;
    }

    /**
     * Actualiza una categoría existente.
     */
    public static function actualizar(PDO $pdo, int $id, array $datos): bool {
        $campos = [];
        $valores = [];

        if (array_key_exists('nombre', $datos)) {
            $campos[] = 'nombre = ?';
            $valores[] = $datos['nombre'];
        }
        if (array_key_exists('descripcion', $datos)) {
            $campos[] = 'descripcion = ?';
            $valores[] = $datos['descripcion'];
        }
        if (array_key_exists('icono', $datos)) {
            $campos[] = 'icono = ?';
            $valores[] = $datos['icono'];
        }
        if (array_key_exists('color', $datos)) {
            $campos[] = 'color = ?';
            $valores[] = $datos['color'];
        }
        if (array_key_exists('orden', $datos)) {
            $campos[] = 'orden = ?';
            $valores[] = $datos['orden'];
        }
        if (array_key_exists('habilitado', $datos)) {
            $campos[] = 'habilitado = ?';
            $valores[] = $datos['habilitado'];
        }

        if (empty($campos)) {
            return false;
        }

        $valores[] = $id;
        $sql = "UPDATE foro_categoria SET " . implode(', ', $campos) . " WHERE id_categoria = ?";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($valores);
    }

    /**
     * Elimina (soft delete) una categoría.
     */
    public static function eliminar(PDO $pdo, int $id): bool {
        $stmt = $pdo->prepare(
            "UPDATE foro_categoria
             SET cancelado = 1, idUpdate = CURRENT_TIMESTAMP
             WHERE id_categoria = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Verifica si existe una categoría con el nombre dado (excluyendo un ID opcional).
     */
    public static function existeNombre(PDO $pdo, string $nombre, ?int $excluirId = null): bool {
        $sql = "SELECT COUNT(*) FROM foro_categoria WHERE nombre = ? AND cancelado = 0";
        $params = [$nombre];

        if ($excluirId !== null) {
            $sql .= " AND id_categoria != ?";
            $params[] = $excluirId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }
}
