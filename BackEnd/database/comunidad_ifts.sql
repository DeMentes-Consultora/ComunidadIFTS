-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-01-2026 a las 00:32:25
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
(1, 'Tecnicatura Superior en Administración de Empresas', 1, 0, '2026-01-28 21:56:31', '2026-01-28 21:56:31'),
(2, 'Tecnicatura Superior en Administración Pública', 1, 0, '2026-01-28 21:56:31', '2026-01-28 21:56:31'),
(3, 'Tecnicatura Superior en Análisis de Sistemas', 1, 0, '2026-01-28 21:56:31', '2026-01-28 21:56:31'),
(4, 'Tecnicatura Superior en Ciencia de Datos e Inteligencia Artificial', 1, 0, '2026-01-28 21:56:31', '2026-01-28 21:56:31'),
(5, 'Tecnicatura Superior en Gestión de Políticas Culturales', 1, 0, '2026-01-28 21:56:31', '2026-01-28 21:56:31'),
(6, 'Tecnicatura Superior en Gestión Parlamentaria', 1, 0, '2026-01-28 21:56:31', '2026-01-28 21:56:31'),
(7, 'Tecnicatura Superior en Hoteleria', 1, 0, '2026-01-28 21:56:31', '2026-01-28 21:56:31'),
(8, 'Tecnicatura Superior en Realización Audiovisual', 1, 0, '2026-01-28 21:56:31', '2026-01-28 21:56:31'),
(9, 'Tecnicatura Superior en Recursos Humanos', 1, 0, '2026-01-28 21:56:31', '2026-01-28 21:56:31'),
(10, 'Tecnicatura Superior en Turismo', 1, 0, '2026-01-28 21:56:31', '2026-01-28 21:56:31');

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
(1, 'IFTS 12', 'Av. Belgrano 637, C1029 Cdad. Autónoma de Buenos Aires', '011 4345-6676', 'contacto@ifts12online.com.ar', 'https://ifts12online.com.ar/', 'Este es el mejor IFTS del condado, acá nació la Comunidad IFTS de la mano de Minotti, Díaz y Varela.', -34.61252630, -58.37540925, 'data:image/jpeg;base64,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', 0, 1, 0, '2026-01-28 22:06:49', '2026-01-28 22:06:49'),
(2, 'IFTS 20', 'Gurruchaga 739 Caba', '011 4776-0364', 'dfts_ifts20_de9@bue.edu.ar', 'https://www.instagram.com/ifts_20/?hl=es', 'Cursdadas de modalidad híbrida, una semana presencial y otra semana virtual.\r\nContactate para mas info', -34.59542079, -58.43867534, 'data:image/jpeg;base64,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', 0, 1, 0, '2026-01-28 22:06:49', '2026-01-28 22:06:49'),
(3, 'IFTS 15', 'Figueroa Alcorta 2977 CABA', '011 15-3898-1600', 'infoiftsn15@gmail.com', 'https://www.instagram.com/iftsn15?igsh=MWZrenltcm5yYnVwag==', 'Inscripciones abiertas 2026\nNo te quedes afuera', -34.58072709, -58.39772877, 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/7QCEUGhvdG9zaG9wIDMuMAA4QklNBAQAAAAAAGgcAigAYkZCTUQwYTAwMGFiNzAxMDAwMDI3MDMwMDAwOTMwNDAwMDBiYTA1MDAwMDhhMDcwMDAwYmUwODAwMDAwNjBiMDAwMDYwMGIwMDAwNjgwYzAwMDA5MjBkMDAwMDk1MTAwMDAwAP/bAIQABQYGCwgLCwsLCw0LCwsNDg4NDQ4ODw0ODg4NDxAQEBEREBAQEA8TEhMPEBETFBQTERMWFhYTFhUVFhkWGRYWEgEFBQUKBwoICQkICwgKCAsKCgkJCgoMCQoJCgkMDQsKCwsKCw0MCwsICwsMDAwNDQwMDQoLCg0MDQ0MExQTExOc/8IAEQgAlgCWAwEiAAIRAQMRAf/EAIIAAQABBQEAAAAAAAAAAAAAAAAHAgMEBQYBEAABAwEEBQcIBwkBAAAAAAABAAIRAwQSITEQMEFRYRMgIjJxgZEFFDNScqGxwSNAQmKC0fAVNENTYHOS4fHSEQEAAQMCBAYDAQEBAAAAAAABEQAhMUFRYXGB8BAwkaGxwSDR4fFAYP/aAAwDAQACAAMAAAABmUAAA88etfaxru1YGddo9F2kABDUyw0TKAAYtHtvEuajVZe485Gqp1trlN143ubqc3Ls5IzLICGplhomUADT7jUYV7G5vLs4eRVf3m6uUcDT0nLU+9Pd0PQW6tuN3ggIamWGiZQANRt9PhXuQq3vJ4t7vNxweV75ueZq0NFW267CzKfdwN3ggIamWGiZQAMPMW/dXx3a2NXlxDupGtXPIm7HrrdHteVbz8qyGbZAQ1MsNEymKZTjdGScj3CJPo5rmvHdUWdlg38bYYNFNW0YHmRa2DT5VPuc0G99VQ1MsNZVqZdfsNeR3q9psDquElSKyRI27rjTv6uS63Fu6K/sNJzmz6enNdNquW2WLmaTPwt/z28yrORDUyw1uMOZfPRZ9uhYvhhZoeevHlNYD1R7U8W7gIamWGvUygAAAAAAAAQ1MsNEyoaEyoaEyoaEyoaEyoaEyoaEyoaEyoaEyoaEyw0H/9oACAEBAAEFAvrUo2hoXnIQrtKnXVKt1CkXK9TYvOGq9TejSLFTq3tZUfcFNl1Vq8qZTlKo2iFUZKpvvDVO6dS0vRTAuRKLIUKzPTeg/VUM6zpdmrNT0WmnooHpVsHaqhnajceFZqk6LTUhZqyG++tnqupUtlEOTbQxNfC84cqjpRtMOslIBDp1NVVZeFN18WywyvpKSs9UkfSVlY7DCqOuiky4NXUpShWhcixy80C5JjUa0qnSu64tlGzheboWdqAjU1X3G2a3uqsHlaqV+0at39rVVZrYXMPlWq5WO0mu2q+6KVa+qtYsNSoQmVDL7TCq1Lo84chptPo/J/orHbDZ1Y7Ry7G/vlppcrTo2mpY1Z67KzbR1RLVWdeL6d5NpXVWZcVZpchUdTTTOm0+j8n+i8jaG/vluruosreUxVp+R6ZAtHVotvMc26dFqzqVSxVat9UxDdIY0JrA3RybZXm9PRChRphQg0D+j//aAAgBAwABPwHmCg8/YKcwtzBHbz2tkgDami7N0xHWqfJv6kqGEOcbzo2kxJTDPUJ/tvxB7P1KqMEXm5ZEeqfy51LAPduED8WCc2RyY6zQCPvEiXd+5PqBkMuh13MyettyTnNAa4Ux4nB3j4LrH+60yPvN/wCc6liHt3iR+HH4JzZu1A8NmM97c1UptcZD2icxjntjBXgz6LMfbP3jt7lF0x/KaZ7Xf95zXXSCNiGM3ReB61PaDvamcm0zeIjYW7e5MG1gLj67sAOPb2qo8Rdbjtc71j+WobWeYF6Z34/FObUc64TJ3TguSMT38YymNy5B3Rwi/kqlEsidvHnUOuztCY4Pe71mXh2t/wBLlDETgpcRQIxd0zjtVob0WuLbjiTIHx1U/Vv/2gAIAQIAAT8B5httIfxW+M/BU6zKnVeHdhnnveGAuOAGJVQ37pqNLi/0dnGGHrP/AN4BHlg5jG8my9JutbIa0bScOzJVmXcarAN1el0S32hnHiFQrGeTfi6Ja4ZVG7/aG3nWrpOo09jnSeIYL0eMKnULHG0OxZVJYfuBphh7N/aqNF9Umtyhp8p1QAD0B1c9+apsqOdUpPrukYjBsPYduXcVHJtiZNlqtAO24+MPB3u51q6LqD9jXQeAeInxhU6l0VKBpPqXS7qgYseSRtG9We0PpNuuo1Ddydh1dk45gIMdVBtIwfnSB9Ruw+3j7le5VpMR5zVZdG24yMfBp51SmKjS12IdgU+WFoqOuPbhTr/ZcPVfx7e0FVjXqMu3GuvZvY/Nu3PKe9VXYBtUhjchRp9J7+B4dg71Z6RJ5R4umIYz+W3/ANHb4c8icDiqtkotDnlkACTEjLgCqdWhSZyrAA04SBj37V50y9cxmYmOjeImJ3wvPaf0hDpFLrEfrFWe1srzcnCMxGfOtvoqvsO+CrU3UqTIxp1gwn7rx+a83YXX7vS3+7xjbmrlNptjXSxk0h0Rl8V5PqG/UY15q0mgXXHYd08+OGiBuzQAGQj6t//aAAgBAQAGPwL65kfqG8qXnu0bV+gpYe5cdbedmuHM4K+3NTq42NUacj4aM1CI2HVvPFHRPhonx0BMOreOKG6SPy0RojQTskDwTBx1fByxEj5qMR2rBZnQA3HHFTs+ZU7G6y67NTt37+1bR7wnPd1QtvyU+/8AJXW562RgVDwsPcs1j71DApOJ12K3LrFb1hqXO3AnwVVxA6AkeBWDAf8AJTyeN6MnblFxs96e+oLt3t+a6DBA4Eq8W3cY7VK4qEAMyrrs1hipWXMqey74K0ez8indG9ejbuV6IxhfjPwTmb1cczCf1BUs7xu0ByB4aN5KHYhCghTpqey74K0ez8iqn4fno/GfgpaJM9wRaaeJ44dqe7YYju0Qo0jsQwwQACA5mQx4LAAaJgTvjRNxvgNTl/R//9oACAEBAQE/If8AqQXWK1qeVd4futejnQHF/OO0u4Ku45KwCJ4EtcL0FYFp4kq9yCitwMnmXzXTnURzL30O/wBUtpbZvzqVFFtqGUlrfZtyqIYF7alRXq5+XYcQ8+4q6aTPPShDTuCXTwxqLUApn05PuutU79fLu3H7V1gvvFY8SoifLk38JiPL6NJFR8w9G1dYR7nl27D9qkbkvqjRQ1Anpjl/PCBHW7yptnUqBMB+pfmr+ce55bk/0/2rpIacGKlzFLdGkzVmyK7opVdl1aEBeCHwcalQRJj3KvNBHfv5du1LlC2BZPupULeh9DxpxsHZwpYSHaJe/mr+oeh+qy13XQcP2oSzLAaf2rJrl5+Zn8Ou9aSO8Wabler6rotoKL33Pqmzjvod8aziXXzitE0vC8lS7v3QszzUdog8lhrpk4smig2MJhuXvwr2lhfDVvFchoQZ3zV7okT9Jmoed6AkifdakF5qPVICsTDUkhlNeFJG4Zq8GAfFEADJN6RHO5imMABNsUVh5tKQxEqVw/o0pDx71u8DxIcwEXcHelZW3CZwHA3rtuOoaYTZ4lz3KZbrKGzOJwOK0xTsK4nc1n5lAB3fb90QWo+WjjkTCUUmXUNCgc1HETrUNYGn9oSDD4963eB8U8J23HU56AmJDVfihDRRMEOzWTtpYiRuK6X3is/MoFWFaRFk8e040hwF9+FBMM9adjIeKCQ3GhECMgBPOtSjYD48PgIT658LmXv/AC8EOb0AxakuQenilyD0pDZrCA5H/j//2gAMAwEBAgEDAQAAEPPPOuc/PPKPPP8AeTjzzyjzy2QXv3zyjzzn38tHzyjzz/NibXzyjzDi0uDPfaiiRgjbnXKOiwzww43z12jzzzzzzzzyjDDDDDDDDCD/2gAIAQMBAT8Q/C6HVI+a9u0n5oQlUBzqwEbXvfsiOZRhRSKn0CHS+WsJW/qFiehSwSJl52ebR6P5PeuWKu5hNCVrkkByewigt2wJXGMYvtSFo2cpaW1ZpJNEDtAkZ9fc/lxlzRRD3VLgJkZCDQ7VO3RZMvoFvSoV0gC+w9Ida1ab9peg9R+SIoVJ3tUSsr/YiNo0slbzRJyaJcob6VccgvTb7LpQJUJ3H6em+fzFLjCalNikgLLniGpUhvLBmTSjT4iySwy1LrVDFmSFs23NM61i3KIDjOPy7FvUwWUOJj1VDd8HWY3ibxiatSiucvUvtRgDdiOC8f385cz4S74xwpTdZ53/AOb/2gAIAQIBAT8Q8Vi6wGtPwyGYse6rpF5ihzC51/N3YKmwffy0uZY7qOOfBwtKXuZxNxK8CBLS9Sivf3Cd0orcDDvYjQFtS35Gayz0Sk4NDgYW53nMdYalX9C2siw5AtegY7ZpmQcxs4mpXhC8o3wOj8nM5L7kVwPerfnADIP+yoK2ZFOO/baOlqn8JmoL3CyTtdNalOsS7DH5FNAQ5/CZHRoIHFPw3iLJoMDbCJNiG4WzRtQ8+Ea3TFvTo3j/AA+0PzGEAORJHmUPNqfbubI9qCtTdaxHN3pK9YXsIHTndFDkDBIoTMQ4VtJq4SvMutETnH5sfYQkvT3argP2RdGNCyzWjc0TQztA3Wlcojk0jT4/JJs3pKRCDSLenhms3Wzz3o2AGwQe3/N//9oACAEBAQE/EP8AqJgA1WCrBPlz72K/xaPpPHvcokUBwjJ50AWgfv8AWWneQaH0d3p0pf6X9rvj7ouV7/Qt80tITXW+n540PI1z8ncnmEuasd/0MtAjtSe1NF2bQ18X60qic6FqsDzVrk8G9QE+pk8X6VOHddqfnFCZZwNh3J5Y1gPkfPsq0WIHjWHSmSb8d6VuJYBK70uy3+KhaNRZEiGkpLipZWbvpffrR2x6mf08u/Mwe9Mgu9MQUFUMdn9jqfAYr9H0Y9Kdo1D5uVIdtf6Pl3ZmT3FYEKWyipZvWgnLmePu9KmilLjk6Dq1iORWaiTve/Wj3zywd1YY7dHrUXZ+ZRzRGSn8tjvZKW/OgcgLicaEt7FEDMXauesgD6vFinYuX1DpwF4Dnj59nly43njt1oStZZY+RrSpwyHKOw+H6lD0W9MBCok7bc6KmBbHzLWdakdNebuu9DQtbAdeb+0Y693tgx5kg0Cw6uPH1rklfQfqSsOp2I+VGKXZ8ParuzHI+FKSuF9ApM3yyidvt84KCdkmnpb4pPe/vXI6OS34o+L+9DQNoI8kiQeSkqBi8WrM/wDDNQ9mtCbNt9Sn0br/AK0RXQhrdAhvDkeFy4K2rqhDj/QU1vwE4kAAHVNxvTIRRESi7wq0dwAw9W1ASgybpo0ZnD0KuyBMh20hChlfDV50KCAhxcloQkalEOUF6n4KvbKRQ9x0IyUEM4K85MtDNQPkGTExNwyVetJ8aMP6ulYgBEArdAteZktlNdj3rbFuToeArCh0TcKk68XRTkcoc8VLGzJOrNMvjKxsBmoT4rIi3AWrAYidfwVP01JwTf41gMWpk6AddEMu/M4c818eg0c4Sl3EvtmjwwPOux713AjfpWQk9TR60YPDsOKrc1n2Ng0ZYEr5OIIrdUHNvHSfF0ABESRHIjaKNXkEA2AQ51qQwqb7GJgT4AavnUcTZdHGkmzSnvFetAAgsFEwAbJJ70TABsEHtT8qbofnxv6nEPzQEAmySU3LW4D/AOP/AP/Z', 0, 1, 0, '2026-01-28 22:06:49', '2026-01-28 22:06:49');

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
(1, 1, 1, 1, 0, '2026-01-28 22:51:56', '2026-01-28 22:51:56'),
(2, 1, 2, 1, 0, '2026-01-28 22:51:56', '2026-01-28 22:51:56'),
(3, 1, 3, 1, 0, '2026-01-28 22:51:56', '2026-01-28 22:51:56'),
(4, 1, 4, 1, 0, '2026-01-28 22:51:56', '2026-01-28 22:51:56'),
(5, 1, 10, 1, 0, '2026-01-28 22:51:56', '2026-01-28 22:51:56'),
(6, 2, 5, 1, 0, '2026-01-28 22:51:56', '2026-01-28 22:51:56'),
(7, 2, 6, 1, 0, '2026-01-28 22:51:56', '2026-01-28 22:51:56'),
(8, 2, 7, 1, 0, '2026-01-28 22:51:56', '2026-01-28 22:51:56'),
(9, 2, 8, 1, 0, '2026-01-28 22:51:56', '2026-01-28 22:51:56'),
(10, 3, 9, 1, 0, '2026-01-28 22:51:56', '2026-01-28 22:51:56');

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
  MODIFY `id_carrera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `institucion`
--
ALTER TABLE `institucion`
  MODIFY `id_institucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `institucion_carrera`
--
ALTER TABLE `institucion_carrera`
  MODIFY `id_institucion_carrera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
