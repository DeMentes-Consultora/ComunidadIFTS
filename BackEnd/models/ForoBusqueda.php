<?php
/**
 * Modelo ForoBusqueda
 *
 * Maneja la búsqueda fulltext de temas y respuestas en el foro.
 */
class ForoBusqueda {

    /**
     * Busca temas por término de búsqueda (fulltext + LIKE).
     */
    public static function buscarTemas(PDO $pdo, string $termino, ?int $categoriaId = null, int $page = 1, int $limit = 15): array {
        $offset = ($page - 1) * $limit;
        $where = ["t.habilitado = 1", "t.cancelado = 0"];
        $params = [];

        // Búsqueda fulltext + LIKE para cobertura amplia
        $where[] = "(MATCH(t.titulo, t.contenido) AGAINST(? IN BOOLEAN MODE) OR t.titulo LIKE ? OR t.contenido LIKE ?)";
        $searchTerm = $termino;
        $params[] = $searchTerm;
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";

        if ($categoriaId !== null) {
            $where[] = "t.id_categoria = ?";
            $params[] = $categoriaId;
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
                    t.titulo,
                    t.vistas,
                    t.cantidad_respuestas,
                    t.cerrado,
                    t.fijo,
                    t.idCreate,
                    c.nombre AS nombre_categoria,
                    c.icono AS icono_categoria,
                    c.color AS color_categoria,
                    p.nombre AS autor_nombre,
                    p.apellido AS autor_apellido,
                    p.foto_perfil_url AS autor_foto
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
            'pages' => (int)ceil($total / $limit),
            'termino' => $termino
        ];
    }

    /**
     * Busca respuestas por término de búsqueda.
     */
    public static function buscarRespuestas(PDO $pdo, string $termino, int $page = 1, int $limit = 20): array {
        $offset = ($page - 1) * $limit;

        $where = ["r.habilitado = 1", "r.cancelado = 0"];
        $params = [];

        $where[] = "(MATCH(r.contenido) AGAINST(? IN BOOLEAN MODE) OR r.contenido LIKE ?)";
        $params[] = $termino;
        $params[] = "%$termino%";

        $whereSQL = implode(' AND ', $where);

        // Total
        $sqlCount = "SELECT COUNT(*) FROM foro_respuesta r WHERE $whereSQL";
        $stmtCount = $pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        // Datos
        $sql = "SELECT
                    r.id_respuesta,
                    r.contenido,
                    r.idCreate,
                    r.id_tema,
                    t.titulo AS tema_titulo,
                    p.nombre AS autor_nombre,
                    p.apellido AS autor_apellido
                FROM foro_respuesta r
                INNER JOIN foro_tema t ON r.id_tema = t.id_tema
                INNER JOIN usuario u ON r.id_usuario = u.id_usuario
                INNER JOIN persona p ON u.id_persona = p.id_persona
                WHERE $whereSQL
                ORDER BY r.idCreate DESC
                LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'respuestas' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => (int)ceil($total / $limit),
            'termino' => $termino
        ];
    }
}
