CREATE TABLE IF NOT EXISTS materia (
  id_materia INT(11) NOT NULL AUTO_INCREMENT,
  nombre_materia VARCHAR(250) NOT NULL,
  habilitado INT(11) NOT NULL DEFAULT 1,
  cancelado INT(11) NOT NULL DEFAULT 0,
  idCreate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  idUpdate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_materia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS carrera_materia (
  id_carrera_materia INT(11) NOT NULL AUTO_INCREMENT,
  id_carrera INT(11) NOT NULL,
  id_materia INT(11) NOT NULL,
  habilitado INT(11) NOT NULL DEFAULT 1,
  cancelado INT(11) NOT NULL DEFAULT 0,
  idCreate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  idUpdate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_carrera_materia),
  UNIQUE KEY uk_carrera_materia (id_carrera, id_materia),
  KEY idx_cm_carrera (id_carrera),
  KEY idx_cm_materia (id_materia),
  CONSTRAINT fk_cm_carrera FOREIGN KEY (id_carrera) REFERENCES carrera (id_carrera) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_cm_materia FOREIGN KEY (id_materia) REFERENCES materia (id_materia) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
