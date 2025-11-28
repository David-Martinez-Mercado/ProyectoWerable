-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-11-2025 a las 08:57:40
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
-- Base de datos: `vital_monitor_private`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lecturas`
--

CREATE TABLE `lecturas` (
  `id` int(11) NOT NULL,
  `id_dispositivo` varchar(50) NOT NULL,
  `lectura_FC` decimal(5,2) DEFAULT NULL,
  `lectura_SpO2` decimal(5,2) DEFAULT NULL,
  `lectura_temperatura` decimal(5,2) DEFAULT NULL,
  `gps_lat` decimal(10,8) DEFAULT NULL,
  `gps_lon` decimal(11,8) DEFAULT NULL,
  `fecha_lectura` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `lecturas`
--

INSERT INTO `lecturas` (`id`, `id_dispositivo`, `lectura_FC`, `lectura_SpO2`, `lectura_temperatura`, `gps_lat`, `gps_lon`, `fecha_lectura`) VALUES
(1, 'ESP32-001', 75.00, 98.00, 36.50, 19.43260700, -99.13320800, '2025-11-25 23:38:39'),
(2, 'ESP32-001', 78.00, 97.00, 36.60, 19.43260700, -99.13320800, '2025-11-25 23:53:39'),
(3, 'ESP32-001', 82.00, 96.00, 36.70, 19.43260700, -99.13320800, '2025-11-26 00:08:39'),
(4, 'ESP32-001', 85.00, 95.00, 36.80, 19.43260700, -99.13320800, '2025-11-26 00:23:39'),
(5, 'ESP32-001', 81.00, 96.00, 37.20, 19.43258000, -99.13321000, '2025-11-28 07:06:39'),
(6, 'ESP32-001', 74.00, 98.00, 37.80, 19.43259000, -99.13326000, '2025-11-28 07:06:43'),
(7, 'ESP32-001', 76.00, 98.00, 37.70, 19.43257000, -99.13322000, '2025-11-28 07:06:49'),
(8, 'ESP32-001', 79.00, 98.00, 37.10, 19.43261000, -99.13318000, '2025-11-28 07:06:54'),
(9, 'ESP32-001', 67.00, 98.00, 36.50, 19.43257000, -99.13319000, '2025-11-28 07:06:58'),
(10, 'ESP32-001', 77.00, 98.00, 37.50, 19.43262000, -99.13320000, '2025-11-28 07:07:02'),
(11, 'ESP32-001', 83.00, 97.00, 36.50, 19.43265000, -99.13325000, '2025-11-28 07:07:08'),
(12, 'ESP32-001', 77.00, 97.00, 37.90, 19.43266000, -99.13318000, '2025-11-28 07:07:10'),
(13, 'ESP32-001', 68.00, 96.00, 36.90, 19.43264000, -99.13325000, '2025-11-28 07:07:16'),
(14, 'ESP32-001', 76.00, 97.00, 36.90, 19.43266000, -99.13325000, '2025-11-28 07:07:20'),
(15, 'ESP32-001', 80.00, 97.00, 37.60, 19.43258000, -99.13318000, '2025-11-28 07:07:23'),
(16, 'ESP32-001', 77.00, 96.00, 37.80, 19.43263000, -99.13318000, '2025-11-28 07:07:29'),
(17, 'ESP32-001', 69.00, 96.00, 36.90, 19.43264000, -99.13319000, '2025-11-28 07:07:35'),
(18, 'ESP32-001', 67.00, 97.00, 37.20, 19.43258000, -99.13324000, '2025-11-28 07:07:39'),
(19, 'ESP32-001', 72.00, 98.00, 37.60, 19.43261000, -99.13326000, '2025-11-28 07:07:43'),
(20, 'ESP32-001', 72.00, 96.00, 37.70, 19.43256000, -99.13316000, '2025-11-28 07:07:49'),
(21, 'ESP32-001', 72.00, 98.00, 37.20, 19.43259000, -99.13320000, '2025-11-28 07:07:54'),
(22, 'ESP32-001', 76.00, 96.00, 37.70, 19.43258000, -99.13325000, '2025-11-28 07:07:58'),
(23, 'ESP32-001', 80.00, 98.00, 37.40, 19.43259000, -99.13323000, '2025-11-28 07:08:04'),
(24, 'ESP32-001', 84.00, 98.00, 36.70, 19.43256000, -99.13326000, '2025-11-28 07:08:08'),
(25, 'ESP32-001', 78.00, 98.00, 37.30, 19.43260000, -99.13319000, '2025-11-28 07:08:11'),
(26, 'ESP32-001', 73.00, 96.00, 36.50, 19.43260000, -99.13324000, '2025-11-28 07:08:15'),
(27, 'ESP32-001', 77.00, 98.00, 37.80, 19.43263000, -99.13319000, '2025-11-28 07:08:18'),
(28, 'ESP32-001', 74.00, 96.00, 36.70, 19.43263000, -99.13322000, '2025-11-28 07:08:24'),
(29, 'ESP32-001', 71.00, 98.00, 37.10, 19.43261000, -99.13319000, '2025-11-28 07:08:28'),
(30, 'ESP32-001', 66.00, 98.00, 36.60, 19.43260000, -99.13324000, '2025-11-28 07:08:31'),
(31, 'ESP32-001', 70.00, 98.00, 36.70, 19.43264000, -99.13318000, '2025-11-28 07:08:35'),
(32, 'ESP32-001', 74.00, 97.00, 37.10, 19.43262000, -99.13322000, '2025-11-28 07:08:38'),
(33, 'ESP32-001', 77.00, 97.00, 37.90, 19.43258000, -99.13319000, '2025-11-28 07:08:42'),
(34, 'ESP32-001', 79.00, 98.00, 37.30, 19.43263000, -99.13322000, '2025-11-28 07:08:48'),
(35, 'ESP32-001', 79.00, 98.00, 36.60, 19.43259000, -99.13318000, '2025-11-28 07:08:52'),
(36, 'ESP32-001', 84.00, 98.00, 37.60, 19.43266000, -99.13320000, '2025-11-28 07:08:55'),
(37, 'ESP32-001', 67.00, 96.00, 36.50, 19.43261000, -99.13326000, '2025-11-28 07:09:01'),
(38, 'ESP32-001', 74.00, 98.00, 36.70, 19.43259000, -99.13320000, '2025-11-28 07:09:04'),
(39, 'ESP32-001', 68.00, 97.00, 36.70, 19.43259000, -99.13324000, '2025-11-28 07:09:08'),
(40, 'ESP32-001', 83.00, 98.00, 37.80, 19.43261000, -99.13319000, '2025-11-28 07:09:12'),
(41, 'ESP32-001', 72.00, 98.00, 36.80, 19.43261000, -99.13321000, '2025-11-28 07:09:18'),
(42, 'ESP32-001', 72.00, 97.00, 37.70, 19.43257000, -99.13319000, '2025-11-28 07:09:20'),
(43, 'ESP32-001', 70.00, 96.00, 36.60, 19.43259000, -99.13324000, '2025-11-28 07:09:26'),
(44, 'ESP32-001', 73.00, 98.00, 36.80, 19.43257000, -99.13322000, '2025-11-28 07:09:30'),
(45, 'ESP32-001', 67.00, 97.00, 37.80, 19.43260000, -99.13317000, '2025-11-28 07:09:33'),
(46, 'ESP32-001', 67.00, 96.00, 36.70, 19.43258000, -99.13324000, '2025-11-28 07:09:39'),
(47, 'ESP32-001', 81.00, 96.00, 37.90, 19.43262000, -99.13316000, '2025-11-28 07:09:43'),
(48, 'ESP32-001', 73.00, 96.00, 36.80, 19.43256000, -99.13316000, '2025-11-28 07:09:48'),
(49, 'ESP32-001', 77.00, 96.00, 37.30, 19.43258000, -99.13317000, '2025-11-28 07:09:52'),
(50, 'ESP32-001', 76.00, 96.00, 37.80, 19.43264000, -99.13326000, '2025-11-28 07:09:56'),
(51, 'ESP32-001', 81.00, 96.00, 36.60, 19.43262000, -99.13325000, '2025-11-28 07:10:01'),
(52, 'ESP32-001', 75.00, 97.00, 37.10, 19.43259000, -99.13319000, '2025-11-28 07:10:05'),
(53, 'ESP32-001', 72.00, 97.00, 36.90, 19.43258000, -99.13322000, '2025-11-28 07:10:09'),
(54, 'ESP32-001', 72.00, 97.00, 37.00, 19.43260000, -99.13322000, '2025-11-28 07:10:12'),
(55, 'ESP32-001', 77.00, 96.00, 37.70, 19.43262000, -99.13322000, '2025-11-28 07:10:16'),
(56, 'ESP32-001', 67.00, 98.00, 37.40, 19.43261000, -99.13322000, '2025-11-28 07:10:22'),
(57, 'ESP32-001', 65.00, 96.00, 37.50, 19.43260000, -99.13326000, '2025-11-28 07:10:25'),
(58, 'ESP32-001', 78.00, 96.00, 37.50, 19.43262000, -99.13318000, '2025-11-28 07:10:39'),
(59, 'ESP32-001', 77.00, 98.00, 37.90, 19.43256000, -99.13321000, '2025-11-28 07:10:43'),
(60, 'ESP32-001', 71.00, 98.00, 37.40, 19.43260000, -99.13319000, '2025-11-28 07:10:53'),
(61, 'ESP32-001', 76.00, 96.00, 37.60, 19.43264000, -99.13323000, '2025-11-28 07:10:57'),
(62, 'ESP32-001', 65.00, 97.00, 37.10, 19.43259000, -99.13322000, '2025-11-28 07:11:02'),
(63, 'ESP32-001', 75.00, 98.00, 37.30, 19.43259000, -99.13324000, '2025-11-28 07:11:06'),
(64, 'ESP32-001', 65.00, 96.00, 37.20, 19.43264000, -99.13323000, '2025-11-28 07:11:13'),
(65, 'ESP32-001', 69.00, 96.00, 37.30, 19.43258000, -99.13325000, '2025-11-28 07:11:14'),
(66, 'ESP32-001', 81.00, 98.00, 37.50, 19.43264000, -99.13321000, '2025-11-28 07:11:21'),
(67, 'ESP32-001', 73.00, 97.00, 37.20, 19.43259000, -99.13326000, '2025-11-28 07:11:23'),
(68, 'ESP32-001', 69.00, 98.00, 37.80, 19.43265000, -99.13321000, '2025-11-28 07:11:26'),
(69, 'ESP32-001', 84.00, 97.00, 37.20, 19.43264000, -99.13320000, '2025-11-28 07:11:32'),
(70, 'ESP32-001', 71.00, 96.00, 37.80, 19.43263000, -99.13321000, '2025-11-28 07:11:36'),
(71, 'ESP32-001', 82.00, 97.00, 37.10, 19.43261000, -99.13319000, '2025-11-28 07:11:42'),
(72, 'ESP32-001', 82.00, 98.00, 36.60, 19.43260000, -99.13322000, '2025-11-28 07:11:46'),
(73, 'ESP32-001', 73.00, 97.00, 37.30, 19.43257000, -99.13319000, '2025-11-28 07:11:49'),
(74, 'ESP32-001', 72.00, 96.00, 36.90, 19.43264000, -99.13325000, '2025-11-28 07:11:53'),
(75, 'ESP32-001', 65.00, 96.00, 36.60, 19.43259000, -99.13325000, '2025-11-28 07:11:57'),
(76, 'ESP32-001', 80.00, 98.00, 37.30, 19.43256000, -99.13318000, '2025-11-28 07:12:01'),
(77, 'ESP32-001', 81.00, 96.00, 36.70, 19.43261000, -99.13319000, '2025-11-28 07:12:04'),
(78, 'ESP32-001', 65.00, 96.00, 37.70, 19.43263000, -99.13320000, '2025-11-28 07:12:08'),
(79, 'ESP32-001', 69.00, 97.00, 36.80, 19.43259000, -99.13322000, '2025-11-28 07:12:14'),
(80, 'ESP32-001', 67.00, 96.00, 37.20, 19.43263000, -99.13319000, '2025-11-28 07:12:17'),
(81, 'ESP32-001', 69.00, 97.00, 36.50, 19.43259000, -99.13319000, '2025-11-28 07:12:21'),
(82, 'ESP32-001', 72.00, 98.00, 36.80, 19.43263000, -99.13322000, '2025-11-28 07:12:25'),
(83, 'ESP32-001', 81.00, 98.00, 36.50, 19.43265000, -99.13318000, '2025-11-28 07:12:28'),
(84, 'ESP32-001', 84.00, 98.00, 37.00, 19.43261000, -99.13319000, '2025-11-28 07:12:32'),
(85, 'ESP32-001', 70.00, 98.00, 36.80, 19.43264000, -99.13316000, '2025-11-28 07:12:36'),
(86, 'ESP32-001', 81.00, 98.00, 37.40, 19.43264000, -99.13316000, '2025-11-28 07:12:39'),
(87, 'ESP32-001', 67.00, 97.00, 37.10, 19.43256000, -99.13318000, '2025-11-28 07:12:43'),
(88, 'ESP32-001', 69.00, 96.00, 37.20, 19.43261000, -99.13318000, '2025-11-28 07:12:47'),
(89, 'ESP32-001', 77.00, 98.00, 36.60, 19.43266000, -99.13318000, '2025-11-28 07:12:50'),
(90, 'ESP32-001', 82.00, 97.00, 37.80, 19.43258000, -99.13322000, '2025-11-28 07:12:54'),
(91, 'ESP32-001', 65.00, 98.00, 36.80, 19.43263000, -99.13320000, '2025-11-28 07:12:59'),
(92, 'ESP32-001', 78.00, 96.00, 36.80, 19.43256000, -99.13316000, '2025-11-28 07:13:03'),
(93, 'ESP32-001', 79.00, 96.00, 36.80, 19.43263000, -99.13323000, '2025-11-28 07:13:07'),
(94, 'ESP32-001', 79.00, 96.00, 36.50, 19.43257000, -99.13321000, '2025-11-28 07:13:10'),
(95, 'ESP32-001', 65.00, 96.00, 37.10, 19.43259000, -99.13321000, '2025-11-28 07:13:14'),
(96, 'ESP32-001', 65.00, 96.00, 37.40, 19.43259000, -99.13322000, '2025-11-28 07:13:18'),
(97, 'ESP32-001', 77.00, 96.00, 37.80, 19.43256000, -99.13321000, '2025-11-28 07:13:21'),
(98, 'ESP32-001', 83.00, 96.00, 37.70, 19.43263000, -99.13317000, '2025-11-28 07:13:27'),
(99, 'ESP32-001', 76.00, 97.00, 37.30, 19.43257000, -99.13317000, '2025-11-28 07:13:32'),
(100, 'ESP32-001', 73.00, 98.00, 37.60, 19.43262000, -99.13319000, '2025-11-28 07:13:36'),
(101, 'ESP32-001', 84.00, 97.00, 37.10, 19.43265000, -99.13323000, '2025-11-28 07:13:41'),
(102, 'ESP32-001', 76.00, 96.00, 36.70, 19.43262000, -99.13322000, '2025-11-28 07:13:45'),
(103, 'ESP32-001', 83.00, 97.00, 37.80, 19.43257000, -99.13325000, '2025-11-28 07:13:49'),
(104, 'ESP32-001', 75.00, 98.00, 37.90, 19.43261000, -99.13320000, '2025-11-28 07:13:55'),
(105, 'ESP32-001', 65.00, 98.00, 37.70, 19.43257000, -99.13322000, '2025-11-28 07:14:00'),
(106, 'ESP32-001', 70.00, 96.00, 36.70, 19.43264000, -99.13319000, '2025-11-28 07:14:06'),
(107, 'ESP32-001', 68.00, 97.00, 36.80, 19.43258000, -99.13323000, '2025-11-28 07:14:09'),
(108, 'ESP32-001', 77.00, 97.00, 37.20, 19.43263000, -99.13323000, '2025-11-28 07:14:13'),
(109, 'ESP32-001', 74.00, 97.00, 37.30, 19.43265000, -99.13320000, '2025-11-28 07:14:16'),
(110, 'ESP32-001', 65.00, 98.00, 36.60, 19.43256000, -99.13322000, '2025-11-28 07:14:20'),
(111, 'ESP32-001', 66.00, 97.00, 37.40, 19.43265000, -99.13316000, '2025-11-28 07:14:24'),
(112, 'ESP32-001', 83.00, 97.00, 37.80, 19.43261000, -99.13319000, '2025-11-28 07:14:27'),
(113, 'ESP32-001', 66.00, 96.00, 37.20, 19.43257000, -99.13322000, '2025-11-28 07:14:33'),
(114, 'ESP32-001', 68.00, 98.00, 37.90, 19.43266000, -99.13322000, '2025-11-28 07:14:36'),
(115, 'ESP32-001', 83.00, 97.00, 36.50, 19.43264000, -99.13320000, '2025-11-28 07:14:42'),
(116, 'ESP32-001', 73.00, 98.00, 37.50, 19.43258000, -99.13320000, '2025-11-28 07:14:45'),
(117, 'ESP32-001', 71.00, 98.00, 36.90, 19.43261000, -99.13322000, '2025-11-28 07:14:49'),
(118, 'ESP32-001', 75.00, 98.00, 36.50, NULL, NULL, '2025-11-28 07:15:37'),
(119, 'ESP32-001', 75.00, 98.00, 36.50, NULL, NULL, '2025-11-28 07:16:53'),
(120, 'ESP32-001', 74.00, 98.10, 37.20, 19.43264000, -99.13323000, '2025-11-28 07:17:24'),
(121, 'ESP32-001', 72.30, 98.50, 37.20, 19.43263000, -99.13322000, '2025-11-28 07:17:54'),
(122, 'ESP32-001', 72.50, 98.00, 37.20, 19.43262000, -99.13317000, '2025-11-28 07:18:25'),
(123, 'ESP32-001', 71.20, 98.00, 37.10, 19.43259000, -99.13319000, '2025-11-28 07:18:55'),
(124, 'ESP32-001', 69.60, 98.40, 36.40, 19.43262000, -99.13321000, '2025-11-28 07:19:26'),
(125, 'ESP32-001', 71.00, 98.60, 36.20, 19.43264000, -99.13324000, '2025-11-28 07:19:57'),
(126, 'ESP32-001', 69.30, 98.90, 36.90, 19.43263000, -99.13324000, '2025-11-28 07:20:27'),
(127, 'ESP32-001', 68.70, 98.60, 36.60, 19.43261000, -99.13325000, '2025-11-28 07:20:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_sincronizacion_c5`
--

