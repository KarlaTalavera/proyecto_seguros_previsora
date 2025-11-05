-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-11-2025 a las 14:41:29
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
-- Estructura de tabla para la tabla `agente_permiso`
--

CREATE TABLE `agente_permiso` (
  `cedula_agente` varchar(20) NOT NULL,
  `id_permiso` int(10) UNSIGNED NOT NULL,
  `tiene_permiso` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_poliza`
--

CREATE TABLE `categoria_poliza` (
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `cedula_asegurado` varchar(20) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cobertura`
--

CREATE TABLE `cobertura` (
  `id_cobertura` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `detalle` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_poliza`
--

CREATE TABLE `detalle_poliza` (
  `id_poliza` int(10) UNSIGNED NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `monto_prima` decimal(10,2) NOT NULL
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
(15, 'reportes_generar_clientes', 'Permite generar reportes de clientes.');

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `poliza_cobertura`
--

CREATE TABLE `poliza_cobertura` (
  `id_poliza` int(10) UNSIGNED NOT NULL,
  `id_cobertura` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_poliza`
--

CREATE TABLE `tipo_poliza` (
  `id_tipo_poliza` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_categoria` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `cedula` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  `id_rol` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`cedula`, `nombre`, `apellido`, `email`, `password_hash`, `telefono`, `fecha_creacion`, `activo`, `id_rol`) VALUES
('V12345678', 'Santiago', 'Rodriguez', 'santi@previsora.com', '$2y$10$v8dGm8Hq4saG0j/5lVlKaOqITPTvNAwonfzRjWLanH3oZowlTeCly', '04247654321', '2025-11-04 01:17:40', 1, 2),
('V31843813', 'Karla', 'Talavera', 'admin@previsora.com', '$2y$10$xQNNf3KGSblr4UhPyxzmM.edawtvKfeb1t4xDk0K3K9r40GMDRQR2', '04121365498', '2025-11-04 01:17:40', 1, 1),
('V31894578', 'Sarai', 'Leon', 'saraleon030405@gmail.com', '$2y$10$oF2cREtn772dCHSCiUR7LuHKBGxLv83rE5edOAzRFkBsWUtsGfI9i', '04121122334', '2025-11-04 01:17:40', 1, 3);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `agente_permiso`
--
ALTER TABLE `agente_permiso`
  ADD PRIMARY KEY (`cedula_agente`,`id_permiso`),
  ADD KEY `fk_permiso` (`id_permiso`);

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
-- AUTO_INCREMENT de la tabla `categoria_poliza`
--
ALTER TABLE `categoria_poliza`
  MODIFY `id_categoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cobertura`
--
ALTER TABLE `cobertura`
  MODIFY `id_cobertura` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permiso`
--
ALTER TABLE `permiso`
  MODIFY `id_permiso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `poliza`
--
ALTER TABLE `poliza`
  MODIFY `id_poliza` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `siniestro`
--
ALTER TABLE `siniestro`
  MODIFY `id_siniestro` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_poliza`
--
ALTER TABLE `tipo_poliza`
  MODIFY `id_tipo_poliza` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `agente_permiso`
--
ALTER TABLE `agente_permiso`
  ADD CONSTRAINT `fk_cedula_agente_permiso` FOREIGN KEY (`cedula_agente`) REFERENCES `usuario` (`cedula`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permiso` (`id_permiso`) ON DELETE CASCADE;

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
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
