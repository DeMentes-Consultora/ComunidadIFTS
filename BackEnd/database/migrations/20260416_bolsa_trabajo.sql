-- ============================================================
-- Migración: Bolsa de Trabajo
-- Fecha: 2026-04-16
-- Descripción:
--   1. Crea tabla 'bolsadetrabajo' si no existe
--   2. Corrige columna 'habiltado' (typo) -> 'habilitado' de forma compatible
--   3. Corrige valor por defecto de 'cancelado' (debe ser 0)
--   4. Agrega claves foráneas faltantes a bolsadetrabajo
--   5. Crea tabla 'postulacion' si no existe
-- Estados de oferta:
--   habilitado=0, cancelado=0  -> PENDIENTE de revisión por admin
--   habilitado=1, cancelado=0  -> PUBLICADA (visible a alumnos)
--   habilitado=0, cancelado=1  -> RECHAZADA / dada de baja
-- ============================================================

-- 1. Crear tabla bolsadetrabajo si no existe
CREATE TABLE IF NOT EXISTS `bolsadetrabajo` (
  `id_bolsaDeTrabajo` int(11) NOT NULL AUTO_INCREMENT,
  `id_institucion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tituloOferta` varchar(255) NOT NULL,
  `textoOferta` text NOT NULL,
  `habilitado` int(1) NOT NULL DEFAULT 0,
  `cancelado` int(1) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_bolsaDeTrabajo`),
  KEY `idx_bolsa_institucion` (`id_institucion`),
  KEY `idx_bolsa_usuario` (`id_usuario`),
  KEY `idx_bolsa_estado` (`habilitado`, `cancelado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Compatibilidad con typo histórico: habiltado -> habilitado
ALTER TABLE `bolsadetrabajo`
  ADD COLUMN IF NOT EXISTS `habilitado` int(1) NOT NULL DEFAULT 0 AFTER `textoOferta`;

SET @has_habiltado := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'bolsadetrabajo'
    AND COLUMN_NAME = 'habiltado'
);

SET @sql_copy_habiltado := IF(
  @has_habiltado > 0,
  'UPDATE bolsadetrabajo SET habilitado = habiltado WHERE habilitado <> habiltado;',
  'SELECT 1;'
);
PREPARE stmt_copy_habiltado FROM @sql_copy_habiltado;
EXECUTE stmt_copy_habiltado;
DEALLOCATE PREPARE stmt_copy_habiltado;

SET @sql_drop_habiltado := IF(
  @has_habiltado > 0,
  'ALTER TABLE bolsadetrabajo DROP COLUMN habiltado;',
  'SELECT 1;'
);
PREPARE stmt_drop_habiltado FROM @sql_drop_habiltado;
EXECUTE stmt_drop_habiltado;
DEALLOCATE PREPARE stmt_drop_habiltado;

-- 3. Corregir default de cancelado (oferta recién creada no está cancelada)
ALTER TABLE `bolsadetrabajo`
  MODIFY `cancelado` int(1) NOT NULL DEFAULT 0,
  MODIFY `habilitado` int(1) NOT NULL DEFAULT 0;

-- 4. Agregar claves foráneas faltantes a bolsadetrabajo
SET @has_fk_bolsa_institucion := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'bolsadetrabajo'
    AND CONSTRAINT_NAME = 'fk_bolsa_institucion'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql_add_fk_bolsa_institucion := IF(
  @has_fk_bolsa_institucion = 0,
  'ALTER TABLE bolsadetrabajo ADD CONSTRAINT fk_bolsa_institucion FOREIGN KEY (id_institucion) REFERENCES institucion (id_institucion) ON UPDATE CASCADE ON DELETE RESTRICT;',
  'SELECT 1;'
);
PREPARE stmt_add_fk_bolsa_institucion FROM @sql_add_fk_bolsa_institucion;
EXECUTE stmt_add_fk_bolsa_institucion;
DEALLOCATE PREPARE stmt_add_fk_bolsa_institucion;

SET @has_fk_bolsa_usuario := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'bolsadetrabajo'
    AND CONSTRAINT_NAME = 'fk_bolsa_usuario_creador'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql_add_fk_bolsa_usuario := IF(
  @has_fk_bolsa_usuario = 0,
  'ALTER TABLE bolsadetrabajo ADD CONSTRAINT fk_bolsa_usuario_creador FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT;',
  'SELECT 1;'
);
PREPARE stmt_add_fk_bolsa_usuario FROM @sql_add_fk_bolsa_usuario;
EXECUTE stmt_add_fk_bolsa_usuario;
DEALLOCATE PREPARE stmt_add_fk_bolsa_usuario;

-- 5. Crear tabla postulacion
CREATE TABLE IF NOT EXISTS `postulacion` (
  `id_postulacion`    int(11)       NOT NULL AUTO_INCREMENT,
  `id_bolsaDeTrabajo` int(11)       NOT NULL COMMENT 'FK a bolsadetrabajo',
  `id_usuario`        int(11)       NOT NULL COMMENT 'Alumno que se postula',
  `cv_url`            varchar(512)  DEFAULT NULL COMMENT 'URL pública del CV en Cloudinary',
  `cv_public_id`      varchar(512)  DEFAULT NULL COMMENT 'Public ID en Cloudinary para borrado',
  `cancelado`         int(1)        NOT NULL DEFAULT 0,
  `idCreate`          timestamp     NOT NULL DEFAULT current_timestamp(),
  `idUpdate`          timestamp     NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_postulacion`),
  UNIQUE KEY `uq_postulacion` (`id_bolsaDeTrabajo`, `id_usuario`),
  CONSTRAINT `fk_postulacion_oferta`
    FOREIGN KEY (`id_bolsaDeTrabajo`) REFERENCES `bolsadetrabajo` (`id_bolsaDeTrabajo`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_postulacion_alumno`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
