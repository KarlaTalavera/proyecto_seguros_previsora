-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-12-2025 a las 01:06:50
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
('V12345678', 18, 1),
('V12345678', 19, 1);

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
(106, '2025-12-02', '2025-12-06', 5000.00, 5, 1000.00, '2025-12-10', 'MENSUAL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id_notificacion` int(10) UNSIGNED NOT NULL,
  `cedula_destino` varchar(20) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('info','warning','success','danger','primary') NOT NULL DEFAULT 'info',
  `enlace` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(19, 'solicitud_gestionar', 'Coordinar solicitudes de pólizas y reportes de siniestros');

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
(102, 'POL-1003', 'ELIMINADA', 1, 'V12345678', 1),
(103, 'POL-1004', 'ELIMINADA', 2, 'V12345678', 3),
(104, 'POL-1005', 'ELIMINADA', 1, 'V12345678', 8),
(105, 'POL-1006', 'ELIMINADA', 2, 'V12345678', 9),
(106, 'POL-1007', 'ACTIVA', 1, 'V26260313', 5);

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
(106, 1),
(106, 2),
(106, 3);

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
(12, 106, 1, '2025-12-10', 1000.00, NULL, 200.00, 'PENDIENTE'),
(13, 106, 2, '2026-01-10', 1000.00, NULL, NULL, 'PENDIENTE'),
(14, 106, 3, '2026-02-10', 1000.00, NULL, NULL, 'PENDIENTE'),
(15, 106, 4, '2026-03-10', 1000.00, NULL, NULL, 'PENDIENTE'),
(16, 106, 5, '2026-04-10', 1000.00, NULL, NULL, 'PENDIENTE');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporte_pago_cuota`
--

CREATE TABLE `reporte_pago_cuota` (
  `id_reporte` int(10) UNSIGNED NOT NULL,
  `id_cuota` int(10) UNSIGNED NOT NULL,
  `id_poliza` int(10) UNSIGNED NOT NULL,
  `reportado_por` varchar(20) NOT NULL,
  `monto_reportado` decimal(12,2) NOT NULL,
  `referencia_pago` varchar(100) NOT NULL,
  `ruta_comprobante` varchar(255) NOT NULL,
  `nota_cliente` text DEFAULT NULL,
  `estado` enum('PENDIENTE','APROBADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
  `motivo_rechazo` text DEFAULT NULL,
  `fecha_reporte` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `revisado_por` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reporte_pago_cuota`
--

INSERT INTO `reporte_pago_cuota` (`id_reporte`, `id_cuota`, `id_poliza`, `reportado_por`, `monto_reportado`, `referencia_pago`, `ruta_comprobante`, `nota_cliente`, `estado`, `motivo_rechazo`, `fecha_reporte`, `fecha_revision`, `revisado_por`) VALUES
(1, 12, 106, 'V20000001', 200.00, '0234', 'assets/comprobantes_pagos/cmp_1765342264_274131717b.jpg', 'no pude pagal a tiempo peldon', 'APROBADO', NULL, '2025-12-10 04:51:04', '2025-12-10 04:52:41', 'V31843813');

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
(36, 'S-202511-P105-3', '2025-11-10 14:00:00', 'Siniestro prueba (mes 0) P105', 900.00, 'ABIERTO', 105, 'V12345678');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_poliza`
--

CREATE TABLE `solicitud_poliza` (
  `id_solicitud` int(10) UNSIGNED NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `cedula_cliente` varchar(20) NOT NULL,
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `id_tipo_poliza` int(10) UNSIGNED NOT NULL,
  `descripcion` text DEFAULT NULL,
  `contacto_preferido` varchar(255) DEFAULT NULL,
  `estado` enum('EN_REVISION','CONTACTADO','EN_PROCESO','APROBADO','RECHAZADO','CANCELADO') NOT NULL DEFAULT 'EN_REVISION',
  `cedula_agente_asignado` varchar(20) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `nota_interna` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitud_poliza`
--

INSERT INTO `solicitud_poliza` (`id_solicitud`, `id_cliente`, `cedula_cliente`, `id_categoria`, `id_tipo_poliza`, `descripcion`, `contacto_preferido`, `estado`, `cedula_agente_asignado`, `fecha_creacion`, `fecha_actualizacion`, `nota_interna`) VALUES
(1, 1, 'V20000001', 1, 1, 'Cobertura complementaria para viajes frecuentes.', 'juan.perez@example.com', 'EN_REVISION', 'V26260313', '2025-11-20 18:30:00', '2025-12-03 23:49:25', NULL),
(2, 2, 'V20000002', 3, 6, 'Actualizar póliza de comercio con cobertura robo.', '0414-7654321', 'CONTACTADO', 'V12345678', '2025-11-25 13:10:00', '2025-12-03 23:47:51', 'Cliente contactado, espera cotización.'),
(0, 1, 'V20000001', 3, 8, 'necesito un pocoe vainas, por ejemplo, mas amigos, que el dolar baje y que las clases terminen, pero por ahora me conformo con tener una poliza que cubra los gastos en caso de que mi casa se prenda candela oh no mi casa tiooo xdxdxdxd', '0412-1365498', 'EN_REVISION', 'V26260313', '2025-12-03 23:08:50', '2025-12-03 23:08:50', NULL),
(0, 1, 'V20000001', 2, 5, 'Ejemplo', 'strb2006@gmail.com', 'EN_REVISION', 'V26260313', '2025-12-13 22:29:00', '2025-12-13 22:29:00', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_siniestro`
--

CREATE TABLE `solicitud_siniestro` (
  `id_solicitud` int(10) UNSIGNED NOT NULL,
  `id_poliza` int(10) UNSIGNED NOT NULL,
  `cedula_cliente` varchar(20) NOT NULL,
  `tipo_incidente` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_incidente` date NOT NULL,
  `lugar_incidente` varchar(255) DEFAULT NULL,
  `estado` enum('EN_REVISION','CITA_PENDIENTE','EN_GESTION','ESCALADO','CERRADO','CANCELADO') NOT NULL DEFAULT 'EN_REVISION',
  `cedula_agente_asignado` varchar(20) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `nota_interna` text DEFAULT NULL,
  `fecha_cita` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitud_siniestro`
--

INSERT INTO `solicitud_siniestro` (`id_solicitud`, `id_poliza`, `cedula_cliente`, `tipo_incidente`, `descripcion`, `fecha_incidente`, `lugar_incidente`, `estado`, `cedula_agente_asignado`, `fecha_creacion`, `fecha_actualizacion`, `nota_interna`, `fecha_cita`) VALUES
(1, 100, 'V20000001', 'Choque frontal leve', 'Cliente solicita evaluación de daños en parachoques.', '2025-11-18', 'Caracas - Av. Libertador', 'CITA_PENDIENTE', 'V12345678', '2025-11-19 19:45:00', '2025-12-03 23:46:38', 'Coordinar perito para el 22/11.', '2025-11-22 10:30:00'),
(2, 105, 'V20000002', 'Daño por agua', 'Inundación parcial del local comercial tras lluvia.', '2025-11-22', 'Valencia - Av. Bolívar', 'EN_GESTION', 'V12345678', '2025-11-23 12:15:00', '2025-12-13 23:14:24', NULL, NULL);

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
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `fk_notificacion_usuario` (`cedula_destino`),
  ADD KEY `idx_cedula_destino` (`cedula_destino`),
  ADD KEY `idx_leida` (`leida`),
  ADD KEY `idx_fecha_creacion` (`fecha_creacion`),
  ADD KEY `idx_tipo` (`tipo`);

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
-- Indices de la tabla `reporte_pago_cuota`
--
ALTER TABLE `reporte_pago_cuota`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_rpc_cuota` (`id_cuota`),
  ADD KEY `fk_rpc_poliza` (`id_poliza`),
  ADD KEY `fk_rpc_reportado_por` (`reportado_por`),
  ADD KEY `fk_rpc_revisado_por` (`revisado_por`),
  ADD KEY `idx_rpc_estado` (`estado`),
  ADD KEY `idx_rpc_fecha` (`fecha_reporte`);

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
  MODIFY `id_asegurado` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categoria_poliza`
--
ALTER TABLE `categoria_poliza`
  MODIFY `id_categoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cobertura`
--
ALTER TABLE `cobertura`
  MODIFY `id_cobertura` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id_notificacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id_cuota` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `reporte_pago_cuota`
--
ALTER TABLE `reporte_pago_cuota`
  MODIFY `id_reporte` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id_tipo_poliza` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `fk_notificacion_usuario` FOREIGN KEY (`cedula_destino`) REFERENCES `usuario` (`cedula`) ON DELETE CASCADE;

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
-- Filtros para la tabla `reporte_pago_cuota`
--
ALTER TABLE `reporte_pago_cuota`
  ADD CONSTRAINT `fk_rpc_cuota` FOREIGN KEY (`id_cuota`) REFERENCES `poliza_cuota` (`id_cuota`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rpc_poliza` FOREIGN KEY (`id_poliza`) REFERENCES `poliza` (`id_poliza`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rpc_reportado_por` FOREIGN KEY (`reportado_por`) REFERENCES `usuario` (`cedula`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rpc_revisado_por` FOREIGN KEY (`revisado_por`) REFERENCES `usuario` (`cedula`) ON DELETE SET NULL;

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
