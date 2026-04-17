CREATE TABLE IF NOT EXISTS `sidebar` (
  `id_sidebar` int(11) NOT NULL AUTO_INCREMENT,
  `brand_text` varchar(255) DEFAULT NULL,
  `foto_perfil_public_id` varchar(255) DEFAULT NULL,
  `foto_perfil_url` varchar(512) DEFAULT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_sidebar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sidebar` (`brand_text`, `foto_perfil_public_id`, `foto_perfil_url`, `habilitado`, `cancelado`)
SELECT 'Comunidad IFTS', NULL, NULL, 1, 0
WHERE NOT EXISTS (
  SELECT 1
  FROM `sidebar`
  WHERE `cancelado` = 0
);
