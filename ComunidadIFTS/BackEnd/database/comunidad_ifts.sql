-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-01-2026 a las 02:07:32
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
-- Estructura de tabla para la tabla `ifts`
--

CREATE TABLE `ifts` (
  `id_ifts` int(11) NOT NULL,
  `nomnbre_ifts` varchar(250) NOT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ifts`
--

INSERT INTO `ifts` (`id_ifts`, `nomnbre_ifts`, `habilitado`, `cancelado`, `idCreate`, `idUpdate`) VALUES
(1, 'IFTS12', 1, 0, '2026-01-25 20:25:12', '2026-01-25 20:25:12'),
(2, 'IFTS9', 1, 0, '2026-01-25 20:25:12', '2026-01-25 20:25:12'),
(3, 'IFTS29', 1, 0, '2026-01-25 20:25:12', '2026-01-25 20:25:12'),
(4, 'IFTS15', 1, 0, '2026-01-25 20:25:12', '2026-01-25 20:25:12'),
(5, 'ifts11', 1, 0, '2026-01-25 20:25:12', '2026-01-25 20:25:12'),
(6, 'IFTS12', 1, 0, '2026-01-25 20:28:18', '2026-01-25 20:28:18'),
(7, 'IFTS9', 1, 0, '2026-01-25 20:28:18', '2026-01-25 20:28:18'),
(8, 'IFTS29', 1, 0, '2026-01-25 20:28:18', '2026-01-25 20:28:18'),
(9, 'IFTS15', 1, 0, '2026-01-25 20:28:18', '2026-01-25 20:28:18'),
(10, 'IFTS11', 1, 0, '2026-01-25 20:28:18', '2026-01-25 20:28:18');

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
  `id_ifts` int(11) NOT NULL,
  `habilitado` int(11) NOT NULL DEFAULT 1,
  `cancelado` int(11) NOT NULL DEFAULT 0,
  `idCreate` timestamp NOT NULL DEFAULT current_timestamp(),
  `idUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ifts`
--
ALTER TABLE `ifts`
  ADD PRIMARY KEY (`id_ifts`);

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
  ADD KEY `fk_id_ifts` (`id_ifts`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ifts`
--
ALTER TABLE `ifts`
  MODIFY `id_ifts` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_ifts`) REFERENCES `ifts` (`id_ifts`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_ibfk_3` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
