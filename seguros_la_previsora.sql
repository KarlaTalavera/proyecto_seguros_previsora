-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-12-2025 a las 03:04:10
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
-- Base de datos: `seguros_la_previsora`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

CREATE TABLE `administrador` (
  `cedula_admin` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`cedula_admin`, `nombre`, `apellido`, `telefono`) VALUES
('V31843813', 'Karla', 'Talavera', '04121365498');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agente`
--

CREATE TABLE `agente` (
  `cedula_agente` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `agente`
--

INSERT INTO `agente` (`cedula_agente`, `nombre`, `apellido`, `telefono`) VALUES
('V12345678', 'Nuevo', 'Agente', '04123333333'),
('V26260313', 'Tilinaso', 'Papulince', '0412-1361992');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agente_permiso`
--

CREATE TABLE `agente_permiso` (
  `cedula_agente` varchar(20) NOT NULL,
  `id_permiso` int(10) UNSIGNED NOT NULL,
  `tiene_permiso` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `agente_permiso`
--

INSERT INTO `agente_permiso` (`cedula_agente`, `id_permiso`, `tiene_permiso`) VALUES
('V12345678', 1, 1),
('V12345678', 2, 0),
('V12345678', 3, 0),
('V12345678', 4, 0),
('V12345678', 5, 0),
('V12345678', 6, 1),
('V12345678', 7, 0),
('V12345678', 8, 0),
('V12345678', 9, 0),
('V12345678', 10, 0),
('V12345678', 11, 1),
('V12345678', 12, 0),
('V12345678', 13, 0),
('V12345678', 14, 0),
('V12345678', 15, 1),
('V12345678', 16, 1),
('V12345678', 17, 1),
('V12345678', 18, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asegurado`
--

CREATE TABLE `asegurado` (
  `id_asegurado` int(10) UNSIGNED NOT NULL,
  `id_poliza` int(10) UNSIGNED NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `parentesco` varchar(100) DEFAULT NULL,
  `sexo` enum('M','F') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asegurado`
--

INSERT INTO `asegurado` (`id_asegurado`, `id_poliza`, `cedula`, `nombre`, `apellido`, `fecha_nacimiento`, `parentesco`, `sexo`) VALUES
(1, 105, 'V31663615', 'Tomas', 'Rodriguez', '2025-12-01', 'Hijo/a', 'M'),
(7, 102, 'V12345678', 'Elizabeth', 'Barboza', '2025-02-04', 'Hijo/a', 'F');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_poliza`
--

CREATE TABLE `categoria_poliza` (
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria_poliza`
--

INSERT INTO `categoria_poliza` (`id_categoria`, `nombre`) VALUES
(2, 'Automóvil'),
(3, 'Patrimoniales'),
(1, 'Personas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `cedula_asegurado` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `cedula_asegurado`, `nombre`, `apellido`, `telefono`, `direccion`, `fecha_nacimiento`) VALUES
(1, 'V20000001', 'Juan', 'Pérez', '04141234567', 'Av. Principal, Caracas', '1985-04-12'),
(2, 'V20000002', 'María', 'Gómez', '04147654321', 'Calle Falsa 123, Valencia', '1990-07-02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cobertura`
--

CREATE TABLE `cobertura` (
  `id_cobertura` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `detalle` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cobertura`
--

INSERT INTO `cobertura` (`id_cobertura`, `nombre`, `detalle`) VALUES
(1, 'Asistencia Vial', 'Grúa y asistencia mecánica'),
(2, 'GPS', 'Seguimiento GPS opcional'),
(3, 'Robo Total', 'Cobertura por sustracción ilegítima');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_poliza`
--

CREATE TABLE `detalle_poliza` (
  `id_poliza` int(10) UNSIGNED NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `monto_prima_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `numero_cuotas` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `monto_cuota` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_primer_vencimiento` date NOT NULL,
  `frecuencia_pago` enum('MENSUAL','TRIMESTRAL','SEMESTRAL','ANUAL') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_poliza`
--

INSERT INTO `detalle_poliza` (`id_poliza`, `fecha_inicio`, `fecha_fin`, `monto_prima_total`, `numero_cuotas`, `monto_cuota`, `fecha_primer_vencimiento`, `frecuencia_pago`) VALUES
(100, '2024-12-05', '2025-11-25', 450.00, 1, 450.00, '2025-10-26', 'ANUAL'),
(101, '2025-05-05', '2025-11-12', 1200.00, 1, 1200.00, '2025-10-01', 'ANUAL'),
(102, '2025-10-05', '2025-11-15', 95.00, 1, 95.00, '2025-09-01', 'ANUAL'),
(103, '2025-08-05', '2026-02-03', 300.00, 1, 300.00, '2025-07-08', 'ANUAL'),
(104, '2024-11-05', '2025-11-10', 800.00, 1, 800.00, '2025-04-19', 'ANUAL'),
(105, '2025-09-05', '2025-11-17', 1500.00, 1, 1500.00, '2026-11-05', 'ANUAL'),
(106, '2025-12-03', '2026-12-31', 1000.00, 32, 31.25, '2025-12-03', 'TRIMESTRAL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso`
--

CREATE TABLE `permiso` (
  `id_permiso` int(10) UNSIGNED NOT NULL,
  `nombre_permiso` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permiso`
--

INSERT INTO `permiso` (`id_permiso`, `nombre_permiso`, `descripcion`) VALUES
(1, 'cliente_crear', 'Permite registrar nuevos clientes.'),
(2, 'cliente_ver_lista', 'Permite ver la lista de clientes.'),
(3, 'cliente_editar', 'Permite editar la información de los clientes.'),
(4, 'cliente_eliminar', 'Permite eliminar clientes.'),
(5, 'poliza_crear', 'Permite crear nuevas pólizas.'),
(6, 'poliza_ver_lista', 'Permite ver la lista de pólizas.'),
(7, 'poliza_editar', 'Permite editar la información de las pólizas.'),
(8, 'poliza_eliminar', 'Permite eliminar pólizas.'),
(9, 'siniestro_crear', 'Permite registrar nuevos siniestros.'),
(10, 'siniestro_ver_lista', 'Permite ver la lista de siniestros.'),
(11, 'siniestro_editar', 'Permite editar la información de los siniestros.'),
(12, 'siniestro_eliminar', 'Permite eliminar siniestros.'),
(13, 'reportes_generar_polizas', 'Permite generar reportes de pólizas.'),
(14, 'reportes_generar_siniestros', 'Permite generar reportes de siniestros.'),
(15, 'reportes_generar_clientes', 'Permite generar reportes de clientes.'),
(16, 'poliza_categoria_personas', 'Autoriza al agente a emitir pólizas de la categoría Personas.'),
(17, 'poliza_categoria_automovil', 'Autoriza al agente a emitir pólizas de la categoría Automóvil.'),
(18, 'poliza_categoria_patrimoniales', 'Autoriza al agente a emitir pólizas de la categoría Patrimoniales.'),
(19, 'solicitud_gestionar', 'Gestionar solicitudes de pólizas y siniestros');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `poliza`
--

CREATE TABLE `poliza` (
  `id_poliza` int(10) UNSIGNED NOT NULL,
  `numero_poliza` varchar(50) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `cedula_agente` varchar(20) NOT NULL,
  `id_tipo_poliza` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `poliza`
--

INSERT INTO `poliza` (`id_poliza`, `numero_poliza`, `estado`, `id_cliente`, `cedula_agente`, `id_tipo_poliza`) VALUES
(100, 'POL-1001', 'ELIMINADA', 1, 'V12345678', 5),
(101, 'POL-1002', 'ACTIVA', 2, 'V12345678', 6),
(102, 'POL-1003', 'PENDIENTE', 1, 'V12345678', 1),
(103, 'POL-1004', 'ACTIVA', 2, 'V12345678', 3),
(104, 'POL-1005', 'VENCER', 1, 'V12345678', 8),
(105, 'POL-1006', 'ACTIVA', 2, 'V12345678', 9),
(106, 'POL-1007', 'ACTIVA', 1, 'V12345678', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `poliza_cobertura`
--

CREATE TABLE `poliza_cobertura` (
  `id_poliza` int(10) UNSIGNED NOT NULL,
  `id_cobertura` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `poliza_cobertura`
--

INSERT INTO `poliza_cobertura` (`id_poliza`, `id_cobertura`) VALUES
(101, 1),
(101, 2),
(105, 3),
(106, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `poliza_cuota`
--

CREATE TABLE `poliza_cuota` (
  `id_cuota` int(10) UNSIGNED NOT NULL,
  `id_poliza` int(10) UNSIGNED NOT NULL,
  `numero_cuota` smallint(5) UNSIGNED NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `monto_programado` decimal(12,2) NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `monto_pagado` decimal(12,2) DEFAULT NULL,
  `estado` enum('PENDIENTE','PAGADO','ATRASADO','CONDONADO') NOT NULL DEFAULT 'PENDIENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `poliza_cuota`
--

INSERT INTO `poliza_cuota` (`id_cuota`, `id_poliza`, `numero_cuota`, `fecha_vencimiento`, `monto_programado`, `fecha_pago`, `monto_pagado`, `estado`) VALUES
(1, 100, 1, '2025-10-26', 450.00, NULL, NULL, 'ATRASADO'),
(2, 101, 1, '2025-10-01', 1200.00, NULL, NULL, 'ATRASADO'),
(3, 102, 1, '2025-09-01', 95.00, NULL, NULL, 'ATRASADO'),
(4, 103, 1, '2025-07-08', 300.00, NULL, NULL, 'ATRASADO'),
(5, 104, 1, '2025-04-19', 800.00, NULL, NULL, 'ATRASADO'),
(6, 105, 1, '2026-11-05', 1500.00, NULL, NULL, 'PENDIENTE'),
(7, 106, 1, '2025-12-03', 31.25, NULL, NULL, 'PENDIENTE'),
(8, 106, 2, '2026-03-03', 31.25, NULL, NULL, 'PENDIENTE'),
(9, 106, 3, '2026-06-03', 31.25, NULL, NULL, 'PENDIENTE'),
(10, 106, 4, '2026-09-03', 31.25, NULL, NULL, 'PENDIENTE'),
(11, 106, 5, '2026-12-03', 31.25, NULL, NULL, 'PENDIENTE'),
(12, 106, 6, '2027-03-03', 31.25, NULL, NULL, 'PENDIENTE'),
(13, 106, 7, '2027-06-03', 31.25, NULL, NULL, 'PENDIENTE'),
(14, 106, 8, '2027-09-03', 31.25, NULL, NULL, 'PENDIENTE'),
(15, 106, 9, '2027-12-03', 31.25, NULL, NULL, 'PENDIENTE'),
(16, 106, 10, '2028-03-03', 31.25, NULL, NULL, 'PENDIENTE'),
(17, 106, 11, '2028-06-03', 31.25, NULL, NULL, 'PENDIENTE'),
(18, 106, 12, '2028-09-03', 31.25, NULL, NULL, 'PENDIENTE'),
(19, 106, 13, '2028-12-03', 31.25, NULL, NULL, 'PENDIENTE'),
(20, 106, 14, '2029-03-03', 31.25, NULL, NULL, 'PENDIENTE'),
(21, 106, 15, '2029-06-03', 31.25, NULL, NULL, 'PENDIENTE'),
(22, 106, 16, '2029-09-03', 31.25, NULL, NULL, 'PENDIENTE'),
(23, 106, 17, '2029-12-03', 31.25, NULL, NULL, 'PENDIENTE'),
(24, 106, 18, '2030-03-03', 31.25, NULL, NULL, 'PENDIENTE'),
(25, 106, 19, '2030-06-03', 31.25, NULL, NULL, 'PENDIENTE'),
(26, 106, 20, '2030-09-03', 31.25, NULL, NULL, 'PENDIENTE'),
(27, 106, 21, '2030-12-03', 31.25, NULL, NULL, 'PENDIENTE'),
(28, 106, 22, '2031-03-03', 31.25, NULL, NULL, 'PENDIENTE'),
(29, 106, 23, '2031-06-03', 31.25, NULL, NULL, 'PENDIENTE'),
(30, 106, 24, '2031-09-03', 31.25, NULL, NULL, 'PENDIENTE'),
(31, 106, 25, '2031-12-03', 31.25, NULL, NULL, 'PENDIENTE'),
(32, 106, 26, '2032-03-03', 31.25, NULL, NULL, 'PENDIENTE'),
(33, 106, 27, '2032-06-03', 31.25, NULL, NULL, 'PENDIENTE'),
(34, 106, 28, '2032-09-03', 31.25, NULL, NULL, 'PENDIENTE'),
(35, 106, 29, '2032-12-03', 31.25, NULL, NULL, 'PENDIENTE'),
(36, 106, 30, '2033-03-03', 31.25, NULL, NULL, 'PENDIENTE'),
(37, 106, 31, '2033-06-03', 31.25, NULL, NULL, 'PENDIENTE'),
(38, 106, 32, '2033-09-03', 31.25, NULL, NULL, 'PENDIENTE');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(10) UNSIGNED NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`) VALUES
(1, 'administrador'),
(2, 'agente'),
(3, 'asegurado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `siniestro`
--

CREATE TABLE `siniestro` (
  `id_siniestro` int(10) UNSIGNED NOT NULL,
  `numero_siniestro` varchar(50) NOT NULL,
  `fecha_reporte` timestamp NOT NULL DEFAULT current_timestamp(),
  `descripcion` text NOT NULL,
  `monto_estimado` decimal(10,2) DEFAULT NULL,
  `estado` varchar(50) NOT NULL,
  `id_poliza` int(10) UNSIGNED NOT NULL,
  `cedula_agente_gestion` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `siniestro`
--

INSERT INTO `siniestro` (`id_siniestro`, `numero_siniestro`, `fecha_reporte`, `descripcion`, `monto_estimado`, `estado`, `id_poliza`, `cedula_agente_gestion`) VALUES
(1, 'S-202412-01', '2024-12-01 14:00:00', 'Siniestro prueba -11', 1200.00, 'ABIERTO', 100, 'V12345678'),
(2, 'S-202501-01', '2025-01-01 14:00:00', 'Siniestro prueba -10', 800.00, 'CERRADO', 101, 'V12345678'),
(3, 'S-202502-01', '2025-02-01 14:00:00', 'Siniestro prueba -9', 950.00, 'ABIERTO', 102, 'V12345678'),
(4, 'S-202503-01', '2025-03-01 14:00:00', 'Siniestro prueba -8', 400.00, 'ABIERTO', 103, 'V12345678'),
(5, 'S-202504-01', '2025-04-01 14:00:00', 'Siniestro prueba -7', 1300.00, 'CERRADO', 104, 'V12345678'),
(6, 'S-202505-01', '2025-05-01 14:00:00', 'Siniestro prueba -6', 600.00, 'ABIERTO', 105, 'V12345678'),
(7, 'S-202506-01', '2025-06-01 14:00:00', 'Siniestro prueba -5', 1100.00, 'CERRADO', 100, 'V12345678'),
(8, 'S-202507-01', '2025-07-01 14:00:00', 'Siniestro prueba -4', 300.00, 'ABIERTO', 101, 'V12345678'),
(9, 'S-202508-01', '2025-08-01 14:00:00', 'Siniestro prueba -3', 720.00, 'ABIERTO', 102, 'V12345678'),
(10, 'S-202509-01', '2025-09-01 14:00:00', 'Siniestro prueba -2', 1400.00, 'CERRADO', 103, 'V12345678'),
(11, 'S-202510-01', '2025-10-01 14:00:00', 'Siniestro prueba -1', 980.00, 'ABIERTO', 104, 'V12345678'),
(12, 'S-202511-01', '2025-11-01 14:00:00', 'Siniestro prueba 0', 900.00, 'ABIERTO', 105, 'V12345678'),
(13, 'S-202412-P100-1', '2024-12-15 14:00:00', 'Siniestro prueba (mes -11) P100', 1200.00, 'ABIERTO', 100, 'V12345678'),
(14, 'S-202412-P101-1', '2024-12-20 15:00:00', 'Siniestro prueba (mes -11) P101', 750.00, 'CERRADO', 101, 'V12345678'),
(15, 'S-202501-P102-1', '2025-01-10 13:30:00', 'Siniestro prueba (mes -10) P102', 400.00, 'ABIERTO', 102, 'V12345678'),
(16, 'S-202502-P100-2', '2025-02-05 12:00:00', 'Siniestro prueba (mes -9) P100', 950.00, 'ABIERTO', 100, 'V12345678'),
(17, 'S-202502-P103-1', '2025-02-14 17:10:00', 'Siniestro prueba (mes -9) P103', 1100.00, 'CERRADO', 103, 'V12345678'),
(18, 'S-202502-P104-1', '2025-02-22 20:20:00', 'Siniestro prueba (mes -9) P104', 320.00, 'ABIERTO', 104, 'V12345678'),
(19, 'S-202504-P104-2', '2025-04-09 16:00:00', 'Siniestro prueba (mes -7) P104', 1300.00, 'CERRADO', 104, 'V12345678'),
(20, 'S-202504-P105-1', '2025-04-18 19:30:00', 'Siniestro prueba (mes -7) P105', 880.00, 'ABIERTO', 105, 'V12345678'),
(21, 'S-202505-P100-3', '2025-05-03 13:00:00', 'Siniestro prueba (mes -6) P100', 600.00, 'ABIERTO', 100, 'V12345678'),
(22, 'S-202505-P101-2', '2025-05-12 14:20:00', 'Siniestro prueba (mes -6) P101', 450.00, 'CERRADO', 101, 'V12345678'),
(23, 'S-202505-P102-2', '2025-05-25 18:45:00', 'Siniestro prueba (mes -6) P102', 720.00, 'ABIERTO', 102, 'V12345678'),
(24, 'S-202506-P103-2', '2025-06-07 15:00:00', 'Siniestro prueba (mes -5) P103', 1100.00, 'CERRADO', 103, 'V12345678'),
(25, 'S-202507-P105-2', '2025-07-16 14:00:00', 'Siniestro prueba (mes -4) P105', 300.00, 'ABIERTO', 105, 'V12345678'),
(26, 'S-202507-P100-4', '2025-07-26 13:15:00', 'Siniestro prueba (mes -4) P100', 480.00, 'ABIERTO', 100, 'V12345678'),
(27, 'S-202508-P101-3', '2025-08-02 12:30:00', 'Siniestro prueba (mes -3) P101', 720.00, 'ABIERTO', 101, 'V12345678'),
(28, 'S-202508-P102-3', '2025-08-11 17:00:00', 'Siniestro prueba (mes -3) P102', 1400.00, 'CERRADO', 102, 'V12345678'),
(29, 'S-202508-P103-3', '2025-08-21 21:20:00', 'Siniestro prueba (mes -3) P103', 560.00, 'ABIERTO', 103, 'V12345678'),
(30, 'S-202508-P104-3', '2025-08-28 16:40:00', 'Siniestro prueba (mes -3) P104', 350.00, 'ABIERTO', 104, 'V12345678'),
(31, 'S-202509-P100-5', '2025-09-06 13:50:00', 'Siniestro prueba (mes -2) P100', 1400.00, 'CERRADO', 100, 'V12345678'),
(32, 'S-202509-P101-4', '2025-09-15 15:15:00', 'Siniestro prueba (mes -2) P101', 980.00, 'ABIERTO', 101, 'V12345678'),
(33, 'S-202509-P102-4', '2025-09-24 20:00:00', 'Siniestro prueba (mes -2) P102', 420.00, 'ABIERTO', 102, 'V12345678'),
(34, 'S-202510-P103-4', '2025-10-05 14:10:00', 'Siniestro prueba (mes -1) P103', 980.00, 'ABIERTO', 103, 'V12345678'),
(35, 'S-202510-P104-4', '2025-10-18 18:30:00', 'Siniestro prueba (mes -1) P104', 650.00, 'CERRADO', 104, 'V12345678'),
(36, 'S-202511-P105-3', '2025-11-10 04:00:00', 'Siniestro prueba (mes 0) P105', 900.00, 'ABIERTO', 105, 'V12345678');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_poliza`
--

CREATE TABLE `tipo_poliza` (
  `id_tipo_poliza` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `id_categoria` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_poliza`
--

INSERT INTO `tipo_poliza` (`id_tipo_poliza`, `nombre`, `id_categoria`) VALUES
(1, 'Accidentes Personales', 1),
(2, 'AP Escolar Colectiva', 1),
(3, 'Salud - Telemedicina y Domiciliaria', 1),
(4, 'Funerario Previ Serenidad', 1),
(5, 'R.C.V. Vehículos', 2),
(6, 'Combinado Residencial', 3),
(7, 'Combinado Empresarial', 3),
(8, 'Incendio', 3),
(9, 'Sustracción Ilegítima', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_poliza_cobertura`
--

CREATE TABLE `tipo_poliza_cobertura` (
  `id_tipo_poliza` int(10) UNSIGNED NOT NULL,
  `id_cobertura` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_poliza_cobertura`
--

INSERT INTO `tipo_poliza_cobertura` (`id_tipo_poliza`, `id_cobertura`) VALUES
(5, 1),
(5, 2),
(5, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `cedula` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `id_rol` int(10) UNSIGNED NOT NULL,
  `foto_perfil` varchar(255) DEFAULT 'undraw_profile.svg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`cedula`, `email`, `password_hash`, `activo`, `id_rol`, `foto_perfil`) VALUES
('V12345678', 'agente@example.com', '$2y$10$cKK9.jeL.I0NNhAQzuBOXehgD1NPK/Kt52mHrfh4YZL0XNoFwTj3y', 1, 2, 'perfil_V12345678_1764683485.jpg'),
('V20000001', 'juan.perez@example.com', '$2y$10$0X84RacVPt9gcu1DwqcDBONznGMyoCHSNC2rZ3Oc4frVsXkcDyKHy', 1, 3, 'undraw_profile.svg'),
('V20000002', 'maria.gomez@example.com', '$2y$10$cIBvbEvDurW46YugpZYzDuOOhHJpdBYKnPN/V8k0zBKBPjVTmsJVK', 1, 3, 'undraw_profile.svg'),
('V26260313', 'k4tam4ria@gmail.com', '$2y$10$JoiP4w0Kkryfe7XAUCd.xuFmBQS.Al8d6t6AIoK5xpKXhMWKaKpVu', 1, 2, 'undraw_profile.svg'),
('V31843813', 'admin@previsora.com', '$2y$10$a79Q3w6f9KuUDbL4j74xQOKxy5PQ6hFaHRCuAh2mwxKhN6VL.avja', 1, 1, 'perfil_V31843813_1764682914.jpg');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`cedula_admin`);

--
-- Indices de la tabla `agente`
--
ALTER TABLE `agente`
  ADD PRIMARY KEY (`cedula_agente`);

--
-- Indices de la tabla `agente_permiso`
--
ALTER TABLE `agente_permiso`
  ADD PRIMARY KEY (`cedula_agente`,`id_permiso`),
  ADD KEY `fk_permiso` (`id_permiso`);

--
-- Indices de la tabla `asegurado`
--
ALTER TABLE `asegurado`
  ADD PRIMARY KEY (`id_asegurado`),
  ADD KEY `fk_asegurado_poliza` (`id_poliza`);

--
-- Indices de la tabla `categoria_poliza`
--
ALTER TABLE `categoria_poliza`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `cedula_asegurado` (`cedula_asegurado`);

--
-- Indices de la tabla `cobertura`
--
ALTER TABLE `cobertura`
  ADD PRIMARY KEY (`id_cobertura`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `detalle_poliza`
--
ALTER TABLE `detalle_poliza`
  ADD PRIMARY KEY (`id_poliza`);

--
-- Indices de la tabla `permiso`
--
ALTER TABLE `permiso`
  ADD PRIMARY KEY (`id_permiso`),
  ADD UNIQUE KEY `nombre_permiso` (`nombre_permiso`);

--
-- Indices de la tabla `poliza`
--
ALTER TABLE `poliza`
  ADD PRIMARY KEY (`id_poliza`),
  ADD UNIQUE KEY `numero_poliza` (`numero_poliza`),
  ADD KEY `fk_poliza_cliente` (`id_cliente`),
  ADD KEY `fk_poliza_agente` (`cedula_agente`),
  ADD KEY `fk_poliza_tipo` (`id_tipo_poliza`);

--
-- Indices de la tabla `poliza_cobertura`
--
ALTER TABLE `poliza_cobertura`
  ADD PRIMARY KEY (`id_poliza`,`id_cobertura`),
  ADD KEY `fk_pc_cobertura` (`id_cobertura`);

--
-- Indices de la tabla `poliza_cuota`
--
ALTER TABLE `poliza_cuota`
  ADD PRIMARY KEY (`id_cuota`),
  ADD UNIQUE KEY `uq_poliza_cuota` (`id_poliza`,`numero_cuota`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `siniestro`
--
ALTER TABLE `siniestro`
  ADD PRIMARY KEY (`id_siniestro`),
  ADD UNIQUE KEY `numero_siniestro` (`numero_siniestro`),
  ADD KEY `fk_siniestro_poliza` (`id_poliza`),
  ADD KEY `fk_siniestro_agente` (`cedula_agente_gestion`);

--
-- Indices de la tabla `tipo_poliza`
--
ALTER TABLE `tipo_poliza`
  ADD PRIMARY KEY (`id_tipo_poliza`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `fk_tipo_categoria` (`id_categoria`);

--
-- Indices de la tabla `tipo_poliza_cobertura`
--
ALTER TABLE `tipo_poliza_cobertura`
  ADD PRIMARY KEY (`id_tipo_poliza`,`id_cobertura`),
  ADD KEY `fk_tpc_cobertura` (`id_cobertura`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`cedula`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asegurado`
--
ALTER TABLE `asegurado`
  MODIFY `id_asegurado` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `categoria_poliza`
--
ALTER TABLE `categoria_poliza`
  MODIFY `id_categoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `cobertura`
--
ALTER TABLE `cobertura`
  MODIFY `id_cobertura` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `permiso`
--
ALTER TABLE `permiso`
  MODIFY `id_permiso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `poliza`
--
ALTER TABLE `poliza`
  MODIFY `id_poliza` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT de la tabla `poliza_cuota`
--
ALTER TABLE `poliza_cuota`
  MODIFY `id_cuota` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `siniestro`
--
ALTER TABLE `siniestro`
  MODIFY `id_siniestro` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2002;

--
-- AUTO_INCREMENT de la tabla `tipo_poliza`
--
ALTER TABLE `tipo_poliza`
  MODIFY `id_tipo_poliza` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD CONSTRAINT `fk_admin_usuario` FOREIGN KEY (`cedula_admin`) REFERENCES `usuario` (`cedula`) ON DELETE CASCADE;

--
-- Filtros para la tabla `agente`
--
ALTER TABLE `agente`
  ADD CONSTRAINT `fk_agente_usuario` FOREIGN KEY (`cedula_agente`) REFERENCES `usuario` (`cedula`) ON DELETE CASCADE;

--
-- Filtros para la tabla `agente_permiso`
--
ALTER TABLE `agente_permiso`
  ADD CONSTRAINT `fk_cedula_agente_permiso` FOREIGN KEY (`cedula_agente`) REFERENCES `usuario` (`cedula`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permiso` (`id_permiso`) ON DELETE CASCADE;

--
-- Filtros para la tabla `asegurado`
--
ALTER TABLE `asegurado`
  ADD CONSTRAINT `fk_asegurado_poliza` FOREIGN KEY (`id_poliza`) REFERENCES `poliza` (`id_poliza`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `fk_cedula_asegurado` FOREIGN KEY (`cedula_asegurado`) REFERENCES `usuario` (`cedula`);

--
-- Filtros para la tabla `detalle_poliza`
--
ALTER TABLE `detalle_poliza`
  ADD CONSTRAINT `fk_detalle_poliza_poliza` FOREIGN KEY (`id_poliza`) REFERENCES `poliza` (`id_poliza`) ON DELETE CASCADE;

--
-- Filtros para la tabla `poliza`
--
ALTER TABLE `poliza`
  ADD CONSTRAINT `fk_poliza_agente` FOREIGN KEY (`cedula_agente`) REFERENCES `usuario` (`cedula`),
  ADD CONSTRAINT `fk_poliza_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`),
  ADD CONSTRAINT `fk_poliza_tipo` FOREIGN KEY (`id_tipo_poliza`) REFERENCES `tipo_poliza` (`id_tipo_poliza`);

--
-- Filtros para la tabla `poliza_cobertura`
--
ALTER TABLE `poliza_cobertura`
  ADD CONSTRAINT `fk_pc_cobertura` FOREIGN KEY (`id_cobertura`) REFERENCES `cobertura` (`id_cobertura`),
  ADD CONSTRAINT `fk_pc_poliza` FOREIGN KEY (`id_poliza`) REFERENCES `poliza` (`id_poliza`) ON DELETE CASCADE;

--
-- Filtros para la tabla `poliza_cuota`
--
ALTER TABLE `poliza_cuota`
  ADD CONSTRAINT `fk_poliza_cuota_poliza` FOREIGN KEY (`id_poliza`) REFERENCES `poliza` (`id_poliza`) ON DELETE CASCADE;

--
-- Filtros para la tabla `siniestro`
--
ALTER TABLE `siniestro`
  ADD CONSTRAINT `fk_siniestro_agente` FOREIGN KEY (`cedula_agente_gestion`) REFERENCES `usuario` (`cedula`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_siniestro_poliza` FOREIGN KEY (`id_poliza`) REFERENCES `poliza` (`id_poliza`);

--
-- Filtros para la tabla `tipo_poliza`
--
ALTER TABLE `tipo_poliza`
  ADD CONSTRAINT `fk_tipo_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_poliza` (`id_categoria`);

--
-- Filtros para la tabla `tipo_poliza_cobertura`
--
ALTER TABLE `tipo_poliza_cobertura`
  ADD CONSTRAINT `fk_tpc_cobertura` FOREIGN KEY (`id_cobertura`) REFERENCES `cobertura` (`id_cobertura`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tpc_tipo` FOREIGN KEY (`id_tipo_poliza`) REFERENCES `tipo_poliza` (`id_tipo_poliza`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
