ALTER TABLE navbar
    ADD COLUMN IF NOT EXISTS brand_text VARCHAR(255) NULL AFTER id_navbar;

ALTER TABLE carrousel
    ADD COLUMN IF NOT EXISTS titulo VARCHAR(255) NULL AFTER id_carousel,
    ADD COLUMN IF NOT EXISTS descripcion VARCHAR(500) NULL AFTER titulo,
    ADD COLUMN IF NOT EXISTS enlace VARCHAR(255) NULL AFTER descripcion,
    ADD COLUMN IF NOT EXISTS orden_visual INT(11) NOT NULL DEFAULT 0 AFTER enlace;

INSERT INTO navbar (brand_text, foto_perfil_public_id, foto_perfil_url, habilitado, cancelado)
SELECT 'Comunidad IFTS', NULL, NULL, 1, 0
WHERE NOT EXISTS (
    SELECT 1
    FROM navbar
    WHERE cancelado = 0
);

INSERT INTO carrousel (titulo, descripcion, enlace, orden_visual, habilitado, cancelado)
SELECT 'Bienvenido a Comunidad IFTS', 'Conecta con todos los Institutos Superiores de Tecnologia de Buenos Aires', '#', 1, 1, 0
WHERE NOT EXISTS (
    SELECT 1
    FROM carrousel
    WHERE cancelado = 0
);

INSERT INTO carrousel (titulo, descripcion, enlace, orden_visual, habilitado, cancelado)
SELECT 'IFTS y comunidad', 'Descubre carreras, instituciones y oportunidades para crecer profesionalmente', '#', 2, 1, 0
WHERE NOT EXISTS (
    SELECT 1
    FROM (
        SELECT COUNT(*) AS total
        FROM carrousel
        WHERE cancelado = 0
    ) AS resumen
    WHERE resumen.total >= 2
);