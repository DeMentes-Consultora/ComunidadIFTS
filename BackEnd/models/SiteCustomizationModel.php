<?php

class SiteCustomizationModel
{
    private static bool $tablaTiendaCarruselCreada = false;
    private static bool $tablaTiendaGaleriaCreada = false;
    private static bool $tablaFooterBrandingCreada = false;

    public static function obtenerConfiguracionPublica(PDO $pdo): array
    {
        return [
            'navbar' => self::obtenerNavbar($pdo),
            'sidebar' => self::obtenerSidebar($pdo),
            'footer_branding' => self::obtenerFooterBranding($pdo),
            'carousel' => self::obtenerCarrusel($pdo, false),
            'shop_carousel' => self::obtenerTiendaCarrusel($pdo, false),
            'shop_gallery' => self::obtenerTiendaGaleria($pdo, false),
        ];
    }

    public static function obtenerConfiguracionAdmin(PDO $pdo): array
    {
        return [
            'navbar' => self::obtenerNavbar($pdo),
            'sidebar' => self::obtenerSidebar($pdo),
            'footer_branding' => self::obtenerFooterBranding($pdo),
            'carousel' => self::obtenerCarrusel($pdo, true),
            'shop_carousel' => self::obtenerTiendaCarrusel($pdo, true),
            'shop_gallery' => self::obtenerTiendaGaleria($pdo, true),
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

    public static function obtenerFooterBranding(PDO $pdo): array
    {
        self::asegurarTablaFooterBranding($pdo);

        $stmt = $pdo->query(
            'SELECT id_footer_branding, developer_text, enlace, foto_perfil_url, foto_perfil_public_id, habilitado
             FROM footer_branding
             WHERE cancelado = 0
             ORDER BY id_footer_branding ASC
             LIMIT 1'
        );

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'id_footer_branding' => isset($row['id_footer_branding']) ? (int)$row['id_footer_branding'] : null,
            'developer_text' => trim((string)($row['developer_text'] ?? 'Desarrollado por DeMentesConsultora')),
            'link_url' => self::nullableText($row['enlace'] ?? null),
            'logo_url' => self::nullableText($row['foto_perfil_url'] ?? null),
            'logo_public_id' => self::nullableText($row['foto_perfil_public_id'] ?? null),
            'habilitado' => isset($row['habilitado']) ? (int)$row['habilitado'] : 1,
        ];
    }

    public static function guardarFooterBranding(PDO $pdo, array $footerBranding): array
    {
        self::asegurarTablaFooterBranding($pdo);

        $actual = self::obtenerFooterBranding($pdo);
        $developerText = trim((string)($footerBranding['developer_text'] ?? ''));
        $linkUrl = self::nullableText($footerBranding['link_url'] ?? null);
        $logoUrl = self::nullableText($footerBranding['logo_url'] ?? null);
        $logoPublicId = self::nullableText($footerBranding['logo_public_id'] ?? null);
        $habilitado = isset($footerBranding['habilitado']) ? (int)((bool)$footerBranding['habilitado']) : 1;

        if (!empty($actual['id_footer_branding'])) {
            $stmt = $pdo->prepare(
                'UPDATE footer_branding
                 SET developer_text = ?, enlace = ?, foto_perfil_url = ?, foto_perfil_public_id = ?, habilitado = ?, cancelado = 0
                 WHERE id_footer_branding = ?'
            );
            $stmt->execute([$developerText, $linkUrl, $logoUrl, $logoPublicId, $habilitado, $actual['id_footer_branding']]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO footer_branding (developer_text, enlace, foto_perfil_url, foto_perfil_public_id, habilitado, cancelado)
                 VALUES (?, ?, ?, ?, ?, 0)'
            );
            $stmt->execute([$developerText, $linkUrl, $logoUrl, $logoPublicId, $habilitado]);
        }

        return self::obtenerFooterBranding($pdo);
    }

    public static function obtenerCarrusel(PDO $pdo, bool $includeDisabled = false): array
    {
        return self::obtenerColeccionSlides($pdo, 'carrousel', 'id_carrousel', $includeDisabled);
    }

    public static function guardarCarrusel(PDO $pdo, array $slides): array
    {
        return self::guardarColeccionSlides($pdo, 'carrousel', 'id_carrousel', $slides);
    }

    public static function obtenerTiendaCarrusel(PDO $pdo, bool $includeDisabled = false): array
    {
        self::asegurarTablaTiendaCarrusel($pdo);
        return self::obtenerColeccionSlides($pdo, 'tienda_carrousel', 'id_tienda_carrousel', $includeDisabled);
    }

    public static function guardarTiendaCarrusel(PDO $pdo, array $slides): array
    {
        self::asegurarTablaTiendaCarrusel($pdo);
        return self::guardarColeccionSlides($pdo, 'tienda_carrousel', 'id_tienda_carrousel', $slides);
    }

    public static function obtenerTiendaGaleria(PDO $pdo, bool $includeDisabled = false): array
    {
        self::asegurarTablaTiendaGaleria($pdo);
        return self::obtenerColeccionSlides($pdo, 'tienda_producto', 'id_tienda_producto', $includeDisabled);
    }

    public static function guardarTiendaGaleria(PDO $pdo, array $slides): array
    {
        self::asegurarTablaTiendaGaleria($pdo);
        return self::guardarColeccionSlides($pdo, 'tienda_producto', 'id_tienda_producto', $slides);
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

    private static function obtenerColeccionSlides(PDO $pdo, string $table, string $idColumn, bool $includeDisabled): array
    {
        $sql = sprintf(
            'SELECT %s AS id_slide, titulo, descripcion, enlace, orden_visual, foto_perfil_url, foto_perfil_public_id, habilitado
             FROM %s
             WHERE cancelado = 0',
            $idColumn,
            $table
        );

        if (!$includeDisabled) {
            $sql .= ' AND habilitado = 1';
        }

        $sql .= ' ORDER BY orden_visual ASC, id_slide ASC';

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $row): array {
            return [
                'id_carrousel' => (int)$row['id_slide'],
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

    private static function guardarColeccionSlides(PDO $pdo, string $table, string $idColumn, array $slides): array
    {
        $existentes = self::obtenerColeccionSlides($pdo, $table, $idColumn, true);
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
                    sprintf(
                        'UPDATE %s
                         SET titulo = ?, descripcion = ?, enlace = ?, orden_visual = ?, foto_perfil_url = ?, foto_perfil_public_id = ?, habilitado = ?, cancelado = 0
                         WHERE %s = ?',
                        $table,
                        $idColumn
                    )
                );
                $stmt->execute([$titulo, $descripcion, $enlace, $ordenVisual, $fotoUrl, $fotoPublicId, $habilitado, $id]);
                $idsPersistidos[] = $id;
                continue;
            }

            $stmt = $pdo->prepare(
                sprintf(
                    'INSERT INTO %s (titulo, descripcion, enlace, orden_visual, foto_perfil_url, foto_perfil_public_id, habilitado, cancelado)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 0)',
                    $table
                )
            );
            $stmt->execute([$titulo, $descripcion, $enlace, $ordenVisual, $fotoUrl, $fotoPublicId, $habilitado]);
            $idsPersistidos[] = (int)$pdo->lastInsertId();
        }

        foreach ($existentesPorId as $existingId => $existingSlide) {
            if (in_array($existingId, $idsPersistidos, true)) {
                continue;
            }

            $stmt = $pdo->prepare(sprintf('UPDATE %s SET cancelado = 1 WHERE %s = ?', $table, $idColumn));
            $stmt->execute([$existingId]);
        }

        return self::obtenerColeccionSlides($pdo, $table, $idColumn, true);
    }

    private static function asegurarTablaTiendaCarrusel(PDO $pdo): void
    {
        if (self::$tablaTiendaCarruselCreada) {
            return;
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS tienda_carrousel (
                id_tienda_carrousel INT(11) NOT NULL AUTO_INCREMENT,
                titulo VARCHAR(255) NOT NULL DEFAULT "",
                descripcion TEXT NULL,
                enlace VARCHAR(255) NULL,
                orden_visual INT(11) NOT NULL DEFAULT 1,
                foto_perfil_url TEXT NULL,
                foto_perfil_public_id VARCHAR(255) NULL,
                habilitado TINYINT(1) NOT NULL DEFAULT 1,
                cancelado TINYINT(1) NOT NULL DEFAULT 0,
                idCreate TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                idUpdate TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id_tienda_carrousel)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tablaTiendaCarruselCreada = true;
    }

    private static function asegurarTablaTiendaGaleria(PDO $pdo): void
    {
        if (self::$tablaTiendaGaleriaCreada) {
            return;
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS tienda_producto (
                id_tienda_producto INT(11) NOT NULL AUTO_INCREMENT,
                titulo VARCHAR(255) NOT NULL DEFAULT "",
                descripcion TEXT NULL,
                enlace VARCHAR(255) NULL,
                orden_visual INT(11) NOT NULL DEFAULT 1,
                foto_perfil_url TEXT NULL,
                foto_perfil_public_id VARCHAR(255) NULL,
                habilitado TINYINT(1) NOT NULL DEFAULT 1,
                cancelado TINYINT(1) NOT NULL DEFAULT 0,
                idCreate TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                idUpdate TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id_tienda_producto)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        self::$tablaTiendaGaleriaCreada = true;
    }

    private static function asegurarTablaFooterBranding(PDO $pdo): void
    {
        if (self::$tablaFooterBrandingCreada) {
            return;
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS footer_branding (
                id_footer_branding INT(11) NOT NULL AUTO_INCREMENT,
                developer_text VARCHAR(255) NOT NULL DEFAULT "Desarrollado por DeMentesConsultora",
                enlace VARCHAR(255) NULL,
                foto_perfil_url TEXT NULL,
                foto_perfil_public_id VARCHAR(255) NULL,
                habilitado TINYINT(1) NOT NULL DEFAULT 1,
                cancelado TINYINT(1) NOT NULL DEFAULT 0,
                idCreate TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                idUpdate TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id_footer_branding)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $columnas = $pdo->query('SHOW COLUMNS FROM footer_branding LIKE "enlace"')->fetchAll(PDO::FETCH_ASSOC);
        if (count($columnas) === 0) {
            $pdo->exec('ALTER TABLE footer_branding ADD COLUMN enlace VARCHAR(255) NULL AFTER developer_text');
        }

        self::$tablaFooterBrandingCreada = true;
    }
}