-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-05-2026 a las 16:29:02
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
-- Base de datos: `metricas_sistema`
--
CREATE DATABASE IF NOT EXISTS `metricas_sistema` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `metricas_sistema`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `ultimo_uso` datetime DEFAULT NULL,
  `total_usos` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas`
--

CREATE TABLE `areas` (
  `id` int(11) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#3b82f6',
  `icono` varchar(50) DEFAULT 'chart-bar',
  `activo` tinyint(1) DEFAULT 1,
  `orden` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `departamento_id` int(11) DEFAULT NULL,
  `accion` enum('view','create','update','delete','login','logout') NOT NULL,
  `tabla_afectada` varchar(100) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios_metricas`
--

CREATE TABLE `comentarios_metricas` (
  `id` int(11) NOT NULL,
  `valor_metrica_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_graficos`
--

CREATE TABLE `configuracion_graficos` (
  `id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `configuracion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`configuracion`)),
  `grid_x` int(11) DEFAULT 0,
  `grid_y` int(11) DEFAULT 0,
  `grid_w` int(11) DEFAULT 4,
  `grid_h` int(11) DEFAULT 3,
  `visible_para` enum('admin','viewer','todos') DEFAULT 'todos',
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('agencia','corporativo','global') DEFAULT 'corporativo',
  `color` varchar(7) DEFAULT '#3b82f6',
  `icono` varchar(50) DEFAULT 'building',
  `activo` tinyint(1) DEFAULT 1,
  `orden` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id`, `nombre`, `descripcion`, `tipo`, `color`, `icono`, `activo`, `orden`, `created_at`, `updated_at`) VALUES
(3, 'Global', 'Métricas consolidadas de toda la organización', 'global', '#8b5cf6', 'world', 1, 999, '2026-04-23 23:53:45', '2026-04-28 16:56:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_actividad`
--

