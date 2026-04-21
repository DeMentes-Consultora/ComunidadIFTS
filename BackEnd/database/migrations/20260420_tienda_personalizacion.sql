CREATE TABLE IF NOT EXISTS `tienda_carrousel` (
  `id_tienda_carrousel` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL DEFAULT '',
  `descripcion` text DEFAULT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `orden_visual` int(11) NOT NULL DEFAULT 1,
  `foto_perfil_url` text DEFAULT NULL,
  `foto_perfil_public_id` varchar(255) DEFAULT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_tienda_carrousel`),
  KEY `idx_tienda_carrousel_cancelado` (`cancelado`),
  KEY `idx_tienda_carrousel_habilitado` (`habilitado`),
  KEY `idx_tienda_carrousel_orden_visual` (`orden_visual`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tienda_producto` (
  `id_tienda_producto` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL DEFAULT '',
  `descripcion` text DEFAULT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `orden_visual` int(11) NOT NULL DEFAULT 1,
  `foto_perfil_url` text DEFAULT NULL,
  `foto_perfil_public_id` varchar(255) DEFAULT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_tienda_producto`),
  KEY `idx_tienda_producto_cancelado` (`cancelado`),
  KEY `idx_tienda_producto_habilitado` (`habilitado`),
  KEY `idx_tienda_producto_orden_visual` (`orden_visual`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;