CREATE TABLE `logs_sincronizacion_c5` (
  `id` int(11) NOT NULL,
  `id_alerta_privada` int(11) NOT NULL,
  `accion` enum('ENVIADO_C5','ACTUALIZADO_DESDE_C5') NOT NULL,
  `datos_enviados` text DEFAULT NULL,
  `datos_recibidos` text DEFAULT NULL,
  `fecha_sincronizacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `logs_sincronizacion_c5`
--

INSERT INTO `logs_sincronizacion_c5` (`id`, `id_alerta_privada`, `accion`, `datos_enviados`, `datos_recibidos`, `fecha_sincronizacion`) VALUES
(12, 8, 'ENVIADO_C5', '{\"id\":8,\"id_dispositivo\":\"ESP32-001\",\"tipo_alerta\":\"extravio\",\"descripcion\":\"Paciente extraviado\",\"ubicacion_lat\":null,\"ubicacion_lon\":null,\"estado\":\"PENDIENTE\",\"fecha_creacion\":\"2025-11-28 00:56:59\",\"fecha_actualizacion\":\"2025-11-28 00:56:59\",\"sincronizado_c5\":0,\"id_alerta_publica\":null,\"notas_c5\":null,\"nombre_paciente\":\"Mar\\u00eda Gonz\\u00e1lez\",\"edad\":72,\"enfermedades_cronicas\":\"Hipertensi\\u00f3n, Diabetes\",\"contacto_emergencia\":\"Contacto no especificado\",\"direccion_residencia\":\"Direcci\\u00f3n no especificada\"}', NULL, '2025-11-28 06:57:00'),
(13, 8, 'ACTUALIZADO_DESDE_C5', NULL, '{\"estado_nuevo\":\"RESUELTA\",\"notas_c5\":\"\",\"fecha_actualizacion\":\"2025-11-28 07:57:18\"}', '2025-11-28 06:57:18'),
(14, 8, 'ACTUALIZADO_DESDE_C5', NULL, '{\"estado_nuevo\":\"RESUELTA\",\"notas_c5\":\"\",\"fecha_actualizacion\":\"2025-11-28 07:57:23\"}', '2025-11-28 06:57:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_alertas`
--

CREATE TABLE `log_alertas` (
  `id` int(11) NOT NULL,
  `id_dispositivo` varchar(50) NOT NULL,
  `tipo_alerta` enum('medica','extravio') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ubicacion_lat` decimal(10,8) DEFAULT NULL,
  `ubicacion_lon` decimal(11,8) DEFAULT NULL,
  `estado` enum('PENDIENTE','EN PROCESO','EN LUGAR','RESUELTA','CANCELADA') DEFAULT 'PENDIENTE',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sincronizado_c5` tinyint(1) DEFAULT 0,
  `id_alerta_publica` int(11) DEFAULT NULL,
  `notas_c5` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `log_alertas`
--

INSERT INTO `log_alertas` (`id`, `id_dispositivo`, `tipo_alerta`, `descripcion`, `ubicacion_lat`, `ubicacion_lon`, `estado`, `fecha_creacion`, `fecha_actualizacion`, `sincronizado_c5`, `id_alerta_publica`, `notas_c5`) VALUES
(8, 'ESP32-001', 'extravio', 'Paciente extraviado', NULL, NULL, 'RESUELTA', '2025-11-28 06:56:59', '2025-11-28 06:57:23', 1, 21, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `codigo` varchar(50) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_paciente` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `direccion_residencia` text NOT NULL,
  `direccion_recoleccion` text DEFAULT NULL,
  `contacto_emergencia` varchar(200) NOT NULL,
  `edad` int(11) NOT NULL,
  `enfermedades_cronicas` text DEFAULT NULL,
  `estado` enum('online','offline','inactivo') DEFAULT 'offline',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_lectura` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`codigo`, `id_usuario`, `nombre_paciente`, `fecha_nacimiento`, `direccion_residencia`, `direccion_recoleccion`, `contacto_emergencia`, `edad`, `enfermedades_cronicas`, `estado`, `fecha_registro`, `ultima_lectura`) VALUES
('ESP32-001', 1, 'María González', '1950-01-01', 'Dirección no especificada', NULL, 'Contacto no especificado', 72, 'Hipertensión, Diabetes', 'online', '2025-11-26 00:38:39', '2025-11-28 07:20:58'),
('ESP32-002', 2, 'Carlos López', '1950-01-01', 'Dirección no especificada', NULL, 'Contacto no especificado', 68, 'Problemas cardíacos', 'offline', '2025-11-26 00:38:39', '2025-11-26 03:29:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `umbrales_alerta`
--

CREATE TABLE `umbrales_alerta` (
  `id` int(11) NOT NULL,
  `id_dispositivo` varchar(50) NOT NULL,
  `umbral_FC_min` int(11) DEFAULT 60,
  `umbral_FC_max` int(11) DEFAULT 100,
  `umbral_SpO2_min` int(11) DEFAULT 90,
  `umbral_temperatura_min` decimal(4,2) DEFAULT 35.50,
  `umbral_temperatura_max` decimal(4,2) DEFAULT 37.50,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `umbrales_alerta`
--

INSERT INTO `umbrales_alerta` (`id`, `id_dispositivo`, `umbral_FC_min`, `umbral_FC_max`, `umbral_SpO2_min`, `umbral_temperatura_min`, `umbral_temperatura_max`, `fecha_actualizacion`) VALUES
(1, 'ESP32-001', 84, 100, 90, 35.50, 37.50, '2025-11-28 07:18:50'),
(2, 'ESP32-002', 60, 100, 90, 35.50, 37.50, '2025-11-26 00:38:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tipo_usuario` enum('familiar','cuidador','medico') DEFAULT 'familiar',
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `tipo_usuario`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Administrador', 'admin@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'medico', 'activo', '2025-11-26 00:38:39', '2025-11-26 00:38:39'),
(2, 'Juan Pérez', 'juan@familia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'familiar', 'activo', '2025-11-26 00:38:39', '2025-11-26 00:38:39');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dispositivo_fecha` (`id_dispositivo`,`fecha_lectura`);

--
-- Indices de la tabla `logs_sincronizacion_c5`
--
ALTER TABLE `logs_sincronizacion_c5`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_alerta_privada` (`id_alerta_privada`),
  ADD KEY `idx_fecha` (`fecha_sincronizacion`);

--
-- Indices de la tabla `log_alertas`
--
ALTER TABLE `log_alertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_dispositivo` (`id_dispositivo`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `umbrales_alerta`
--
ALTER TABLE `umbrales_alerta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_dispositivo` (`id_dispositivo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT de la tabla `logs_sincronizacion_c5`
--
ALTER TABLE `logs_sincronizacion_c5`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `log_alertas`
--
ALTER TABLE `log_alertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `umbrales_alerta`
--
ALTER TABLE `umbrales_alerta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD CONSTRAINT `lecturas_ibfk_1` FOREIGN KEY (`id_dispositivo`) REFERENCES `pacientes` (`codigo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `log_alertas`
--
ALTER TABLE `log_alertas`
  ADD CONSTRAINT `log_alertas_ibfk_1` FOREIGN KEY (`id_dispositivo`) REFERENCES `pacientes` (`codigo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD CONSTRAINT `pacientes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `umbrales_alerta`
--
ALTER TABLE `umbrales_alerta`
  ADD CONSTRAINT `umbrales_alerta_ibfk_1` FOREIGN KEY (`id_dispositivo`) REFERENCES `pacientes` (`codigo`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