CREATE TABLE `log_actividad` (
  `id` bigint(20) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(100) DEFAULT NULL,
  `tabla_afectada` varchar(100) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metas_metricas`
--

CREATE TABLE `metas_metricas` (
  `id` int(11) NOT NULL,
  `metrica_id` int(11) NOT NULL,
  `tipo_meta` enum('mensual','anual') DEFAULT 'mensual',
  `ejercicio` int(11) DEFAULT NULL,
  `periodo_id` int(11) DEFAULT NULL,
  `valor_objetivo` decimal(10,2) NOT NULL,
  `tipo_comparacion` enum('mayor_igual','menor_igual','igual','rango') DEFAULT 'mayor_igual',
  `valor_min` decimal(10,2) DEFAULT NULL,
  `valor_max` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metricas`
--

CREATE TABLE `metricas` (
  `id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tiene_meta` tinyint(1) DEFAULT 0,
  `unidad` varchar(50) DEFAULT NULL,
  `tipo_valor` enum('numero','porcentaje','tiempo','decimal') DEFAULT 'numero',
  `icono` varchar(50) DEFAULT 'chart-line',
  `es_calculada` tinyint(1) DEFAULT 0,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metricas_componentes`
--

CREATE TABLE `metricas_componentes` (
  `id` int(11) NOT NULL,
  `metrica_calculada_id` int(11) NOT NULL,
  `metrica_componente_id` int(11) NOT NULL,
  `operacion` enum('suma','resta','promedio') DEFAULT 'suma',
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos`
--

CREATE TABLE `periodos` (
  `id` int(11) NOT NULL,
  `ejercicio` int(11) NOT NULL,
  `periodo` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL,
  `periodo_id` int(11) DEFAULT NULL COMMENT 'NULL para reportes anuales',
  `anio` int(11) NOT NULL COMMENT 'A├▒o del reporte (2026, 2027, etc)',
  `tipo_reporte` enum('mensual','trimestral','semestral','anual') NOT NULL DEFAULT 'mensual',
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL COMMENT 'Descripci├│n breve/resumen ejecutivo',
  `contenido` longtext NOT NULL COMMENT 'HTML generado por TinyMCE',
  `estado` enum('borrador','revision','publicado','archivado') NOT NULL DEFAULT 'borrador',
  `fecha_publicacion` datetime DEFAULT NULL,
  `version` int(11) DEFAULT 1,
  `usuario_creacion_id` int(11) NOT NULL,
  `usuario_modificacion_id` int(11) DEFAULT NULL,
  `usuario_publicacion_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_adjuntos`
--

CREATE TABLE `reportes_adjuntos` (
  `id` int(11) NOT NULL,
  `reporte_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `archivo_path` varchar(500) NOT NULL,
  `tipo_mime` varchar(100) NOT NULL,
  `tamano_bytes` int(11) NOT NULL,
  `usuario_subida_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_comentarios`
--

CREATE TABLE `reportes_comentarios` (
  `id` int(11) NOT NULL,
  `reporte_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `estado_reporte_momento` enum('borrador','revision','publicado','archivado') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_graficos`
--

CREATE TABLE `reportes_graficos` (
  `id` int(11) NOT NULL,
  `reporte_id` int(11) NOT NULL,
  `grafico_id` int(11) NOT NULL COMMENT 'ID del gr├ífico original en configuracion_graficos',
  `imagen_path` varchar(500) NOT NULL COMMENT 'Ruta de la captura PNG del gr├ífico',
  `imagen_thumbnail` varchar(500) DEFAULT NULL COMMENT 'Miniatura 150x150 para galer├¡a',
  `imagen_width` int(11) DEFAULT NULL COMMENT 'Ancho original en px',
  `imagen_height` int(11) DEFAULT NULL COMMENT 'Alto original en px',
  `periodo_captura_id` int(11) DEFAULT NULL COMMENT 'Per├¡odo cuando se captur├│',
  `titulo_grafico` varchar(255) NOT NULL COMMENT 'T├¡tulo del gr├ífico en ese momento',
  `posicion_en_reporte` int(11) DEFAULT 1 COMMENT 'Orden de aparici├│n (1, 2, 3...)',
  `alineacion` enum('left','center','right','justify') DEFAULT 'center',
  `ajuste_texto` enum('inline','wrap','square','tight','through','top-bottom','behind','front') DEFAULT 'inline',
  `ancho_display` int(11) DEFAULT NULL COMMENT 'Ancho en px cuando se inserta (puede ser diferente al original)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_versiones`
--

CREATE TABLE `reportes_versiones` (
  `id` int(11) NOT NULL,
  `reporte_id` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `contenido` longtext NOT NULL COMMENT 'Copia del HTML en esa versi├│n',
  `usuario_id` int(11) NOT NULL,
  `nota_version` text DEFAULT NULL COMMENT 'Nota explicando el cambio',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `timeline_eventos`
--

CREATE TABLE `timeline_eventos` (
  `id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('release','incidente','mejora','otro') DEFAULT 'otro',
  `fecha_evento` date NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rol` enum('super_admin','dept_admin','area_admin','dept_viewer') NOT NULL DEFAULT 'dept_viewer',
  `departamento_id` int(11) DEFAULT NULL,
  `area_id` int(11) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `tema` enum('light','dark') DEFAULT 'light',
  `avatar_icono` varchar(50) DEFAULT 'user',
  `avatar_color` varchar(7) DEFAULT '#3b82f6',
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `nombre`, `email`, `rol`, `departamento_id`, `area_id`, `foto_perfil`, `tema`, `avatar_icono`, `avatar_color`, `activo`, `ultimo_acceso`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrador', 'superadmin@metricas.com', 'super_admin', NULL, NULL, NULL, 'light', 'user-circle', '#1756ba', 1, '2026-04-30 15:09:40', '2026-04-20 15:30:21', '2026-04-30 21:55:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `valores_metricas`
--

CREATE TABLE `valores_metricas` (
  `id` int(11) NOT NULL,
  `metrica_id` int(11) NOT NULL,
  `periodo_id` int(11) NOT NULL,
  `valor_numero` int(11) DEFAULT NULL,
  `valor_decimal` decimal(10,2) DEFAULT NULL,
  `nota` text DEFAULT NULL,
  `usuario_registro_id` int(11) DEFAULT NULL,
  `usuario_modificacion_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_metricas_completas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_metricas_completas` (
`id` int(11)
,`nombre` varchar(100)
,`slug` varchar(100)
,`descripcion` text
,`unidad` varchar(50)
,`tipo_valor` enum('numero','porcentaje','tiempo','decimal')
,`es_calculada` tinyint(1)
,`area_id` int(11)
,`area_nombre` varchar(100)
,`area_slug` varchar(100)
,`departamento_id` int(11)
,`departamento_nombre` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_usuarios_completos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_usuarios_completos` (
`id` int(11)
,`username` varchar(50)
,`nombre` varchar(100)
,`email` varchar(100)
,`rol` enum('super_admin','dept_admin','area_admin','dept_viewer')
,`activo` tinyint(1)
,`ultimo_acceso` datetime
,`departamento_id` int(11)
,`departamento_nombre` varchar(100)
,`area_id` int(11)
,`area_nombre` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_metricas_completas`
--
DROP TABLE IF EXISTS `v_metricas_completas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_metricas_completas`  AS SELECT `m`.`id` AS `id`, `m`.`nombre` AS `nombre`, `m`.`slug` AS `slug`, `m`.`descripcion` AS `descripcion`, `m`.`unidad` AS `unidad`, `m`.`tipo_valor` AS `tipo_valor`, `m`.`es_calculada` AS `es_calculada`, `a`.`id` AS `area_id`, `a`.`nombre` AS `area_nombre`, `a`.`slug` AS `area_slug`, `d`.`id` AS `departamento_id`, `d`.`nombre` AS `departamento_nombre` FROM ((`metricas` `m` join `areas` `a` on(`m`.`area_id` = `a`.`id`)) join `departamentos` `d` on(`a`.`departamento_id` = `d`.`id`)) WHERE `m`.`activo` = 1 AND `a`.`activo` = 1 AND `d`.`activo` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_usuarios_completos`
--
DROP TABLE IF EXISTS `v_usuarios_completos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_usuarios_completos`  AS SELECT `u`.`id` AS `id`, `u`.`username` AS `username`, `u`.`nombre` AS `nombre`, `u`.`email` AS `email`, `u`.`rol` AS `rol`, `u`.`activo` AS `activo`, `u`.`ultimo_acceso` AS `ultimo_acceso`, `d`.`id` AS `departamento_id`, `d`.`nombre` AS `departamento_nombre`, `a`.`id` AS `area_id`, `a`.`nombre` AS `area_nombre` FROM ((`usuarios` `u` left join `departamentos` `d` on(`u`.`departamento_id` = `d`.`id`)) left join `areas` `a` on(`u`.`area_id` = `a`.`id`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_departamento` (`departamento_id`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indices de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_departamento` (`departamento_id`),
  ADD KEY `idx_accion` (`accion`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_tabla` (`tabla_afectada`);

--
-- Indices de la tabla `comentarios_metricas`
--
ALTER TABLE `comentarios_metricas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_valor_metrica` (`valor_metrica_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indices de la tabla `configuracion_graficos`
--
ALTER TABLE `configuracion_graficos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_area` (`area_id`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_orden` (`orden`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indices de la tabla `log_actividad`
--
ALTER TABLE `log_actividad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_tabla` (`tabla_afectada`);

--
-- Indices de la tabla `metas_metricas`
--
ALTER TABLE `metas_metricas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_metrica_tipo_periodo` (`metrica_id`,`tipo_meta`,`ejercicio`,`periodo_id`),
  ADD KEY `idx_metrica` (`metrica_id`),
  ADD KEY `idx_periodo` (`periodo_id`),
  ADD KEY `idx_ejercicio` (`ejercicio`);

--
-- Indices de la tabla `metricas`
--
ALTER TABLE `metricas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_area_slug` (`area_id`,`slug`),
  ADD KEY `idx_area` (`area_id`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_es_calculada` (`es_calculada`);

--
-- Indices de la tabla `metricas_componentes`
--
ALTER TABLE `metricas_componentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_calculada` (`metrica_calculada_id`),
  ADD KEY `idx_componente` (`metrica_componente_id`);

--
-- Indices de la tabla `periodos`
--
ALTER TABLE `periodos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ejercicio_periodo` (`ejercicio`,`periodo`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_ejercicio` (`ejercicio`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_reporte_unico` (`area_id`,`periodo_id`,`anio`,`tipo_reporte`,`version`),
  ADD KEY `usuario_creacion_id` (`usuario_creacion_id`),
  ADD KEY `usuario_modificacion_id` (`usuario_modificacion_id`),
  ADD KEY `usuario_publicacion_id` (`usuario_publicacion_id`),
  ADD KEY `idx_area` (`area_id`),
  ADD KEY `idx_periodo` (`periodo_id`),
  ADD KEY `idx_anio` (`anio`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_tipo` (`tipo_reporte`),
  ADD KEY `idx_fecha_pub` (`fecha_publicacion`);

--
-- Indices de la tabla `reportes_adjuntos`
--
ALTER TABLE `reportes_adjuntos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_subida_id` (`usuario_subida_id`),
  ADD KEY `idx_reporte` (`reporte_id`);

--
-- Indices de la tabla `reportes_comentarios`
--
ALTER TABLE `reportes_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reporte` (`reporte_id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Indices de la tabla `reportes_graficos`
--
ALTER TABLE `reportes_graficos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `periodo_captura_id` (`periodo_captura_id`),
  ADD KEY `idx_reporte` (`reporte_id`),
  ADD KEY `idx_grafico` (`grafico_id`),
  ADD KEY `idx_posicion` (`posicion_en_reporte`);

--
-- Indices de la tabla `reportes_versiones`
--
ALTER TABLE `reportes_versiones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_reporte_version` (`reporte_id`,`version`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_reporte` (`reporte_id`),
  ADD KEY `idx_version` (`version`);

--
-- Indices de la tabla `timeline_eventos`
--
ALTER TABLE `timeline_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_area` (`area_id`),
  ADD KEY `idx_fecha` (`fecha_evento`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `area_id` (`area_id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rol` (`rol`),
  ADD KEY `idx_departamento` (`departamento_id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `valores_metricas`
--
ALTER TABLE `valores_metricas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_metrica_periodo` (`metrica_id`,`periodo_id`),
  ADD KEY `usuario_registro_id` (`usuario_registro_id`),
  ADD KEY `usuario_modificacion_id` (`usuario_modificacion_id`),
  ADD KEY `idx_metrica` (`metrica_id`),
  ADD KEY `idx_periodo` (`periodo_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comentarios_metricas`
--
ALTER TABLE `comentarios_metricas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion_graficos`
--
ALTER TABLE `configuracion_graficos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `log_actividad`
--
ALTER TABLE `log_actividad`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `metas_metricas`
--
ALTER TABLE `metas_metricas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `metricas`
--
ALTER TABLE `metricas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `metricas_componentes`
--
ALTER TABLE `metricas_componentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `periodos`
--
ALTER TABLE `periodos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes_adjuntos`
--
ALTER TABLE `reportes_adjuntos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes_comentarios`
--
ALTER TABLE `reportes_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes_graficos`
--
ALTER TABLE `reportes_graficos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes_versiones`
--
ALTER TABLE `reportes_versiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `timeline_eventos`
--
ALTER TABLE `timeline_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `valores_metricas`
--
ALTER TABLE `valores_metricas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD CONSTRAINT `api_tokens_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `areas`
--
ALTER TABLE `areas`
  ADD CONSTRAINT `areas_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`);

--
-- Filtros para la tabla `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `audit_log_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `comentarios_metricas`
--
ALTER TABLE `comentarios_metricas`
  ADD CONSTRAINT `comentarios_metricas_ibfk_1` FOREIGN KEY (`valor_metrica_id`) REFERENCES `valores_metricas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentarios_metricas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `configuracion_graficos`
--
ALTER TABLE `configuracion_graficos`
  ADD CONSTRAINT `configuracion_graficos_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `log_actividad`
--
ALTER TABLE `log_actividad`
  ADD CONSTRAINT `log_actividad_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `metas_metricas`
--
ALTER TABLE `metas_metricas`
  ADD CONSTRAINT `metas_metricas_ibfk_1` FOREIGN KEY (`metrica_id`) REFERENCES `metricas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `metas_metricas_ibfk_2` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `metricas`
--
ALTER TABLE `metricas`
  ADD CONSTRAINT `metricas_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `metricas_componentes`
--
ALTER TABLE `metricas_componentes`
  ADD CONSTRAINT `metricas_componentes_ibfk_1` FOREIGN KEY (`metrica_calculada_id`) REFERENCES `metricas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `metricas_componentes_ibfk_2` FOREIGN KEY (`metrica_componente_id`) REFERENCES `metricas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`),
  ADD CONSTRAINT `reportes_ibfk_2` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reportes_ibfk_3` FOREIGN KEY (`usuario_creacion_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `reportes_ibfk_4` FOREIGN KEY (`usuario_modificacion_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reportes_ibfk_5` FOREIGN KEY (`usuario_publicacion_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reportes_adjuntos`
--
ALTER TABLE `reportes_adjuntos`
  ADD CONSTRAINT `reportes_adjuntos_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reportes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_adjuntos_ibfk_2` FOREIGN KEY (`usuario_subida_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `reportes_comentarios`
--
ALTER TABLE `reportes_comentarios`
  ADD CONSTRAINT `reportes_comentarios_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reportes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_comentarios_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes_graficos`
--
ALTER TABLE `reportes_graficos`
  ADD CONSTRAINT `reportes_graficos_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reportes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_graficos_ibfk_2` FOREIGN KEY (`grafico_id`) REFERENCES `configuracion_graficos` (`id`),
  ADD CONSTRAINT `reportes_graficos_ibfk_3` FOREIGN KEY (`periodo_captura_id`) REFERENCES `periodos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reportes_versiones`
--
ALTER TABLE `reportes_versiones`
  ADD CONSTRAINT `reportes_versiones_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reportes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_versiones_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `timeline_eventos`
--
ALTER TABLE `timeline_eventos`
  ADD CONSTRAINT `timeline_eventos_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timeline_eventos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `valores_metricas`
--
ALTER TABLE `valores_metricas`
  ADD CONSTRAINT `valores_metricas_ibfk_1` FOREIGN KEY (`metrica_id`) REFERENCES `metricas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `valores_metricas_ibfk_2` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `valores_metricas_ibfk_3` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `valores_metricas_ibfk_4` FOREIGN KEY (`usuario_modificacion_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
