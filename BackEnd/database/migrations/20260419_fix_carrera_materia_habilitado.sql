-- Corrige typo historico en carrera_materia: habiltado -> habilitado
-- Fecha: 2026-04-19

-- 1) Asegurar columna habilitado si no existe
SET @has_habilitado := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'carrera_materia'
    AND COLUMN_NAME = 'habilitado'
);

SET @sql_add_habilitado := IF(
  @has_habilitado = 0,
  'ALTER TABLE carrera_materia ADD COLUMN habilitado INT(11) NOT NULL DEFAULT 1 AFTER id_materia;',
  'SELECT 1;'
);
PREPARE stmt_add_habilitado FROM @sql_add_habilitado;
EXECUTE stmt_add_habilitado;
DEALLOCATE PREPARE stmt_add_habilitado;

-- 2) Si existe habiltado, copiar valores a habilitado
SET @has_habiltado := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'carrera_materia'
    AND COLUMN_NAME = 'habiltado'
);

SET @sql_copy_habiltado := IF(
  @has_habiltado > 0,
  'UPDATE carrera_materia SET habilitado = habiltado WHERE habilitado <> habiltado;',
  'SELECT 1;'
);
PREPARE stmt_copy_habiltado FROM @sql_copy_habiltado;
EXECUTE stmt_copy_habiltado;
DEALLOCATE PREPARE stmt_copy_habiltado;

-- 3) Si existe habiltado, eliminar columna typo
SET @sql_drop_habiltado := IF(
  @has_habiltado > 0,
  'ALTER TABLE carrera_materia DROP COLUMN habiltado;',
  'SELECT 1;'
);
PREPARE stmt_drop_habiltado FROM @sql_drop_habiltado;
EXECUTE stmt_drop_habiltado;
DEALLOCATE PREPARE stmt_drop_habiltado;

-- 4) Normalizar definicion final
ALTER TABLE carrera_materia
  MODIFY habilitado INT(11) NOT NULL DEFAULT 1;
