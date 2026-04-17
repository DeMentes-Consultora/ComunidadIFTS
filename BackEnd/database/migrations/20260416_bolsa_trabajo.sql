-- ============================================================
-- Migración: Bolsa de Trabajo
-- Fecha: 2026-04-16
-- Descripción:
--   1. Corrige columna 'habiltado' (typo) -> 'habilitado' en bolsadetrabajo
--   2. Corrige valor por defecto de 'cancelado' (era 1, debe ser 0)
--   3. Agrega claves foráneas a bolsadetrabajo
--   4. Crea tabla 'postulacion'
-- Estados de oferta:
--   habilitado=0, cancelado=0  -> PENDIENTE de revisión por admin
--   habilitado=1, cancelado=0  -> PUBLICADA (visible a alumnos)
--   habilitado=0, cancelado=1  -> RECHAZADA / dada de baja
-- ============================================================

-- 1. Corregir typo en columna 'habiltado' -> 'habilitado'
ALTER TABLE `bolsadetrabajo`
  CHANGE `habiltado` `habilitado` int(1) NOT NULL DEFAULT 0;

-- 2. Corregir default de 'cancelado' (oferta recién creada no está cancelada)
ALTER TABLE `bolsadetrabajo`
  MODIFY `cancelado` int(1) NOT NULL DEFAULT 0;

-- 3. Agregar claves foráneas a bolsadetrabajo
--    (om. si ya existen en el entorno)
ALTER TABLE `bolsadetrabajo`
  ADD CONSTRAINT `fk_bolsa_institucion`
    FOREIGN KEY (`id_institucion`) REFERENCES `institucion` (`id_institucion`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_bolsa_usuario_creador`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
    ON UPDATE CASCADE ON DELETE RESTRICT;

-- 4. Crear tabla postulacion
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
