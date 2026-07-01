-- Migración: Módulo de Foro - ComunidadIFTS
-- Fecha: 30 de junio de 2026
-- Descripción: Creación de tablas para el módulo de foro con categorías, temas, respuestas, adjuntos y vistas

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `foro_categoria`
--

CREATE TABLE `foro_categoria` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#3f51b5',
  `orden` int(11) NOT NULL DEFAULT 0,
  `habilitado` int(1) NOT NULL DEFAULT 1,
  `cancelado` int(1) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Volcado de datos para la tabla `foro_categoria`
--

INSERT INTO `foro_categoria` (`nombre`, `descripcion`, `icono`, `color`, `orden`) VALUES
('General', 'Temas generales de la comunidad', 'forum', '#3f51b5', 1),
('Tecnología', 'Discusiones sobre tecnología y desarrollo', 'computer', '#4caf50', 2),
('Instituciones', 'Temas relacionados con los IFTS', 'school', '#ff9800', 3),
('Bolsa de Trabajo', 'Ofertas y oportunidades laborales', 'work', '#e91e63', 4),
('Ayuda', 'Consultas y soporte técnico', 'help', '#9c27b0', 5);

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `foro_tema`
--

CREATE TABLE `foro_tema` (
  `id_tema` int(11) NOT NULL AUTO_INCREMENT,
  `id_categoria` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `vistas` int(11) NOT NULL DEFAULT 0,
  `cantidad_respuestas` int(11) NOT NULL DEFAULT 0,
  `cerrado` int(1) NOT NULL DEFAULT 0,
  `motivo_cierre` varchar(500) DEFAULT NULL,
  `fijo` int(1) NOT NULL DEFAULT 0,
  `habilitado` int(1) NOT NULL DEFAULT 1,
  `cancelado` int(1) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_tema`),
  KEY `idx_tema_categoria` (`id_categoria`),
  KEY `idx_tema_usuario` (`id_usuario`),
  KEY `idx_tema_estado` (`habilitado`, `cancelado`, `cerrado`),
  KEY `idx_tema_fecha` (`idCreate`),
  CONSTRAINT `fk_tema_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `foro_categoria` (`id_categoria`) ON UPDATE CASCADE,
  CONSTRAINT `fk_tema_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `foro_respuesta`
--

CREATE TABLE `foro_respuesta` (
  `id_respuesta` int(11) NOT NULL AUTO_INCREMENT,
  `id_tema` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `citando_id` int(11) DEFAULT NULL,
  `habilitado` int(1) NOT NULL DEFAULT 1,
  `cancelado` int(1) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_respuesta`),
  KEY `idx_respuesta_tema` (`id_tema`),
  KEY `idx_respuesta_usuario` (`id_usuario`),
  CONSTRAINT `fk_respuesta_tema` FOREIGN KEY (`id_tema`) REFERENCES `foro_tema` (`id_tema`) ON UPDATE CASCADE,
  CONSTRAINT `fk_respuesta_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `foro_adjunto`
--

CREATE TABLE `foro_adjunto` (
  `id_adjunto` int(11) NOT NULL AUTO_INCREMENT,
  `id_tema` int(11) DEFAULT NULL,
  `id_respuesta` int(11) DEFAULT NULL,
  `tipo` enum('imagen','pdf','video') NOT NULL,
  `archivo_url` varchar(512) NOT NULL,
  `archivo_public_id` varchar(255) DEFAULT NULL,
  `archivo_nombre_original` varchar(255) NOT NULL,
  `archivo_tamano_bytes` int(11) NOT NULL,
  `habilitado` int(1) NOT NULL DEFAULT 1,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_adjunto`),
  KEY `idx_adjunto_tema` (`id_tema`),
  KEY `idx_adjunto_respuesta` (`id_respuesta`),
  CONSTRAINT `fk_adjunto_tema` FOREIGN KEY (`id_tema`) REFERENCES `foro_tema` (`id_tema`) ON UPDATE CASCADE,
  CONSTRAINT `fk_adjunto_respuesta` FOREIGN KEY (`id_respuesta`) REFERENCES `foro_respuesta` (`id_respuesta`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `foro_vista`
--

CREATE TABLE `foro_vista` (
  `id_vista` int(11) NOT NULL AUTO_INCREMENT,
  `id_tema` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_vista`),
  KEY `idx_vista_tema` (`id_tema`),
  CONSTRAINT `fk_vista_tema` FOREIGN KEY (`id_tema`) REFERENCES `foro_tema` (`id_tema`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Índices fulltext para búsqueda
--

ALTER TABLE `foro_tema` ADD FULLTEXT KEY `ft_tema_busqueda` (`titulo`, `contenido`);
ALTER TABLE `foro_respuesta` ADD FULLTEXT KEY `ft_respuesta_busqueda` (`contenido`);

COMMIT;
