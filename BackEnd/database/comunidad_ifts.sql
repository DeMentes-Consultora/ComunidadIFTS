-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-04-2026 a las 17:23:55
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
CREATE DATABASE IF NOT EXISTS `comunidad_ifts` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
-- --------------------------------------------------------
USE `comunidad_ifts`;
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
(1, 'Tecnicatura Superior en Análisis de Sistemas', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(2, 'Tecnicatura Superior en Ciencia de Datos e Inteligencia Artificial', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(3, 'Tecnicatura Superior en Administración Pública', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(4, 'Tecnicatura Superior en Gestión Parlamentaria', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(5, 'Tecnicatura Superior en Recursos Humanos', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(6, 'Tecnicatura Superior en Hoteleria', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(7, 'Tecnicatura Superior en Turismo', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(8, 'Tecnicatura Superior en Administración de Empresas', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(9, 'Tecnicatura Superior en Realización Audiovisual', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(10, 'Tecnicatura Superior en Gestión de Políticas Culturales', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(12, 'Tecnicatura Superior en Desarrollo de Software', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(13, 'Tecnicatura Superior en Seguros', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(14, 'Tecnicatura Superior en Administración', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(15, 'Tecnicatura Superior en Comercialización', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(16, 'Tecnicatura Superior en Comercio Internacional', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(17, 'Tecnicatura Superior en Relaciones del Trabajo', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(18, 'Tecnicatura Superior en Construcción Sustentable', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(19, 'Tecnicatura Superior en Coordinación y Gestión de Procesos Constructivos', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(20, 'Tecnicatura Superior  en Emprendimientos Gastronómicos', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(21, 'Tecnicatura Superior  en Desarrollo de Software de Simuladores', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(22, 'Tecnicatura Superior  en Comercio Internacional y Aduanas', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(23, 'Tecnicatura Superior  en Guía de Turismo de la Ciudad de Buenos Aires', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(24, 'Tecnicatura Superior  en Administración de Servicios Salud', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(25, 'Tecnicatura Superior  en Administración Comercial', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(26, 'Tecnicatura Superior en Administración y Relaciones de Trabajo', 1, 0, '2026-03-04 23:31:25', '2026-03-11 13:26:15'),
(27, 'Tecnicatura Superior  en  Laboratorio de Análisis Clínicos', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(28, 'Tecnicatura Superior en Redes y Ciberseguridad', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(29, 'Tecnicatura Superior en Telecomunicaciones y Seguridad Electrónica', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(30, 'Tecnicatura Superior en Bibliotecología', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(31, 'Tecnicatura Superior en Eficiencia Energética', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(32, 'Tecnicatura Superior en Sistemas Embebidos e Internet de las Cosas', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(33, 'Tecnicatura Superior en Administración Financiera del Sector Público', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(34, 'Tecnicatura Superior en Administración Tributaria', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(35, 'Técnico Superior en Higiene y Seguridad en el Trabajo', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(36, 'Técnico Superior en Administración Pública con Orientación Municipal', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(37, 'Tecnicatura Superior en Gestión Ambiental', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(38, 'Tecnicatura Superior en Gastronomía', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(39, 'Tecnicatura Superior en Gestión Turística', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(40, 'Tecnicatura Superior en Administración Hotelera', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(41, 'Tecnicatura Superior en Gestión Integral del Riesgo', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(42, 'Tecnicatura Superior en Interpretación de Lengua de Señas Argentina, Sordos e Hipoacúsicos', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(43, 'Tecnicatura Superior en Diseño Gráfico y Multimedia', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(44, 'Tecnicatura Superior en Pedagogía y Educación Social con Orientación en Derechos Humanos', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(45, 'Tecnicatura Superior en Desarrollo de Software a Distancia', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(46, 'Trayecto Facilitador Tecnológico Digital', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(47, 'Tecnicatura Superior en Mecatrónica', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(48, 'Tecnicatura Superior en Automotores Híbridos y Eléctricos', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(49, 'Tecnicatura Superior en Distribución de la Energía Eléctrica', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(50, 'Tecnicatura Superior en Gestión de Energías Renovables', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25'),
(51, 'Tecnicatura Superior en Alta Cocina', 1, 0, '2026-03-04 23:31:25', '2026-03-04 23:31:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrera_materia`
--

CREATE TABLE `carrera_materia` (
  `id_carreraMateria` int(11) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `habiltado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrousel`
--

CREATE TABLE `carrousel` (
  `id_carrousel` int(11) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `orden_visual` int(11) NOT NULL DEFAULT 0,
  `foto_perfil_public_id` varchar(255) CHARACTER SET armscii8 COLLATE armscii8_general_ci DEFAULT NULL,
  `foto_perfil_url` varchar(512) CHARACTER SET armscii8 COLLATE armscii8_general_ci DEFAULT NULL,
  `habilitado` int(1) NOT NULL DEFAULT 1,
  `cancelado` int(1) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrousel`
--

INSERT INTO `carrousel` (`id_carrousel`, `titulo`, `descripcion`, `enlace`, `orden_visual`, `foto_perfil_public_id`, `foto_perfil_url`, `habilitado`, `cancelado`, `idCreate`, `idUpdate`) VALUES
(1, 'Bienvenido a Comunidad IFTS', 'Conecta con todos los Institutos Superiores de Tecnologia de Buenos Aires', '#', 1, 'ComunidadIFTS/carrusel/php86D5_sydagd', 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1776363189/ComunidadIFTS/carrusel/php86D5_sydagd.png', 1, 0, '2026-04-14 15:50:23', '2026-04-16 18:13:12'),
(2, 'IFTS y comunidad', 'Descubre carreras, instituciones y oportunidades para crecer profesionalmente', '#', 2, 'ComunidadIFTS/carrusel/php8792_bpidkr', 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1776363190/ComunidadIFTS/carrusel/php8792_bpidkr.png', 1, 0, '2026-04-14 15:50:23', '2026-04-16 18:13:12');

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
  `logo_cloudinary_public_id` varchar(200) DEFAULT NULL,
  `likes_ifts` int(11) NOT NULL DEFAULT 0,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `institucion`
--

INSERT INTO `institucion` (`id_institucion`, `nombre_ifts`, `direccion_ifts`, `telefono_ifts`, `email_ifts`, `sitio_web_ifts`, `observaciones_ifts`, `latitud_ifts`, `longitud_ifts`, `logo_ifts`, `logo_cloudinary_public_id`, `likes_ifts`, `habilitado`, `cancelado`, `idCreate`, `idUpdate`) VALUES
(1, 'IFTS 12', 'Av. Belgrano 637, C1029 Cdad. Autónoma de Buenos Aires', '011 4345-6676', 'ifts12@gmail.com', 'https://ifts12online.com.ar/', 'En este momento la sede estará ubicada en la calle Misiones 25, CABA', -34.61252630, -58.37540925, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766543/ComunidadIFTS/logoIFTS/logo_1_27cPK6_uu7crd.png', 'ComunidadIFTS/logoIFTS/logo_1_27cPK6_uu7crd', 4, 1, 0, '2026-01-13 10:51:15', '2026-03-06 06:09:04'),
(2, 'IFTS 20', 'Gurruchaga 739 Caba', '011 4776-0364', 'dfts_ifts20_de9@bue.edu.ar', 'https://www.instagram.com/ifts_20/?hl=es', 'Cursdadas de modalidad híbrida, una semana presencial y otra semana virtual.\r\nContactate para mas info', -34.59542079, -58.43867534, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766544/ComunidadIFTS/logoIFTS/logo_2_csAzTl_tof9ru.png', 'ComunidadIFTS/logoIFTS/logo_2_csAzTl_tof9ru', 1, 1, 0, '2026-01-13 11:12:02', '2026-03-06 06:09:04'),
(3, 'IFTS 15', 'Figueroa Alcorta 2977 CABA', '011 15-3898-1600', 'infoiftsn15@gmail.com', 'https://www.instagram.com/iftsn15?igsh=MWZrenltcm5yYnVwag==', 'Inscripciones abiertas 2026\r\nNo te quedes afuera', -34.58072709, -58.39772877, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766545/ComunidadIFTS/logoIFTS/logo_3_66Jzel_zo8kjm.png', 'ComunidadIFTS/logoIFTS/logo_3_66Jzel_zo8kjm', 0, 1, 0, '2026-01-13 11:27:37', '2026-04-14 13:41:59'),
(5, 'IFTS 18', 'Lucio Norberto Mansilla 3643, C1425BPW Cdad. Autónoma de Buenos Aires', '011 4823-2477', 'dfts_ifts18_de2@bue.edu.ar', 'https://www.ifts18.edu.ar/', NULL, -34.59078478, -58.41474898, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766545/ComunidadIFTS/logoIFTS/logo_5_DYnVqn_ego2vk.png', 'ComunidadIFTS/logoIFTS/logo_5_DYnVqn_ego2vk', 0, 1, 0, '2026-01-15 02:13:01', '2026-03-06 06:09:06'),
(6, 'IFTS 1', 'San Martín 665 4º piso – C.A.B.A.', '4318-7700  Bedelía-Administracion Int  141 ó 143', 'ifts1seg@hotmail.com', 'https://ifts1.com.ar/contactar/', NULL, -34.60020709, -58.37386759, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766546/ComunidadIFTS/logoIFTS/logo_6_iIUTmP_j5n3po.png', 'ComunidadIFTS/logoIFTS/logo_6_iIUTmP_j5n3po', 0, 1, 0, '2026-01-15 02:40:20', '2026-03-06 06:09:07'),
(7, 'IFTS 11', ' Zavaleta 204, C1437 Cdad. Autónoma de Buenos Aires', '011 4912 – 3792', 'ifts11@gmail.com', 'https://ifts11.edu.ar/', NULL, -34.63925808, -58.40305447, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766547/ComunidadIFTS/logoIFTS/logo_7_cViL7p_htvwtr.png', 'ComunidadIFTS/logoIFTS/logo_7_cViL7p_htvwtr', 1, 1, 0, '2026-01-15 06:08:09', '2026-03-06 06:09:07'),
(8, 'IFTS 4', 'Murguiondo 2105, Mataderos, CABA', '011 5418-7001', 'bedelesifts4@gmail.com', 'https://ifts4.edu.ar/index.php', NULL, -34.65835462, -58.49969773, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766548/ComunidadIFTS/logoIFTS/logo_8_Gw2a80_dgtkls.png', 'ComunidadIFTS/logoIFTS/logo_8_Gw2a80_dgtkls', 0, 1, 0, '2026-01-15 06:15:06', '2026-03-06 06:09:08'),
(9, 'IFTS 9', 'Rodríguez Peña 747, C1020ADO Cdad. Autónoma de Buenos Aires', '(011) 4811-9190', 'terciario9@gmail.com', 'https://www.ifts9.com.ar/', 'Formando profesionales desde 1983', -34.60011791, -58.39143686, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766548/ComunidadIFTS/logoIFTS/logo_9_79fZLr_clmck3.png', 'ComunidadIFTS/logoIFTS/logo_9_79fZLr_clmck3', 0, 1, 0, '2026-01-15 06:23:15', '2026-03-06 06:09:09'),
(10, 'IFTS 33', 'Humberto Primo 2260 Piso 1 | (C1229AAJ) | Ciudad de Buenos Aires', ' 4308-2257 ', 'ifts33.inscripcion@bue.edu.ar', 'https://fundacion.uocra.org/oferta-educativa-carreras-de-nivel-superior/ifts-n-33/', NULL, -34.62308940, -58.40244620, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766549/ComunidadIFTS/logoIFTS/logo_10_K8SxUr_n5pnhm.png', 'ComunidadIFTS/logoIFTS/logo_10_K8SxUr_n5pnhm', 1, 1, 0, '2026-01-15 06:48:13', '2026-03-06 06:09:10'),
(11, 'IFTS 2', 'Cañada de Gómez 3850', '4638-5656', 'bedelia.ifts2@gmail.com', 'https://sites.google.com/bue.edu.ar/ifts-2-de-20/página-principal', NULL, -34.67553040, -58.48749530, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766550/ComunidadIFTS/logoIFTS/logo_11_jtSyds_kab95o.png', 'ComunidadIFTS/logoIFTS/logo_11_jtSyds_kab95o', 0, 1, 0, '2026-02-04 15:11:13', '2026-03-06 06:09:10'),
(12, 'IFTS 3', 'Ángel J. Carranza 2045', '4772-8586 / 1659', 'dfts_ifts3_de9@bue.edu.ar', 'https://www.facebook.com/ifts.tres', NULL, -34.57976951, -58.43495429, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766551/ComunidadIFTS/logoIFTS/logo_12_ka1T5b_ngdl3s.png', 'ComunidadIFTS/logoIFTS/logo_12_ka1T5b_ngdl3s', 0, 1, 0, '2026-02-04 15:15:52', '2026-03-06 06:09:11'),
(13, 'IFTS 5', 'Ramsay 2250', '000', 'bedelia.ifts5@gmail.com', 'https://ifts5.com', NULL, -34.55098958, -58.44100535, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766551/ComunidadIFTS/logoIFTS/logo_13_RuDV0e_o9x4nh.png', 'ComunidadIFTS/logoIFTS/logo_13_RuDV0e_o9x4nh', 0, 1, 0, '2026-02-04 15:21:24', '2026-03-06 06:09:11'),
(14, 'IFTS 6', 'Avenida Paseo Colón 650', '4331-5249', 'dfts_ifts6_de9@bue.edu.ar', 'http://www.ifts6.edu.ar/', NULL, -34.61524720, -58.36868489, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766552/ComunidadIFTS/logoIFTS/logo_14_7nZiFN_hdkvo0.png', 'ComunidadIFTS/logoIFTS/logo_14_7nZiFN_hdkvo0', 0, 1, 0, '2026-02-04 15:23:59', '2026-03-06 06:09:12'),
(15, 'IFTS 7', 'Avenida Gaona 1502', '4581-8804', 'dfts_ifts7_de7@bue.edu.ar', 'https://www.ifts7.com.ar/', NULL, -34.60973598, -58.45018923, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766552/ComunidadIFTS/logoIFTS/logo_15_cO8PAD_p2uxwf.png', 'ComunidadIFTS/logoIFTS/logo_15_cO8PAD_p2uxwf', 0, 1, 0, '2026-02-04 15:28:11', '2026-03-06 06:09:13'),
(16, 'IFTS 8', 'Avenida Rivadavia 1453, 1er. piso', '000', 'dfts_ifts8_de1@bue.edu.ar', 'https://sites.google.com/a/bue.edu.ar/ifts8-instituto-de-formacion-tecnica-superior', NULL, -34.60879335, -58.38682204, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766553/ComunidadIFTS/logoIFTS/logo_16_NJPtdf_pjurs4.png', 'ComunidadIFTS/logoIFTS/logo_16_NJPtdf_pjurs4', 0, 1, 0, '2026-02-04 15:32:48', '2026-03-06 06:09:13'),
(17, 'IFTS 10 | IFTS 24', 'Avenida Entre Ríos 757', '4381-5271 / 4382-9141', 'dfts_ifts10_de3@bue.edu.ar', 'https://www.ifts24.edu.ar/', NULL, -34.61752832, -58.39187533, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766554/ComunidadIFTS/logoIFTS/logo_17_RWqGDb_mppak8.png', 'ComunidadIFTS/logoIFTS/logo_17_RWqGDb_mppak8', 0, 1, 0, '2026-02-04 15:44:41', '2026-03-06 06:09:14'),
(18, 'IFTS 13', 'Avenida Juan Bautista Alberdi 163', '4901-6444 / 4902-0976', 'ifts.13@gmail.com', 'https://ifts13.blog/', NULL, -34.62226076, -58.43018532, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766554/ComunidadIFTS/logoIFTS/logo_18_R2qhwj_wt2vve.png', 'ComunidadIFTS/logoIFTS/logo_18_R2qhwj_wt2vve', 0, 1, 0, '2026-02-04 15:49:21', '2026-03-06 06:09:15'),
(19, 'IFTS 14', 'Cochabamba 2830', '4941-0268', 'bedelia.its14@bue.edu.ar', 'https://ifts14.com.ar/', NULL, -34.62579226, -58.40462387, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766555/ComunidadIFTS/logoIFTS/logo_19_z8gZhK_qzo9si.png', 'ComunidadIFTS/logoIFTS/logo_19_z8gZhK_qzo9si', 0, 1, 0, '2026-02-04 15:53:34', '2026-03-06 06:09:15'),
(20, 'IFTS 16', 'Teodoro García 3899', '4566-2084', 'bedeliaifts16@gmail.com', 'https://ifts16.com/', NULL, -34.58380188, -58.45486164, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766556/ComunidadIFTS/logoIFTS/logo_20_RspuUr_efklci.png', 'ComunidadIFTS/logoIFTS/logo_20_RspuUr_efklci', 0, 1, 0, '2026-02-04 15:57:45', '2026-03-06 06:09:16'),
(21, 'IFTS 17', 'Viamonte 872, 3er. piso', '4323-8824', 'tecnicatura@agip.gob.ar', 'https://www.ifts17.edu.ar/', NULL, -34.60022525, -58.37903023, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766556/ComunidadIFTS/logoIFTS/logo_21_XjPMXE_hzgpjm.png', 'ComunidadIFTS/logoIFTS/logo_21_XjPMXE_hzgpjm', 0, 1, 0, '2026-02-04 16:01:30', '2026-03-06 06:09:17'),
(22, 'IFTS 19', 'Catamarca 143', '11-2085-7745', 'dfts_ifts19_de6@bue.edu.ar', 'https://ifts19.edu.ar/', NULL, -34.61184639, -58.40726316, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766730/ComunidadIFTS/logoIFTS/logo_22_j7eEid_ay5gn6.png', 'ComunidadIFTS/logoIFTS/logo_22_j7eEid_ay5gn6', 0, 1, 0, '2026-02-05 09:56:08', '2026-03-06 06:12:11'),
(23, 'IFTS 21', 'Pje. Carlos Spegazzini 450', '4983-0331 / 0885', 'dfts_ifts21_de8@bue.edu.ar', 'http://ifts21.edu.ar/', NULL, -34.61871587, -58.42632294, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766731/ComunidadIFTS/logoIFTS/logo_23_HwWmP3_qocaec.png', 'ComunidadIFTS/logoIFTS/logo_23_HwWmP3_qocaec', 0, 1, 0, '2026-02-05 10:18:47', '2026-03-06 06:12:12'),
(24, 'IFTS 22', 'Avenida Santa Fe 3727', '4831-6970', 'dfts_ifts22_de3@bue.edu.ar', 'https://sites.google.com/bue.edu.ar/ifts22', NULL, -34.58483092, -58.41614664, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766732/ComunidadIFTS/logoIFTS/logo_24_bWjEx3_eqwz4x.png', 'ComunidadIFTS/logoIFTS/logo_24_bWjEx3_eqwz4x', 0, 1, 0, '2026-02-05 10:27:58', '2026-03-06 06:12:12'),
(25, 'IFTS 23', 'Salta 1065', '4304-6735', 'dfts_ifts23_de5@bue.edu.ar', 'https://ifts23.wordpress.com/', NULL, -34.62060532, -58.38306695, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766732/ComunidadIFTS/logoIFTS/logo_25_fJIRd1_fzej8n.png', 'ComunidadIFTS/logoIFTS/logo_25_fJIRd1_fzej8n', 0, 1, 0, '2026-02-05 10:38:53', '2026-03-06 06:12:13'),
(26, 'IFTS 26', 'Estados Unidos 3141', '4931-9843 / 4932-6210', 'dfts_ifts26_de6@bue.edu.ar', 'https://sites.google.com/bue.edu.ar/ifts26', NULL, -34.62120129, -58.40990245, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766733/ComunidadIFTS/logoIFTS/logo_26_YMFjFL_wawyfo.png', 'ComunidadIFTS/logoIFTS/logo_26_YMFjFL_wawyfo', 0, 1, 0, '2026-02-05 11:02:31', '2026-03-06 06:12:14'),
(27, 'IFTS 27', 'Avenida Asamblea 1221', '4923-0115', 'ifts27@gmail.com', 'https://www.ifts27.com/', NULL, -34.63582089, -58.44228745, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766734/ComunidadIFTS/logoIFTS/logo_27_BzuhFB_odl6cs.png', 'ComunidadIFTS/logoIFTS/logo_27_BzuhFB_odl6cs', 0, 1, 0, '2026-02-05 11:10:30', '2026-03-06 06:12:14'),
(28, 'IFTS 28', 'Bartolomé Mitre 3560', '4862-9041', 'dfts_ifts28_de3@bue.edu.ar', 'https://educacionute.org/', NULL, -34.60957704, -58.41669112, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766735/ComunidadIFTS/logoIFTS/logo_28_xHovQc_kmitsx.png', 'ComunidadIFTS/logoIFTS/logo_28_xHovQc_kmitsx', 0, 1, 0, '2026-02-05 11:19:56', '2026-03-06 06:12:15'),
(29, 'IFTS 29', 'Avenida Jujuy 255', '000', 'dfts.ifts29@bue.edu.ar', 'https://ifts29.edu.ar', NULL, -34.61303402, -58.40561092, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766736/ComunidadIFTS/logoIFTS/logo_29_OiEdyV_rx1vpg.png', 'ComunidadIFTS/logoIFTS/logo_29_OiEdyV_rx1vpg', 0, 1, 0, '2026-02-05 11:28:55', '2026-03-06 06:12:16'),
(30, 'IFTS 30', 'Hortiguera 420', '4931- 2761 / 4931-6501', 'dfts_ifts30_de8@bue.edu.ar', 'https://sites.google.com/bue.edu.ar', NULL, -34.62753588, -58.44525933, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766737/ComunidadIFTS/logoIFTS/logo_30_OzH9FK_ajnnoj.png', 'ComunidadIFTS/logoIFTS/logo_30_OzH9FK_ajnnoj', 0, 1, 0, '2026-02-05 11:45:28', '2026-03-06 06:12:17'),
(31, 'IFTS 31', 'Perú 1065, 2do. piso', '7029-8954', 'dfts_ifts31_de2@bue.edu.ar', 'https://www.smata.com.ar/', NULL, -34.62053690, -58.37433100, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766737/ComunidadIFTS/logoIFTS/logo_31_HNlGC8_b1xevy.png', 'ComunidadIFTS/logoIFTS/logo_31_HNlGC8_b1xevy', 0, 1, 0, '2026-02-05 11:59:10', '2026-03-06 06:12:17'),
(32, 'IFTS 32', 'Humberto Primo 102', '3220-6627', 'dfts_ifts32_de4@bue.edu.ar', 'https://ifts32oscarsmith.com.ar/', NULL, -34.62064947, -58.36717486, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766738/ComunidadIFTS/logoIFTS/logo_32_UnfHk2_epwujy.png', 'ComunidadIFTS/logoIFTS/logo_32_UnfHk2_epwujy', 0, 1, 0, '2026-02-05 12:10:18', '2026-03-06 06:12:18'),
(33, 'IFTS 33', 'Humberto Primo 2260, 1re. piso', '4308-2257', 'ifts33.inscripcion@bue.edu.ar', 'https://fundacion.uocra.org/', NULL, -34.62192968, -58.39755893, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766738/ComunidadIFTS/logoIFTS/logo_33_VWwyqP_detnav.png', 'ComunidadIFTS/logoIFTS/logo_33_VWwyqP_detnav', 0, 1, 0, '2026-02-05 12:23:32', '2026-03-06 06:12:19'),
(34, 'IFTS 34', 'Tucumán 651', '4522-0973', 'dets.ifts34@bue.edu.ar', 'https://uthgra.org.ar/', NULL, -34.60100461, -58.37603152, 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1772766739/ComunidadIFTS/logoIFTS/logo_34_Lxn0H6_vxqk1j.png', 'ComunidadIFTS/logoIFTS/logo_34_Lxn0H6_vxqk1j', 0, 1, 0, '2026-02-05 13:47:56', '2026-03-06 06:12:19');

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
(1, 1, 1, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(2, 5, 1, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(3, 7, 1, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(4, 8, 1, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(5, 9, 1, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(6, 20, 1, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(7, 22, 1, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(8, 23, 1, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(9, 1, 2, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(10, 5, 2, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(11, 7, 2, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(12, 10, 2, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(13, 17, 2, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(14, 33, 2, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(15, 1, 3, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(16, 1, 4, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(17, 2, 5, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(18, 2, 6, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(19, 2, 7, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(20, 2, 8, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(22, 1, 10, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(23, 5, 12, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(24, 7, 12, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(25, 8, 12, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(26, 17, 12, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(27, 20, 12, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(28, 6, 13, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(29, 9, 14, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(30, 12, 14, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(31, 15, 14, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(32, 9, 15, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(33, 9, 16, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(34, 9, 17, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(35, 10, 17, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(36, 10, 18, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(37, 10, 19, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(39, 13, 21, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(40, 14, 22, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(41, 15, 23, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(42, 15, 24, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(43, 16, 25, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(44, 16, 26, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(45, 17, 27, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(46, 17, 28, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(47, 17, 29, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(48, 18, 30, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(49, 19, 31, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(50, 19, 32, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(51, 21, 33, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(52, 21, 34, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(53, 22, 35, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(54, 26, 35, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(55, 28, 35, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(56, 23, 36, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(57, 24, 37, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(58, 25, 38, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(59, 25, 39, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(60, 34, 39, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(61, 25, 40, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(62, 26, 41, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(63, 27, 42, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(64, 27, 43, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(65, 28, 44, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(66, 29, 45, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(67, 30, 46, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(68, 31, 47, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(69, 31, 48, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(70, 32, 49, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(71, 32, 50, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(72, 34, 51, 1, 0, '2026-03-04 23:22:52', '2026-03-04 23:22:52'),
(73, 11, 20, 1, 0, '2026-03-06 00:46:33', '2026-03-06 00:46:33'),
(76, 3, 9, 1, 0, '2026-04-14 13:43:06', '2026-04-14 13:43:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia`
--

CREATE TABLE `materia` (
  `id_materia` int(11) NOT NULL,
  `nombre_materia` varchar(250) NOT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `navbar`
--

CREATE TABLE `navbar` (
  `id_navbar` int(11) NOT NULL,
  `brand_text` varchar(255) DEFAULT NULL,
  `foto_perfil_public_id` varchar(255) CHARACTER SET armscii8 COLLATE armscii8_general_ci DEFAULT NULL,
  `foto_perfil_url` varchar(512) CHARACTER SET armscii8 COLLATE armscii8_general_ci DEFAULT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `navbar`
--

INSERT INTO `navbar` (`id_navbar`, `brand_text`, `foto_perfil_public_id`, `foto_perfil_url`, `habilitado`, `cancelado`, `idCreate`, `idUpdate`) VALUES
(1, '', 'ComunidadIFTS/navbar/php2585_mdlpuq', 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1776430687/ComunidadIFTS/navbar/php2585_mdlpuq.png', 1, 0, '2026-04-14 15:50:23', '2026-04-17 12:58:09');

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
  `foto_perfil_url` varchar(512) DEFAULT NULL,
  `foto_perfil_public_id` varchar(255) DEFAULT NULL,
  `habilitado` int(1) NOT NULL DEFAULT 1,
  `cancelado` int(1) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`id_persona`, `apellido`, `nombre`, `edad`, `dni`, `fecha_nacimiento`, `telefono`, `foto_perfil_url`, `foto_perfil_public_id`, `habilitado`, `cancelado`, `idCreate`, `idUpdate`) VALUES
(1, 'Minotti', 'Sebastian', 47, '26582365', '1978-10-16', '1158965236', NULL, NULL, 1, 0, '2026-02-25 23:53:22', '2026-02-25 23:53:22'),
(2, 'B', 'A', 26, '12345678', '2000-01-01', '1111111111', NULL, NULL, 1, 0, '2026-02-25 23:54:32', '2026-02-25 23:54:32'),
(3, 'Prueba', 'Ana', 26, '12345679', '2000-01-01', '1111111111', NULL, NULL, 1, 0, '2026-02-25 23:58:22', '2026-02-25 23:58:22'),
(4, 'Prueba', 'Ana', 26, '12345671', '2000-01-01', '1111111111', NULL, NULL, 1, 0, '2026-02-25 23:59:25', '2026-02-25 23:59:25'),
(5, 'Prueba', 'Ana', 26, '12345672', '2000-01-01', '1111111111', NULL, NULL, 1, 0, '2026-02-26 00:00:07', '2026-02-26 00:00:07'),
(6, 'Prueba', 'Ana', 26, '44290499', '2000-01-01', '1111111111', NULL, NULL, 1, 0, '2026-02-26 00:02:44', '2026-02-26 00:02:44'),
(7, 'prueba', 'prueba', 47, '26523654', '1978-05-16', '1156523654', NULL, NULL, 1, 0, '2026-02-26 00:19:47', '2026-02-26 00:19:47'),
(8, 'Nuevo', 'Test', 25, '64533288', '2000-05-10', '1112345678', NULL, NULL, 1, 0, '2026-02-26 00:22:28', '2026-02-26 00:22:28'),
(9, 'Test', 'Form', 25, '58800111', '2000-05-10', '1112345678', NULL, NULL, 1, 0, '2026-02-26 00:23:36', '2026-02-26 00:23:36'),
(10, 'nuevo', 'nuevo', 26, '23654256', '2000-02-02', '1152365478', NULL, NULL, 1, 0, '2026-02-26 00:26:05', '2026-02-26 00:26:05'),
(11, 'nuevo', 'otroNuevo', 47, '12532569', '1978-10-16', '115236987', NULL, NULL, 1, 0, '2026-02-26 02:50:46', '2026-02-26 02:50:46'),
(12, 'Pendiente', 'Mail', 25, '49254786', '2000-05-10', '1112345678', NULL, NULL, 1, 0, '2026-02-26 02:55:08', '2026-02-26 02:55:08'),
(13, 'registro', 'registro', 47, '12523654', '1978-10-16', '1123654789', NULL, NULL, 1, 0, '2026-02-26 03:00:33', '2026-02-26 03:00:33'),
(14, 'consultora', 'dementes', 47, '12365298', '1978-10-16', '1152365489', NULL, NULL, 1, 0, '2026-03-13 12:50:34', '2026-03-13 12:50:34'),
(15, 'muchachos deL INAP IFTS12', 'Los', 69, '12365236', '1956-10-16', '1125365236', NULL, NULL, 1, 0, '2026-03-13 14:33:55', '2026-03-13 14:33:55'),
(16, 'muchachos deL INAP IFTS12', 'Los', 69, '26469523', '1956-10-16', '1125365475', NULL, NULL, 1, 0, '2026-03-13 15:17:03', '2026-03-13 15:17:03'),
(17, 'Minotti', 'Sebastian', 47, '26589632', '1978-10-16', '1152365478', NULL, NULL, 1, 0, '2026-03-13 15:22:26', '2026-03-13 15:22:26'),
(18, 'Minotti', 'Sebastian', 25, '26587056', '2000-10-16', '1165896325', 'https://res.cloudinary.com/dm8ds67tb/image/upload/v1773422959/ComunidadIFTS/perfiles/bis1jmnpk4ness2sqrbk.jpg', 'ComunidadIFTS/perfiles/bis1jmnpk4ness2sqrbk', 1, 0, '2026-03-13 17:29:00', '2026-03-13 17:29:18'),
(19, 'Minotti', 'Sebastian', 25, '12589632', '2000-10-16', '1164589654', 'https://res.cloudinary.com/dm8ds67tb/image/upload/ComunidadIFTS/perfiles/bupiboaj6qlorvxo9xes.jpg', 'ComunidadIFTS/perfiles/bupiboaj6qlorvxo9xes', 1, 0, '2026-03-13 17:49:31', '2026-03-13 17:49:33'),
(20, 'Minotti', 'Sebastian', 40, '12582365', '1985-10-16', '1165873428', 'https://res.cloudinary.com/dm8ds67tb/image/upload/ComunidadIFTS/perfiles/wu8eizz1kj3cph63312j.jpg', 'ComunidadIFTS/perfiles/wu8eizz1kj3cph63312j', 1, 0, '2026-03-13 18:18:32', '2026-03-13 18:18:35'),
(21, 'Minotti', 'Sebastian', 25, '25365478', '2000-10-16', '1165236547', 'https://res.cloudinary.com/dm8ds67tb/image/upload/ComunidadIFTS/perfiles/xv4ibkunzn98x12dtmrw.jpg', 'ComunidadIFTS/perfiles/xv4ibkunzn98x12dtmrw', 1, 0, '2026-03-13 18:30:04', '2026-03-13 18:30:06');

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
(1, 'Administrador', 1, 0, '2026-02-25 23:16:00', '2026-02-25 23:16:00'),
(2, 'Alumno', 1, 0, '2026-02-25 23:16:00', '2026-02-25 23:16:00'),
(3, 'AdministradorIFTS', 1, 0, '2026-02-25 23:16:00', '2026-02-25 23:16:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sidebar`
--

CREATE TABLE `sidebar` (
  `id_sidebar` int(11) NOT NULL,
  `brand_text` varchar(255) DEFAULT NULL,
  `foto_perfil_public_id` varchar(255) DEFAULT NULL,
  `foto_perfil_url` varchar(512) DEFAULT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sidebar`
--

INSERT INTO `sidebar` (`id_sidebar`, `brand_text`, `foto_perfil_public_id`, `foto_perfil_url`, `habilitado`, `cancelado`, `idCreate`, `idUpdate`) VALUES
(1, 'Comunidad IFTS', NULL, NULL, 1, 0, '2026-04-17 15:22:41', '2026-04-17 15:22:41');

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
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `email`, `clave`, `id_rol`, `id_persona`, `id_institucion`, `habilitado`, `cancelado`, `idCreate`, `idUpdate`) VALUES
(1, 'seba@gmail.com', '$2y$10$Tb.mgWSOKMVKkQM4hpt.kuqZwNRy/wYgY4WIJzPbUCuNDCDGJTnTC', 1, 1, 1, 1, 0, '2026-02-25 23:53:22', '2026-02-26 03:03:10'),
(2, 'test507238115@mail.com', '$2y$10$ZbO25iUGI7.4wfZOjKbv5Osag0DGvr9t4rRw9oy.X1CTHpP6fMKGC', 2, 2, 1, 1, 0, '2026-02-25 23:54:32', '2026-02-26 00:16:11'),
(3, 'test1864180326@mail.com', '$2y$10$f/nEWTe0RvjsEH7MQaN3ZesPM88yGpcqtrS36OyUtKbQUhUxVH1km', 2, 3, 1, 1, 0, '2026-02-25 23:58:22', '2026-02-26 00:16:04'),
(4, 'test1364295098@mail.com', '$2y$10$NpGAF9WJHAUqd/SSO/EMZuOa9dIVUBCqqbHQXTZoykgQSDFWXDgKi', 2, 4, 1, 1, 0, '2026-02-25 23:59:25', '2026-02-26 00:15:56'),
(5, 'test1181805928@mail.com', '$2y$10$GFCawr7umar8fmW1pjWoueTBnM8SXwusQ.7E6S1IHdy1uol0hBnri', 2, 5, 1, 1, 0, '2026-02-26 00:00:07', '2026-02-26 00:15:50'),
(6, 'test1508581466@mail.com', '$2y$10$GapybKH7bnfAFI2fWaBoEuKvxBkNQmgCSwwqVY7nSEfmAudZQ9D6u', 2, 6, 1, 1, 0, '2026-02-26 00:02:44', '2026-02-26 00:15:45'),
(7, 'prueba@gmail.com', '$2y$10$sMLI7OocA0YLOByvaPS5bu49GTzPMaGaoAC4AU/QYr5q81RWS1W8i', 2, 7, 1, 1, 0, '2026-02-26 00:19:47', '2026-02-26 00:20:39'),
(8, 'nuevo1342950043@mail.com', '$2y$10$rex5SJ0czhrKkObKCDABBeiJJebihjYfSsh/HuSuxt31rKsrz/q8i', 2, 8, 1, 0, 1, '2026-02-26 00:22:28', '2026-02-26 00:24:44'),
(9, 'form650866515@mail.com', '$2y$10$wZe1dl0jYNX7DQFUDHYMNubd94Zw4FWQEmBxX4Cl9Zpjh/P83TdEi', 2, 9, 1, 1, 0, '2026-02-26 00:23:36', '2026-02-26 00:24:35'),
(10, 'nuevo@hotmail.com', '$2y$10$Eig3F9EaRK6LhLRGnBuOeO.9uZHyn2sMD7A5N418gIsfJtKKIOuHy', 2, 10, 1, 1, 0, '2026-02-26 00:26:05', '2026-02-26 00:26:36'),
(11, 'nminotti@gmail.com_', '$2y$10$OW/BzlwyUIUIwRyC39iOr.xCAwsiJ8/Vpmjb3imURM3eG874ZsPRm', 2, 11, 3, 1, 0, '2026-02-26 02:50:46', '2026-03-13 15:20:30'),
(12, 'confirmacion30020726@mail.com', '$2y$10$BW0.iaiOusJyyysqekJAned/q5NXL0yx7aepaeUVnk5dq4crfbG2m', 2, 12, 1, 0, 1, '2026-02-26 02:55:08', '2026-02-26 03:04:19'),
(13, 'sebaminotti@gmail.com', '$2y$10$yrb7XSIm1FISbp0eEzDc0OhY9OYm9YxpIvUJIU73YawHRmBVeVu6y', 2, 13, 3, 1, 0, '2026-02-26 03:00:33', '2026-03-13 15:20:11'),
(14, 'dementesconsultora2025@gmail.com', '$2y$10$O4hHfJSOH8sQ2t2SwOjnU.Pr5qi/.PdGhm7pNrMUN.L7iu06/eQa2', 1, 14, 1, 1, 0, '2026-03-13 12:50:34', '2026-03-13 14:06:38'),
(15, 'lesmuchachosdelinapifts@gmail.com', '$2y$10$j5pc/WJyzfxQgBJSOz4RqeBB1MiYSxZCgK4VqgsJsbU3UxUh6fTG2', 2, 15, 1, 1, 0, '2026-03-13 14:33:55', '2026-03-13 15:15:10'),
(16, 'losmuchachosdelinapifts@gmail.com', '$2y$10$ZZysVBEqJbRNHCT9jZMvxeRBzHbqqbMuxi1dhmlkO.bow7k1mvNjm', 1, 16, 2, 1, 0, '2026-03-13 15:17:03', '2026-03-13 15:18:50'),
(17, 'sminotti@gmail.com', '$2y$10$L3mpNFklv4C2Z4I6cgf9e.xJcoLalV6F05bZ520upSD/v/JcXPSqG', 1, 17, 1, 1, 0, '2026-03-13 15:22:26', '2026-03-13 15:35:45'),
(18, 'seb@gmail.com', '$2y$10$UhkPI9fR0FG/SuP1rKVC5O.TCHOkjsmfFX9ZdfwH1o85cAl.Qrs8e', 1, 18, 6, 1, 0, '2026-03-13 17:29:18', '2026-03-13 17:48:31'),
(19, 'mino@gmail.com', '$2y$10$rFCJ2WVPayAWbi0JkJE6KudkElZRI.HVkbb/ai9LY9aXLMvniQode', 2, 19, 5, 1, 0, '2026-03-13 17:49:33', '2026-03-13 18:07:34'),
(20, 'sebas@gmail.com', '$2y$10$PguAEskHTJUTMqB2kh28v.DRU3.UYFrv4fJHHe6ONNYNq6Errz.AG', 2, 20, 5, 1, 0, '2026-03-13 18:18:35', '2026-03-13 18:29:07'),
(21, 'sebastianminotti@gmail.com', '$2y$10$r0Tf23UJ27Xt5rMMBEG/KOjwD.y95yDlgzdDvG.JtwnRAlXQ4gw1q', 2, 21, 3, 1, 0, '2026-03-13 18:30:06', '2026-03-13 18:39:03');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrera`
--
ALTER TABLE `carrera`
  ADD PRIMARY KEY (`id_carrera`);

--
-- Indices de la tabla `carrera_materia`
--
ALTER TABLE `carrera_materia`
  ADD PRIMARY KEY (`id_carreraMateria`),
  ADD KEY `id_carrera` (`id_carrera`,`id_materia`),
  ADD KEY `id_materia` (`id_materia`);

--
-- Indices de la tabla `carrousel`
--
ALTER TABLE `carrousel`
  ADD PRIMARY KEY (`id_carrousel`);

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
-- Indices de la tabla `materia`
--
ALTER TABLE `materia`
  ADD PRIMARY KEY (`id_materia`);

--
-- Indices de la tabla `navbar`
--
ALTER TABLE `navbar`
  ADD PRIMARY KEY (`id_navbar`);

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
-- Indices de la tabla `sidebar`
--
ALTER TABLE `sidebar`
  ADD PRIMARY KEY (`id_sidebar`);

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
  MODIFY `id_carrera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `carrera_materia`
--
ALTER TABLE `carrera_materia`
  MODIFY `id_carreraMateria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carrousel`
--
ALTER TABLE `carrousel`
  MODIFY `id_carrousel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `institucion`
--
ALTER TABLE `institucion`
  MODIFY `id_institucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `institucion_carrera`
--
ALTER TABLE `institucion_carrera`
  MODIFY `id_institucion_carrera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `materia`
--
ALTER TABLE `materia`
  MODIFY `id_materia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `navbar`
--
ALTER TABLE `navbar`
  MODIFY `id_navbar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `persona`
--
ALTER TABLE `persona`
  MODIFY `id_persona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sidebar`
--
ALTER TABLE `sidebar`
  MODIFY `id_sidebar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
