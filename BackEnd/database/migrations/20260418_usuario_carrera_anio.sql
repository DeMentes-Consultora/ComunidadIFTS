-- Agrega datos academicos del alumno al usuario
-- Fecha: 2026-04-18

ALTER TABLE usuario
  ADD COLUMN id_carrera INT(11) NULL AFTER id_institucion,
  ADD COLUMN anio_cursada TINYINT(1) NULL AFTER id_carrera;

ALTER TABLE usuario
  ADD KEY idx_usuario_carrera (id_carrera);

ALTER TABLE usuario
  ADD CONSTRAINT fk_usuario_carrera
    FOREIGN KEY (id_carrera) REFERENCES carrera (id_carrera)
    ON UPDATE CASCADE
    ON DELETE SET NULL;
