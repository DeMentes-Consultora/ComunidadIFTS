<?php

class SiteCustomizationModel
{
    public static function obtenerConfiguracionPublica(PDO $pdo): array
    {
        return [
            'navbar' => self::obtenerNavbar($pdo),
            'sidebar' => self::obtenerSidebar($pdo),
            'carousel' => self::obtenerCarrusel($pdo, false),
        ];
    }

    public static function obtenerConfiguracionAdmin(PDO $pdo): array
    {
        return [
            'navbar' => self::obtenerNavbar($pdo),
            'sidebar' => self::obtenerSidebar($pdo),
            'carousel' => self::obtenerCarrusel($pdo, true),
        ];
    }

    public static function obtenerNavbar(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT id_navbar, brand_text, foto_perfil_url, foto_perfil_public_id, habilitado
             FROM navbar
             WHERE cancelado = 0
             ORDER BY id_navbar ASC
             LIMIT 1'
        );

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'id_navbar' => isset($row['id_navbar']) ? (int)$row['id_navbar'] : null,
            'brand_text' => trim((string)($row['brand_text'] ?? '')),
            'logo_url' => self::nullableText($row['foto_perfil_url'] ?? null),
            'logo_public_id' => self::nullableText($row['foto_perfil_public_id'] ?? null),
            'habilitado' => isset($row['habilitado']) ? (int)$row['habilitado'] : 1,
        ];
    }

    public static function guardarNavbar(PDO $pdo, array $navbar): array
    {
        $actual = self::obtenerNavbar($pdo);
        $brandText = trim((string)($navbar['brand_text'] ?? ''));
        $logoUrl = self::nullableText($navbar['logo_url'] ?? null);
        $logoPublicId = self::nullableText($navbar['logo_public_id'] ?? null);
        $habilitado = isset($navbar['habilitado']) ? (int)((bool)$navbar['habilitado']) : 1;

        if (!empty($actual['id_navbar'])) {
            $stmt = $pdo->prepare(
                'UPDATE navbar
                 SET brand_text = ?, foto_perfil_url = ?, foto_perfil_public_id = ?, habilitado = ?, cancelado = 0
                 WHERE id_navbar = ?'
            );
            $stmt->execute([$brandText, $logoUrl, $logoPublicId, $habilitado, $actual['id_navbar']]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO navbar (brand_text, foto_perfil_url, foto_perfil_public_id, habilitado, cancelado)
                 VALUES (?, ?, ?, ?, 0)'
            );
            $stmt->execute([$brandText, $logoUrl, $logoPublicId, $habilitado]);
        }

        return self::obtenerNavbar($pdo);
    }

    public static function obtenerSidebar(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT id_sidebar, brand_text, foto_perfil_url, foto_perfil_public_id, habilitado
             FROM sidebar
             WHERE cancelado = 0
             ORDER BY id_sidebar ASC
             LIMIT 1'
        );

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'id_sidebar' => isset($row['id_sidebar']) ? (int)$row['id_sidebar'] : null,
            'brand_text' => trim((string)($row['brand_text'] ?? '')),
            'logo_url' => self::nullableText($row['foto_perfil_url'] ?? null),
            'logo_public_id' => self::nullableText($row['foto_perfil_public_id'] ?? null),
            'habilitado' => isset($row['habilitado']) ? (int)$row['habilitado'] : 1,
        ];
    }

    public static function guardarSidebar(PDO $pdo, array $sidebar): array
    {
        $actual = self::obtenerSidebar($pdo);
        $brandText = trim((string)($sidebar['brand_text'] ?? ''));
        $logoUrl = self::nullableText($sidebar['logo_url'] ?? null);
        $logoPublicId = self::nullableText($sidebar['logo_public_id'] ?? null);
        $habilitado = isset($sidebar['habilitado']) ? (int)((bool)$sidebar['habilitado']) : 1;

        if (!empty($actual['id_sidebar'])) {
            $stmt = $pdo->prepare(
                'UPDATE sidebar
                 SET brand_text = ?, foto_perfil_url = ?, foto_perfil_public_id = ?, habilitado = ?, cancelado = 0
                 WHERE id_sidebar = ?'
            );
            $stmt->execute([$brandText, $logoUrl, $logoPublicId, $habilitado, $actual['id_sidebar']]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO sidebar (brand_text, foto_perfil_url, foto_perfil_public_id, habilitado, cancelado)
                 VALUES (?, ?, ?, ?, 0)'
            );
            $stmt->execute([$brandText, $logoUrl, $logoPublicId, $habilitado]);
        }

        return self::obtenerSidebar($pdo);
    }

    public static function obtenerCarrusel(PDO $pdo, bool $includeDisabled = false): array
    {
        $sql = 'SELECT id_carrousel, titulo, descripcion, enlace, orden_visual, foto_perfil_url, foto_perfil_public_id, habilitado
                FROM carrousel
                WHERE cancelado = 0';

        if (!$includeDisabled) {
            $sql .= ' AND habilitado = 1';
        }

        $sql .= ' ORDER BY orden_visual ASC, id_carrousel ASC';

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $row): array {
            return [
                'id_carrousel' => (int)$row['id_carrousel'],
                'titulo' => trim((string)($row['titulo'] ?? '')),
                'descripcion' => trim((string)($row['descripcion'] ?? '')),
                'enlace' => self::nullableText($row['enlace'] ?? null),
                'orden_visual' => isset($row['orden_visual']) ? (int)$row['orden_visual'] : 0,
                'foto_perfil_url' => self::nullableText($row['foto_perfil_url'] ?? null),
                'foto_perfil_public_id' => self::nullableText($row['foto_perfil_public_id'] ?? null),
                'habilitado' => isset($row['habilitado']) ? (int)$row['habilitado'] : 1,
            ];
        }, $rows);
    }

    public static function guardarCarrusel(PDO $pdo, array $slides): array
    {
        $existentes = self::obtenerCarrusel($pdo, true);
        $existentesPorId = [];
        foreach ($existentes as $slideExistente) {
            $existentesPorId[(int)$slideExistente['id_carrousel']] = $slideExistente;
        }

        $idsPersistidos = [];

        foreach ($slides as $index => $slide) {
            $id = isset($slide['id_carrousel']) ? (int)$slide['id_carrousel'] : 0;
            $titulo = trim((string)($slide['titulo'] ?? ''));
            $descripcion = trim((string)($slide['descripcion'] ?? ''));
            $enlace = self::nullableText($slide['enlace'] ?? null);
            $ordenVisual = isset($slide['orden_visual']) ? (int)$slide['orden_visual'] : ($index + 1);
            $fotoUrl = self::nullableText($slide['foto_perfil_url'] ?? null);
            $fotoPublicId = self::nullableText($slide['foto_perfil_public_id'] ?? null);
            $habilitado = isset($slide['habilitado']) ? (int)((bool)$slide['habilitado']) : 1;

            if ($id > 0 && isset($existentesPorId[$id])) {
                $stmt = $pdo->prepare(
                    'UPDATE carrousel
                     SET titulo = ?, descripcion = ?, enlace = ?, orden_visual = ?, foto_perfil_url = ?, foto_perfil_public_id = ?, habilitado = ?, cancelado = 0
                     WHERE id_carrousel = ?'
                );
                $stmt->execute([$titulo, $descripcion, $enlace, $ordenVisual, $fotoUrl, $fotoPublicId, $habilitado, $id]);
                $idsPersistidos[] = $id;
                continue;
            }

            $stmt = $pdo->prepare(
                'INSERT INTO carrousel (titulo, descripcion, enlace, orden_visual, foto_perfil_url, foto_perfil_public_id, habilitado, cancelado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 0)'
            );
            $stmt->execute([$titulo, $descripcion, $enlace, $ordenVisual, $fotoUrl, $fotoPublicId, $habilitado]);
            $idsPersistidos[] = (int)$pdo->lastInsertId();
        }

        foreach ($existentesPorId as $existingId => $existingSlide) {
            if (in_array($existingId, $idsPersistidos, true)) {
                continue;
            }

            $stmt = $pdo->prepare('UPDATE carrousel SET cancelado = 1 WHERE id_carrousel = ?');
            $stmt->execute([$existingId]);
        }

        return self::obtenerCarrusel($pdo, true);
    }

    public static function obtenerEstadisticasDashboard(PDO $pdo): array
    {
        $sql = 'SELECT
                    (SELECT COUNT(*) FROM usuario WHERE cancelado = 0) AS usuarios_registrados,
                    (SELECT COUNT(*) FROM usuario WHERE cancelado = 0 AND id_rol = 2) AS alumnos,
                    (SELECT COUNT(*) FROM usuario WHERE cancelado = 0 AND id_rol = 1) AS administradores,
                    (SELECT COUNT(*) FROM usuario WHERE cancelado = 0 AND habilitado = 0) AS pendientes_aprobacion,
                    (SELECT COUNT(*) FROM institucion WHERE cancelado = 0) AS instituciones,
                    (SELECT COUNT(*) FROM carrera WHERE cancelado = 0) AS carreras';

        $stmt = $pdo->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'usuarios_registrados' => (int)($row['usuarios_registrados'] ?? 0),
            'alumnos' => (int)($row['alumnos'] ?? 0),
            'administradores' => (int)($row['administradores'] ?? 0),
            'pendientes_aprobacion' => (int)($row['pendientes_aprobacion'] ?? 0),
            'instituciones' => (int)($row['instituciones'] ?? 0),
            'carreras' => (int)($row['carreras'] ?? 0),
        ];
    }

    private static function nullableText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string)$value);
        return $text === '' ? null : $text;
    }
}