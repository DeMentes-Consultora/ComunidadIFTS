-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-02-2026 a las 03:06:14
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `comunidad_ifts`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrera`
--

CREATE TABLE `carrera` (
  `id_carrera` int(11) NOT NULL,
  `nombre_carrera` varchar(250) NOT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrera`
--

INSERT INTO `carrera` (`id_carrera`, `nombre_carrera`, `habilitado`, `cancelado`, `idCreate`, `idUpdate`) VALUES
(1, 'Tecnicatura Superior  en  Laboratorio de Análisis Clínicos', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(2, 'Tecnicatura Superior  en Administración Comercial', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(3, 'Tecnicatura Superior  en Administración de Servicios Salud', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(4, 'Tecnicatura Superior  en Administración y Relaciones de Trabajo', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(5, 'Tecnicatura Superior  en Comercio Internacional y Aduanas', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(6, 'Tecnicatura Superior  en Desarrollo de Software de Simuladores', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(7, 'Tecnicatura Superior  en Emprendimientos Gastronómicos', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(8, 'Tecnicatura Superior  en Guía de Turismo de la Ciudad de Buenos Aires', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(9, 'Tecnicatura Superior en Administración', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(10, 'Tecnicatura Superior en Administración de Empresas', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(11, 'Tecnicatura Superior en Administración Financiera del Sector Público', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(12, 'Tecnicatura Superior en Administración Hotelera', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(13, 'Tecnicatura Superior en Administración Pública', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(14, 'Tecnicatura Superior en Administración Tributaria', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(15, 'Tecnicatura Superior en Alta Cocina', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(16, 'Tecnicatura Superior en Análisis de Sistemas', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(17, 'Tecnicatura Superior en Automotores Híbridos y Eléctricos', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(18, 'Tecnicatura Superior en Bibliotecología', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(19, 'Tecnicatura Superior en Ciencia de Datos e Inteligencia Artificial', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(20, 'Tecnicatura Superior en Comercialización', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(21, 'Tecnicatura Superior en Comercio Internacional', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(22, 'Tecnicatura Superior en Construcción Sustentable', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(23, 'Tecnicatura Superior en Coordinación y Gestión de Procesos Constructivos', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(24, 'Tecnicatura Superior en Desarrollo de Software', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(25, 'Tecnicatura Superior en Desarrollo de Software a Distancia', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(26, 'Tecnicatura Superior en Diseño Gráfico y Multimedia', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(27, 'Tecnicatura Superior en Distribución de la Energía Eléctrica', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(28, 'Tecnicatura Superior en Eficiencia Energética', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(29, 'Tecnicatura Superior en Gastronomía', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(30, 'Tecnicatura Superior en Gestión Ambiental', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(31, 'Tecnicatura Superior en Gestión de Energías Renovables', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(32, 'Tecnicatura Superior en Gestión de Políticas Culturales', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(33, 'Tecnicatura Superior en Gestión Integral del Riesgo', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(34, 'Tecnicatura Superior en Gestión Parlamentaria', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(35, 'Tecnicatura Superior en Gestión Turística', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(36, 'Tecnicatura Superior en Hoteleria', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(37, 'Tecnicatura Superior en Interpretación de Lengua de Señas Argentina, Sordos e Hipoacúsicos', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(38, 'Tecnicatura Superior en Mecatrónica', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(39, 'Tecnicatura Superior en Pedagogía y Educación Social con Orientación en Derechos Humanos', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(40, 'Tecnicatura Superior en Realización Audiovisual', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(41, 'Tecnicatura Superior en Recursos Humanos', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(42, 'Tecnicatura Superior en Redes y Ciberseguridad', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(43, 'Tecnicatura Superior en Relaciones del Trabajo', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(44, 'Tecnicatura Superior en Seguros', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(45, 'Tecnicatura Superior en Sistemas Embebidos e Internet de las Cosas', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(46, 'Tecnicatura Superior en Telecomunicaciones y Seguridad Electrónica', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(47, 'Tecnicatura Superior en Turismo', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(48, 'Técnico Superior en Administración Pública con Orientación Municipal', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(49, 'Técnico Superior en Higiene y Seguridad en el Trabajo', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01'),
(50, 'Trayecto Facilitador Tecnológico Digital', 1, 0, '2026-02-16 23:46:01', '2026-02-16 23:46:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `institucion`
--

CREATE TABLE `institucion` (
  `id_institucion` int(11) NOT NULL,
  `nombre_ifts` varchar(255) NOT NULL,
  `direccion_ifts` varchar(255) DEFAULT NULL,
  `telefono_ifts` varchar(50) DEFAULT NULL,
  `email_ifts` varchar(100) DEFAULT NULL,
  `sitio_web_ifts` varchar(255) DEFAULT NULL,
  `observaciones_ifts` varchar(255) DEFAULT NULL,
  `latitud_ifts` decimal(10,8) NOT NULL,
  `longitud_ifts` decimal(11,8) NOT NULL,
  `logo_ifts` longtext DEFAULT NULL,
  `likes_ifts` int(11) NOT NULL DEFAULT 0,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `institucion`
--

INSERT INTO `institucion` (`id_institucion`, `nombre_ifts`, `direccion_ifts`, `telefono_ifts`, `email_ifts`, `sitio_web_ifts`, `observaciones_ifts`, `latitud_ifts`, `longitud_ifts`, `logo_ifts`, `likes_ifts`, `habilitado`, `cancelado`, `idCreate`, `idUpdate`) VALUES
(1, 'IFTS 12', 'Av. Belgrano 637, C1029 Cdad. Autónoma de Buenos Aires', '011 4345-6676', 'contacto@ifts12online.com.ar', 'https://ifts12online.com.ar/', 'Este es el mejor IFTS del condado, acá nació la Comunidad IFTS de la mano de Minotti, Díaz y Varela.', -34.61252630, -58.37540925, NULL, 0, 1, 0, '2026-02-17 01:08:33', '2026-02-17 01:08:33'),
(2, 'IFTS 20', 'Gurruchaga 739 Caba', '011 4776-0364', 'dfts_ifts20_de9@bue.edu.ar', 'https://www.instagram.com/ifts_20/?hl=es', 'Cursdadas de modalidad híbrida, una semana presencial y otra semana virtual.\r\nContactate para mas info', -34.59542079, -58.43867534, NULL, 0, 1, 0, '2026-02-17 01:08:33', '2026-02-17 01:08:33'),
(3, 'IFTS 15', 'Figueroa Alcorta 2977 CABA', '011 15-3898-1600', 'infoiftsn15@gmail.com', 'https://www.instagram.com/iftsn15?igsh=MWZrenltcm5yYnVwag==', 'Inscripciones abiertas 2026\nNo te quedes afuera', -34.58072709, -58.39772877, NULL, 0, 1, 0, '2026-02-17 01:08:33', '2026-02-17 01:08:33'),
(4, 'IFTS 12', 'Av. Belgrano 637, C1029 Cdad. Autónoma de Buenos Aires', '011 4345-6676', 'contacto@ifts12online.com.ar', 'https://ifts12online.com.ar/', 'Este es el mejor IFTS del condado, acá nació la Comunidad IFTS de la mano de Minotti, Díaz y Varela.', -34.61252630, -58.37540925, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(5, 'IFTS 20', 'Gurruchaga 739 Caba', '011 4776-0364', 'dfts_ifts20_de9@bue.edu.ar', 'https://www.instagram.com/ifts_20/?hl=es', 'Cursdadas de modalidad híbrida, una semana presencial y otra semana virtual.\r\nContactate para mas info', -34.59542079, -58.43867534, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(6, 'IFTS 15', 'Figueroa Alcorta 2977 CABA', '011 15-3898-1600', 'infoiftsn15@gmail.com', 'https://www.instagram.com/iftsn15?igsh=MWZrenltcm5yYnVwag==', 'Inscripciones abiertas 2026\nNo te quedes afuera', -34.58072709, -58.39772877, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(7, 'IFTS 18', 'Lucio Norberto Mansilla 3643, C1425BPW Cdad. Autónoma de Buenos Aires', '011 4823-2477', 'dfts_ifts18_de2@bue.edu.ar', 'https://www.ifts18.edu.ar/', '', -34.59078478, -58.41474898, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(8, 'IFTS 1', 'San Martín 665 4º piso – C.A.B.A.', '4318-7700  Bedelía-Administracion Int  141 ó 143', 'ifts1seg@hotmail.com', 'https://ifts1.com.ar/contactar/', '', -34.60020709, -58.37386759, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(9, 'IFTS 11', ' Zavaleta 204, C1437 Cdad. Autónoma de Buenos Aires', '011 4912 – 3792', 'ifts11@gmail.com', 'https://ifts11.edu.ar/', '', -34.63925808, -58.40305447, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(10, 'IFTS 4', 'Murguiondo 2105, Mataderos, CABA', '011 5418-7001', 'bedelesifts4@gmail.com', 'https://ifts4.edu.ar/index.php', '', -34.65835462, -58.49969773, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(11, 'IFTS 9', 'Rodríguez Peña 747, C1020ADO Cdad. Autónoma de Buenos Aires', '(011) 4811-9190', 'terciario9@gmail.com', 'https://www.ifts9.com.ar/', 'Formando profesionales desde 1983', -34.60011791, -58.39143686, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(12, 'IFTS 33', 'Humberto Primo 2260 Piso 1 | (C1229AAJ) | Ciudad de Buenos Aires', ' 4308-2257 ', 'ifts33.inscripcion@bue.edu.ar', 'https://fundacion.uocra.org/oferta-educativa-carreras-de-nivel-superior/ifts-n-33/', '', -34.62308940, -58.40244620, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(13, 'IFTS 2', 'Cañada de Gómez 3850', '4638-5656', 'bedelia.ifts2@gmail.com', 'sites.google.com/bue.edu.ar/ifts-2-de-20/página-principal', '', -34.67553040, -58.48749530, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(14, 'IFTS 3', 'Ángel J. Carranza 2045', '4772-8586 / 1659', 'dfts_ifts3_de9@bue.edu.ar', 'https://www.facebook.com/ifts.tres', '', -34.57976951, -58.43495429, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(15, 'IFTS 5', 'Ramsay 2250', '', 'bedelia.ifts5@gmail.com', 'https://ifts5.com', '', -34.55098958, -58.44100535, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(16, 'IFTS 6', 'Avenida Paseo Colón 650', '4331-5249', 'dfts_ifts6_de9@bue.edu.ar', 'http://www.ifts6.edu.ar/', '', -34.61524720, -58.36868489, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(17, 'IFTS 7', 'Avenida Gaona 1502', '4581-8804', 'dfts_ifts7_de7@bue.edu.ar', 'https://www.ifts7.com.ar/', '', -34.60973598, -58.45018923, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(18, 'IFTS 8', 'Avenida Rivadavia 1453, 1er. piso', '', 'dfts_ifts8_de1@bue.edu.ar', 'https://sites.google.com/a/bue.edu.ar/ifts8-instituto-de-formacion-tecnica-superior', '', -34.60879335, -58.38682204, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(19, 'IFTS 10 | IFTS 24', 'Avenida Entre Ríos 757', '4381-5271 / 4382-9141', 'dfts_ifts10_de3@bue.edu.ar / dfts_ifts24_de3@bue.edu.ar', 'https://www.ifts24.edu.ar/', '', -34.61752832, -58.39187533, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(20, 'IFTS 13', 'Avenida Juan Bautista Alberdi 163', '4901-6444 / 4902-0976', 'ifts.13@gmail.com / dfts_ifts13_de8@bue.edu.ar', 'https://ifts13.blog/', '', -34.62226076, -58.43018532, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(21, 'IFTS 14', 'Cochabamba 2830', '4941-0268', 'academia.ifst14@bue.edu.ar / bedelia.its14@bue.edu.ar', 'https://ifts14.com.ar/', '', -34.62579226, -58.40462387, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(22, 'IFTS 16', 'Teodoro García 3899', '4566-2084', 'bedeliaifts16@gmail.com / dfts_ifts16_de21@bue.edu.ar', 'https://ifts16.com/', '', -34.58380188, -58.45486164, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(23, 'IFTS 17', 'Viamonte 872, 3er. piso', '4323-8824', 'tecnicatura@agip.gob.ar / dfts_ifts17_de1@bue.edu.ar', 'https://www.ifts17.edu.ar/', '', -34.60022525, -58.37903023, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(24, 'IFTS 19', 'Catamarca 143', '11-2085-7745', 'dfts_ifts19_de6@bue.edu.ar', 'https://ifts19.edu.ar/', '', -34.61184639, -58.40726316, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(25, 'IFTS 21', 'Pje. Carlos Spegazzini 450', '4983-0331 / 0885', 'dfts_ifts21_de8@bue.edu.ar', 'http://ifts21.edu.ar/', '', -34.61871587, -58.42632294, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(26, 'IFTS 22', 'Avenida Santa Fe 3727', '4831-6970', 'dfts_ifts22_de3@bue.edu.ar', 'https://sites.google.com/bue.edu.ar/ifts22', '', -34.58483092, -58.41614664, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(27, 'IFTS 23', 'Salta 1065', '4304-6735', 'dfts_ifts23_de5@bue.edu.ar', 'https://ifts23.wordpress.com/', '', -34.62060532, -58.38306695, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(28, 'IFTS 26', 'Estados Unidos 3141', '4931-9843 / 4932-6210', 'dfts_ifts26_de6@bue.edu.ar', 'https://sites.google.com/bue.edu.ar/ifts26', '', -34.62120129, -58.40990245, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(29, 'IFTS 27', 'Avenida Asamblea 1221', '4923-0115', 'ifts27@gmail.com', 'https://www.ifts27.com/', '', -34.63582089, -58.44228745, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(30, 'IFTS 28', 'Bartolomé Mitre 3560', '4862-9041', 'dfts_ifts28_de3@bue.edu.ar', 'https://educacionute.org/', '', -34.60957704, -58.41669112, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(31, 'IFTS 29', 'Avenida Jujuy 255', '', 'dfts.ifts29@bue.edu.ar', 'https://ifts29.edu.ar', '', -34.61303402, -58.40561092, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(32, 'IFTS 30', 'Hortiguera 420', '4931- 2761 / 4931-6501', 'dfts_ifts30_de8@bue.edu.ar', 'https://sites.google.com/bue.edu.ar', '', -34.62753588, -58.44525933, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(33, 'IFTS 31', 'Perú 1065, 2do. piso', '7029-8954', 'dfts_ifts31_de2@bue.edu.ar', 'https://www.smata.com.ar/', '', -34.62053690, -58.37433100, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(34, 'IFTS 32', 'Humberto Primo 102', '3220-6627', 'dfts_ifts32_de4@bue.edu.ar', 'https://ifts32oscarsmith.com.ar/', '', -34.62064947, -58.36717486, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(35, 'IFTS 33', 'Humberto Primo 2260, 1re. piso', '4308-2257', 'ifts33.inscripcion@bue.edu.ar', 'https://fundacion.uocra.org/', '', -34.62192968, -58.39755893, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37'),
(36, 'IFTS 34', 'Tucumán 651', '4522-0973', 'dets.ifts34@bue.edu.ar', 'https://uthgra.org.ar/', '', -34.60100461, -58.37603152, NULL, 0, 1, 0, '2026-02-17 01:17:37', '2026-02-17 01:17:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `institucion_carrera`
--

CREATE TABLE `institucion_carrera` (
  `id_institucion_carrera` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `habiltado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `institucion_carrera`
--

INSERT INTO `institucion_carrera` (`id_institucion_carrera`, `id_institucion`, `id_carrera`, `habiltado`, `cancelado`, `idCreate`, `idUpdate`) VALUES
(1, 1, 1, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(2, 5, 1, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(3, 7, 1, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(4, 8, 1, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(5, 9, 1, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(6, 20, 1, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(7, 22, 1, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(8, 23, 1, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(9, 1, 2, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(10, 5, 2, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(11, 7, 2, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(12, 10, 2, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(13, 17, 2, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(14, 33, 2, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(15, 1, 3, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(16, 1, 4, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(17, 2, 5, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(18, 2, 6, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(19, 2, 7, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(20, 2, 8, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(21, 3, 9, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(22, 1, 10, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(23, 5, 12, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(24, 7, 12, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(25, 8, 12, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(26, 17, 12, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(27, 20, 12, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(28, 6, 13, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(29, 9, 14, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(30, 12, 14, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(31, 15, 14, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(32, 9, 15, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(33, 9, 16, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(34, 9, 17, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(35, 10, 17, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(36, 10, 18, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(37, 10, 19, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(38, 11, 20, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(39, 13, 21, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(40, 14, 22, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(41, 15, 23, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(42, 15, 24, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(43, 16, 25, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(44, 16, 26, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(45, 17, 27, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(46, 17, 28, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(47, 17, 29, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(48, 18, 30, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(49, 19, 31, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(50, 19, 32, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(51, 21, 33, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(52, 21, 34, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(53, 22, 35, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(54, 26, 35, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(55, 28, 35, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(56, 23, 36, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(57, 24, 37, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(58, 25, 38, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(59, 25, 39, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(60, 34, 39, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(61, 25, 40, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(62, 26, 41, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(63, 27, 42, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(64, 27, 43, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(65, 28, 44, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(66, 29, 45, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(67, 30, 46, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(68, 31, 47, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(69, 31, 48, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(70, 32, 49, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(71, 32, 50, 1, 0, '2026-02-17 01:19:46', '2026-02-17 01:19:46'),
(72, 34, 50, 1, 0, '2026-02-17 01:19:46', '2026-02-17 02:05:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona`
--

CREATE TABLE `persona` (
  `id_persona` int(11) NOT NULL,
  `apellido` varchar(250) NOT NULL,
  `nombre` varchar(250) NOT NULL,
  `edad` int(3) NOT NULL,
  `dni` varchar(9) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `telefono` varchar(11) NOT NULL,
  `habilitado` int(1) NOT NULL DEFAULT 1,
  `cancelado` int(1) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(250) NOT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`, `habilitado`, `cancelado`, `idCreate`, `idUpdate`) VALUES
(1, 'Administrador', 1, 0, '2026-01-25 20:29:40', '2026-01-25 20:29:40'),
(2, 'Alumno', 1, 0, '2026-01-25 20:29:40', '2026-01-25 20:29:40'),
(3, 'Administrador', 1, 0, '2026-01-25 20:31:00', '2026-01-25 20:31:00'),
(4, 'Alumno', 1, 0, '2026-01-25 20:31:00', '2026-01-25 20:31:00'),
(5, 'Administrador', 1, 0, '2026-01-25 20:31:55', '2026-01-25 20:31:55'),
(6, 'Alumno', 1, 0, '2026-01-25 20:31:55', '2026-01-25 20:31:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `email` varchar(250) NOT NULL,
  `clave` varchar(250) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `id_persona` int(11) NOT NULL,
  `id_institucion` int(11) NOT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrera`
--
ALTER TABLE `carrera`
  ADD PRIMARY KEY (`id_carrera`);

--
-- Indices de la tabla `institucion`
--
ALTER TABLE `institucion`
  ADD PRIMARY KEY (`id_institucion`);

--
-- Indices de la tabla `institucion_carrera`
--
ALTER TABLE `institucion_carrera`
  ADD PRIMARY KEY (`id_institucion_carrera`),
  ADD KEY `id_ifts` (`id_institucion`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `persona`
--
ALTER TABLE `persona`
  ADD PRIMARY KEY (`id_persona`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_id_rol` (`id_rol`),
  ADD KEY `fk_id_persona` (`id_persona`),
  ADD KEY `fk_id_ifts` (`id_institucion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrera`
--
ALTER TABLE `carrera`
  MODIFY `id_carrera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `institucion`
--
ALTER TABLE `institucion`
  MODIFY `id_institucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `institucion_carrera`
--
ALTER TABLE `institucion_carrera`
  MODIFY `id_institucion_carrera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `persona`
--
ALTER TABLE `persona`
  MODIFY `id_persona` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `institucion_carrera`
--
ALTER TABLE `institucion_carrera`
  ADD CONSTRAINT `institucion_carrera_ibfk_1` FOREIGN KEY (`id_institucion`) REFERENCES `institucion` (`id_institucion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `institucion_carrera_ibfk_2` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_ibfk_3` FOREIGN KEY (`id_institucion`) REFERENCES `institucion` (`id_institucion`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
