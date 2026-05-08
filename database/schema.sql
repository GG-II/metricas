-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: metricas_sistema
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `api_tokens`
--

DROP TABLE IF EXISTS `api_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `ultimo_uso` datetime DEFAULT NULL,
  `total_usos` int DEFAULT '0',
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_token` (`token`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_activo` (`activo`),
  CONSTRAINT `api_tokens_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_tokens`
--

LOCK TABLES `api_tokens` WRITE;
/*!40000 ALTER TABLE `api_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `departamento_id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#3b82f6',
  `icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'chart-bar',
  `activo` tinyint(1) DEFAULT '1',
  `orden` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_departamento` (`departamento_id`),
  KEY `idx_activo` (`activo`),
  KEY `idx_slug` (`slug`),
  CONSTRAINT `areas_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `areas`
--

LOCK TABLES `areas` WRITE;
/*!40000 ALTER TABLE `areas` DISABLE KEYS */;
INSERT INTO `areas` VALUES (1,13,'Fabrica de Software','fabrica-de-software','Desarrollo de apps','#000000','cube',1,1,'2026-05-06 00:14:42','2026-05-06 00:14:42'),(2,13,'Infraestructura','infraestructura','Gestión de servidores, redes, almacenamiento y disponibilidad de sistemas','#2d3034','network',1,2,'2026-05-07 14:07:27','2026-05-07 14:11:03'),(3,13,'Soporte Técnico','soporte-t-cnico','Atención y resolución de incidencias técnicas a usuarios y equipos','#272d38','tool',1,3,'2026-05-07 14:08:17','2026-05-07 14:11:15'),(4,13,'Ciberseguridad','ciberseguridad','Protección de sistemas, datos y redes frente a amenazas y vulnerabilidades','#2b2e34','shield',1,4,'2026-05-07 14:08:50','2026-05-07 14:11:25'),(5,13,'Medios Digitales','medios-digitales','Gestión de pagos y transacciones realizadas por aplicaciones y sitios web','#2d323e','device-tablet',1,5,'2026-05-07 14:09:32','2026-05-07 14:11:38');
/*!40000 ALTER TABLE `areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `departamento_id` int DEFAULT NULL,
  `accion` enum('view','create','update','delete','login','logout') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tabla_afectada` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registro_id` int DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_departamento` (`departamento_id`),
  KEY `idx_accion` (`accion`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_tabla` (`tabla_afectada`),
  CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `audit_log_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comentarios_metricas`
--

DROP TABLE IF EXISTS `comentarios_metricas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comentarios_metricas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `valor_metrica_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_valor_metrica` (`valor_metrica_id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `comentarios_metricas_ibfk_1` FOREIGN KEY (`valor_metrica_id`) REFERENCES `valores_metricas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comentarios_metricas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comentarios_metricas`
--

LOCK TABLES `comentarios_metricas` WRITE;
/*!40000 ALTER TABLE `comentarios_metricas` DISABLE KEYS */;
/*!40000 ALTER TABLE `comentarios_metricas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion_graficos`
--

DROP TABLE IF EXISTS `configuracion_graficos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion_graficos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `area_id` int NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuracion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `grid_x` int DEFAULT '0',
  `grid_y` int DEFAULT '0',
  `grid_w` int DEFAULT '4',
  `grid_h` int DEFAULT '3',
  `visible_para` enum('admin','viewer','todos') COLLATE utf8mb4_unicode_ci DEFAULT 'todos',
  `orden` int DEFAULT '0',
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_area` (`area_id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_activo` (`activo`),
  CONSTRAINT `configuracion_graficos_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `configuracion_graficos_chk_1` CHECK (json_valid(`configuracion`))
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion_graficos`
--

LOCK TABLES `configuracion_graficos` WRITE;
/*!40000 ALTER TABLE `configuracion_graficos` DISABLE KEYS */;
INSERT INTO `configuracion_graficos` VALUES (1,1,'donut','Proyectos en Desarrollo','{\"metricas\":[{\"id\":1,\"color\":\"#57c200\"},{\"id\":2,\"color\":\"#cd1818\"}],\"altura\":350,\"mostrar_porcentaje\":true}',0,0,4,6,'todos',0,1,'2026-05-07 14:59:37','2026-05-07 15:03:15'),(2,1,'comparison','Balance de Bugs (Calidad)','{\"metrica_1_id\":5,\"metrica_2_id\":4,\"color_1\":\"#3b82f6\",\"color_2\":\"#10b981\",\"periodos\":6,\"altura\":350,\"mostrar_valores\":true}',4,0,5,6,'todos',0,1,'2026-05-07 15:02:24','2026-05-08 16:53:32'),(3,1,'kpi_card','Releases a Producción','{\"metrica_id\":3,\"color\":\"#3b82f6\",\"mostrar_comparacion\":false}',9,3,3,3,'todos',0,1,'2026-05-07 15:02:46','2026-05-08 17:49:10'),(4,1,'kpi_card','Procesos Automatizados','{\"metrica_id\":6,\"color\":\"#3b82f6\",\"mostrar_comparacion\":false}',9,0,3,3,'todos',0,1,'2026-05-07 15:03:02','2026-05-08 17:49:06'),(5,2,'gauge','Disponibilidad de Sistemas (Uptime)','{\"metrica_id\":7,\"objetivo\":100,\"color_scheme\":\"green\",\"altura\":280,\"subtitulo\":\"\",\"mostrar_porcentaje\":true,\"animar\":true}',0,0,4,5,'todos',0,1,'2026-05-07 15:04:40','2026-05-08 17:38:29'),(6,2,'comparison','Gestión de Vulnerabilidades','{\"metrica_1_id\":9,\"metrica_2_id\":10,\"color_1\":\"#e85959\",\"color_2\":\"#10b981\",\"periodos\":6,\"altura\":350,\"mostrar_valores\":true}',4,0,5,6,'todos',0,1,'2026-05-07 15:05:51','2026-05-07 15:07:35'),(7,2,'kpi_card','Incidentes del Mes','{\"metrica_id\":8,\"color\":\"#31353c\",\"mostrar_comparacion\":false}',9,3,3,3,'todos',0,1,'2026-05-07 15:06:35','2026-05-08 17:49:22'),(8,2,'kpi_card','Ataques Bloqueados','{\"metrica_id\":11,\"color\":\"#3a3f47\",\"mostrar_comparacion\":false}',9,0,3,3,'todos',0,1,'2026-05-07 15:07:01','2026-05-08 17:49:27'),(10,3,'comparison','Flujo de Tickets','{\"metrica_1_id\":14,\"metrica_2_id\":15,\"color_1\":\"#3b82f6\",\"color_2\":\"#10b981\",\"periodos\":6,\"altura\":350,\"mostrar_valores\":true}',4,3,5,6,'todos',0,1,'2026-05-07 15:09:51','2026-05-07 15:12:39'),(11,3,'kpi_card','Tiempo de Resolución','{\"metrica_id\":17,\"color\":\"#3b82f6\",\"mostrar_comparacion\":false}',9,6,3,3,'todos',0,1,'2026-05-07 15:10:07','2026-05-08 17:49:40'),(12,3,'donut','Soporte a Agencias (Total)','{\"metricas\":[{\"id\":21,\"color\":\"#3b82f6\"},{\"id\":22,\"color\":\"#10b981\"},{\"id\":23,\"color\":\"#f59e0b\"}],\"altura\":350,\"mostrar_porcentaje\":true}',0,3,4,6,'todos',0,1,'2026-05-07 15:10:52','2026-05-07 15:12:34'),(13,3,'progress','Nivel de Servicio (SLA)','{\"metrica_id\":18,\"color\":\"#3b82f6\",\"objetivo\":100,\"altura\":40,\"mostrar_valor\":true}',0,0,9,3,'todos',0,1,'2026-05-07 15:11:05','2026-05-07 15:12:25'),(14,3,'kpi_card','Gestión de Equipos Instalados','{\"metrica_id\":20,\"color\":\"#3b82f6\",\"mostrar_comparacion\":false}',9,3,3,3,'todos',0,1,'2026-05-07 15:11:31','2026-05-08 17:49:37'),(15,3,'kpi_card','Gestión de Equipos Reparados','{\"metrica_id\":19,\"color\":\"#3b82f6\",\"mostrar_comparacion\":false}',9,0,3,3,'todos',0,1,'2026-05-07 15:11:51','2026-05-08 17:49:43'),(16,4,'progress','Avance de Políticas SGSI','{\"metrica_id\":25,\"color\":\"#3b82f6\",\"objetivo\":100,\"altura\":40,\"mostrar_valor\":true}',0,0,9,3,'todos',0,1,'2026-05-07 15:13:57','2026-05-08 17:46:36'),(17,4,'kpi_card','Análisis Proactivos','{\"metrica_id\":27,\"color\":\"#3b82f6\",\"mostrar_comparacion\":true}',9,6,3,3,'todos',0,1,'2026-05-07 15:14:07','2026-05-08 17:46:38'),(18,4,'kpi_card','Activos Críticos Gestionados','{\"metrica_id\":28,\"color\":\"#3b82f6\",\"mostrar_comparacion\":true}',9,3,3,3,'todos',0,1,'2026-05-07 15:14:17','2026-05-08 17:46:38'),(19,4,'bar','Gestión de Identidades por Mes','{\"metrica_id\":26,\"color\":\"#3b82f6\",\"periodos\":6,\"altura\":300,\"mostrar_valores\":true}',0,3,9,6,'todos',0,1,'2026-05-07 15:14:32','2026-05-08 17:46:36'),(20,5,'multi_bar','Monto Transaccionado por Canal (en Millones)','{\"metricas\":[{\"id\":33,\"color\":\"#3b82f6\"},{\"id\":32,\"color\":\"#10b981\"},{\"id\":34,\"color\":\"#f59e0b\"}],\"periodos\":6,\"altura\":400,\"mostrar_valores\":true}',4,0,5,7,'todos',0,1,'2026-05-07 15:16:09','2026-05-08 18:33:15'),(21,5,'kpi_card','Transacciones por fri','{\"metrica_id\":30,\"color\":\"#3b82f6\",\"mostrar_comparacion\":false}',9,6,3,3,'todos',0,1,'2026-05-07 15:16:27','2026-05-08 17:48:37'),(22,5,'kpi_card','Transacciones por CIC Virtual','{\"metrica_id\":29,\"color\":\"#3b82f6\",\"mostrar_comparacion\":false}',9,3,3,3,'todos',0,1,'2026-05-07 15:16:43','2026-05-08 17:48:19'),(23,5,'kpi_card','Transacciones Tarjetas de Débito','{\"metrica_id\":31,\"color\":\"#3b82f6\",\"mostrar_comparacion\":false}',9,0,3,3,'todos',0,1,'2026-05-07 15:16:54','2026-05-08 17:48:32'),(24,5,'donut','Market Share por Canal (Montos en Millones)','{\"metricas\":[{\"id\":33,\"color\":\"#3b82f6\"},{\"id\":32,\"color\":\"#10b981\"},{\"id\":34,\"color\":\"#f59e0b\"}],\"altura\":350,\"mostrar_porcentaje\":true}',0,0,4,6,'todos',0,1,'2026-05-07 15:17:27','2026-05-08 18:33:05'),(25,4,'kpi_card','Políticas Desarrolladas','{\"metrica_id\":35,\"color\":\"#43454c\",\"mostrar_comparacion\":false}',9,0,3,3,'todos',0,1,'2026-05-08 17:46:08','2026-05-08 17:46:38');
/*!40000 ALTER TABLE `configuracion_graficos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departamentos`
--

DROP TABLE IF EXISTS `departamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo` enum('agencia','corporativo','global') COLLATE utf8mb4_unicode_ci DEFAULT 'corporativo',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#3b82f6',
  `icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'building',
  `activo` tinyint(1) DEFAULT '1',
  `orden` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activo` (`activo`),
  KEY `idx_orden` (`orden`),
  KEY `idx_tipo` (`tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamentos`
--

LOCK TABLES `departamentos` WRITE;
/*!40000 ALTER TABLE `departamentos` DISABLE KEYS */;
INSERT INTO `departamentos` VALUES (3,'CIC RL','Métricas consolidadas de toda la organización','global','#8b5cf6','world',1,999,'2026-04-23 23:53:45','2026-05-04 22:06:24'),(13,'Departamento de TI','Áreas: \r\n- Soporte Técnico\r\n- Fabrica de Software\r\n- Infraestructura\r\n- Ciberseguridad','corporativo','#3b82f6','atom',1,1000,'2026-05-05 23:42:21','2026-05-05 23:48:35'),(14,'Departamento Financiero','Áreas: \r\n- Contabilidad\r\n- Tesorería\r\n- Medios de Pago','corporativo','#3b82f6','building',1,1001,'2026-05-05 23:43:53','2026-05-05 23:43:53'),(15,'Departamento de Talento Humano','Áreas\r\n- Contrataciones\r\n- Inducción','corporativo','#3b82f6','user',1,1002,'2026-05-05 23:45:08','2026-05-05 23:45:08'),(16,'Departamento de Negocios','Negocios','corporativo','#3b82f6','activity',1,1003,'2026-05-05 23:46:33','2026-05-05 23:46:33'),(17,'Departamento de Mercadeo','Áreas:\r\n- Marketing\r\n- Educación','corporativo','#3b82f6','brand-facebook',1,1004,'2026-05-05 23:47:47','2026-05-05 23:47:47'),(18,'Departamento Jurídico','Jurídico','corporativo','#3b82f6','building-bank',1,1005,'2026-05-05 23:48:25','2026-05-05 23:48:25'),(19,'Departamento de Cumplimiento','IVE','corporativo','#3b82f6','file-check',1,1006,'2026-05-05 23:50:00','2026-05-05 23:50:00'),(20,'Departamento de Auditoría Interna','Auditoría Interna','corporativo','#3b82f6','report',1,1007,'2026-05-05 23:51:24','2026-05-05 23:51:24'),(21,'Departamento de Créditos','Créditos','corporativo','#3b82f6','folders',1,1008,'2026-05-05 23:53:07','2026-05-05 23:53:07'),(22,'Agencia Central','Áreas\r\n- Cuentas Nuevas\r\n- Caja\r\n- Créditos\r\n- Cobradores\r\n- Valudadores\r\n- Asesores de Créditos\r\n- Captadores de Ahorros','agencia','#59b300','building-community',1,1009,'2026-05-05 23:55:44','2026-05-05 23:55:44'),(23,'Agencia Pradera','Áreas\r\n- Cuentas Nuevas\r\n- Caja\r\n- Créditos\r\n- Cobradores\r\n- Valudadores\r\n- Asesores de Créditos\r\n- Captadores de Ahorros','agencia','#59b300','building-community',1,1010,'2026-05-05 23:56:38','2026-05-05 23:57:23'),(24,'Agencia Jacaltenango','Áreas\r\n- Cuentas Nuevas\r\n- Caja\r\n- Créditos\r\n- Cobradores\r\n- Valudadores\r\n- Asesores de Créditos\r\n- Captadores de Ahorros','agencia','#59b300','building-community',1,1011,'2026-05-05 23:57:55','2026-05-06 00:01:30'),(25,'Agencia Zentro Plaza','Áreas\r\n- Cuentas Nuevas\r\n- Caja\r\n- Créditos\r\n- Cobradores\r\n- Valudadores\r\n- Asesores de Créditos\r\n- Captadores de Ahorros','agencia','#59b300','building-community',1,1012,'2026-05-05 23:58:27','2026-05-05 23:58:27'),(26,'Agencia La Democracia','Áreas\r\n- Cuentas Nuevas\r\n- Caja\r\n- Créditos\r\n- Cobradores\r\n- Valudadores\r\n- Asesores de Créditos\r\n- Captadores de Ahorros','agencia','#59b300','building-community',1,1013,'2026-05-05 23:59:02','2026-05-06 00:01:42'),(27,'Agencia Malacatancito','Áreas\r\n- Cuentas Nuevas\r\n- Caja\r\n- Créditos\r\n- Cobradores\r\n- Valudadores\r\n- Asesores de Créditos\r\n- Captadores de Ahorros','agencia','#59b300','building-community',1,1014,'2026-05-05 23:59:24','2026-05-06 00:01:51'),(28,'Agencia Chiantla','Áreas\r\n- Cuentas Nuevas\r\n- Caja\r\n- Créditos\r\n- Cobradores\r\n- Valudadores\r\n- Asesores de Créditos\r\n- Captadores de Ahorros','agencia','#59b300','building-community',1,1015,'2026-05-06 00:00:14','2026-05-06 00:01:58'),(29,'Agencia Aguacatán','Áreas\r\n- Cuentas Nuevas\r\n- Caja\r\n- Créditos\r\n- Cobradores\r\n- Valudadores\r\n- Asesores de Créditos\r\n- Captadores de Ahorros','agencia','#59b300','building-community',1,1016,'2026-05-06 00:00:43','2026-05-06 00:02:07'),(30,'Agencia Alturas Mall','Áreas\r\n- Cuentas Nuevas\r\n- Caja\r\n- Créditos\r\n- Cobradores\r\n- Valudadores\r\n- Asesores de Créditos\r\n- Captadores de Ahorros','agencia','#59b300','building-community',1,1017,'2026-05-06 00:01:18','2026-05-06 00:01:18');
/*!40000 ALTER TABLE `departamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_actividad`
--

DROP TABLE IF EXISTS `log_actividad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_actividad` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `accion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tabla_afectada` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registro_id` int DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_tabla` (`tabla_afectada`),
  CONSTRAINT `log_actividad_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_actividad`
--

LOCK TABLES `log_actividad` WRITE;
/*!40000 ALTER TABLE `log_actividad` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_actividad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `metas_metricas`
--

DROP TABLE IF EXISTS `metas_metricas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `metas_metricas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `metrica_id` int NOT NULL,
  `tipo_meta` enum('mensual','anual') COLLATE utf8mb4_unicode_ci DEFAULT 'mensual',
  `ejercicio` int DEFAULT NULL,
  `periodo_id` int DEFAULT NULL,
  `valor_objetivo` decimal(10,2) NOT NULL,
  `tipo_comparacion` enum('mayor_igual','menor_igual','igual','rango') COLLATE utf8mb4_unicode_ci DEFAULT 'mayor_igual',
  `valor_min` decimal(10,2) DEFAULT NULL,
  `valor_max` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_metrica_tipo_periodo` (`metrica_id`,`tipo_meta`,`ejercicio`,`periodo_id`),
  KEY `idx_metrica` (`metrica_id`),
  KEY `idx_periodo` (`periodo_id`),
  KEY `idx_ejercicio` (`ejercicio`),
  CONSTRAINT `metas_metricas_ibfk_1` FOREIGN KEY (`metrica_id`) REFERENCES `metricas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `metas_metricas_ibfk_2` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metas_metricas`
--

LOCK TABLES `metas_metricas` WRITE;
/*!40000 ALTER TABLE `metas_metricas` DISABLE KEYS */;
/*!40000 ALTER TABLE `metas_metricas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `metricas`
--

DROP TABLE IF EXISTS `metricas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `metricas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `area_id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tiene_meta` tinyint(1) DEFAULT '0',
  `unidad` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_valor` enum('numero','porcentaje','tiempo','decimal') COLLATE utf8mb4_unicode_ci DEFAULT 'numero',
  `icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'chart-line',
  `es_calculada` tinyint(1) DEFAULT '0',
  `orden` int DEFAULT '0',
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_area_slug` (`area_id`,`slug`),
  KEY `idx_area` (`area_id`),
  KEY `idx_activo` (`activo`),
  KEY `idx_es_calculada` (`es_calculada`),
  CONSTRAINT `metricas_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metricas`
--

LOCK TABLES `metricas` WRITE;
/*!40000 ALTER TABLE `metricas` DISABLE KEYS */;
INSERT INTO `metricas` VALUES (1,1,'Proyectos activos','proyectos-activos','Cantidad de proyectos en desarrollo activo',0,'proyectos','numero','code',0,1,1,'2026-05-07 14:12:47','2026-05-07 14:12:47'),(2,1,'Proyectos finalizados','proyectos-finalizados','Cantidad de proyectos completados en el período',0,'proyectos','numero','check',0,2,1,'2026-05-07 14:14:41','2026-05-07 14:14:41'),(3,1,'Releases','releases','Cantidad de versiones liberadas a producción',0,'releases','numero','rocket',0,3,1,'2026-05-07 14:15:06','2026-05-07 14:15:06'),(4,1,'Bugs corregidos','bugs-corregidos','Cantidad de errores corregidos en el período',0,'bugs_corregidos','numero','bug',0,4,1,'2026-05-07 14:15:48','2026-05-07 14:15:48'),(5,1,'Bugs producción','bugs-producci-n','Cantidad de errores reportados en producción',0,'bugs','numero','alert-triangle',0,5,1,'2026-05-07 14:16:25','2026-05-07 14:16:25'),(6,1,'Automatizaciones','automatizaciones','Cantidad de procesos automatizados',0,'procesos','numero','star',0,6,1,'2026-05-07 14:18:08','2026-05-07 14:18:08'),(7,2,'Disponibilidad sistemas','disponibilidad-sistemas','Porcentaje de uptime de los sistemas',0,'%','porcentaje','chart-bar',0,1,1,'2026-05-07 14:19:00','2026-05-07 14:19:00'),(8,2,'Incidentes infraestructura','incidentes-infraestructura','Cantidad de incidentes de infraestructura',0,'incidentes','numero','alert-circle',0,2,1,'2026-05-07 14:19:59','2026-05-07 14:19:59'),(9,2,'Vulnerabilidades detectadas','vulnerabilidades-detectadas','Vulnerabilidades identificadas en el período',0,'vulnerabilidades','numero','shield',0,3,1,'2026-05-07 14:20:31','2026-05-07 14:20:31'),(10,2,'Vulnerabilidades corregidas','vulnerabilidades-corregidas','Vulnerabilidades mitigadas en el período',0,'vulnerabilidades','numero','shield-check',0,4,1,'2026-05-07 14:21:03','2026-05-07 14:21:03'),(11,2,'Ataques bloqueados','ataques-bloqueados','Intentos de ataque bloqueados',0,'ataques','numero','shield-lock',0,5,1,'2026-05-07 14:21:26','2026-05-07 14:21:26'),(13,2,'Mantenimientos','mantenimientos','Mantenimientos preventivos realizados',0,'mantenimientos','numero','tool',0,6,1,'2026-05-07 14:22:15','2026-05-07 14:22:15'),(14,3,'Tickets recibidos','tickets-recibidos','Total de solicitudes de soporte recibidas',0,'tickets','numero','alert-circle',0,1,1,'2026-05-07 14:24:03','2026-05-07 14:24:54'),(15,3,'Tickets resueltos','tickets-resueltos','Solicitudes resueltas en el período',0,'tickets','numero','circle-check',0,2,1,'2026-05-07 14:24:48','2026-05-07 14:24:48'),(16,3,'Tickets pendientes','tickets-pendientes','Solicitudes pendientes de resolución',0,'tickets','numero','clock',0,3,1,'2026-05-07 14:25:31','2026-05-07 14:25:31'),(17,3,'Tiempo resolución','tiempo-resoluci-n','Tiempo promedio de resolución en minutos',0,'minutos','tiempo','alarm',0,4,1,'2026-05-07 14:26:13','2026-05-07 14:40:59'),(18,3,'Cumplimiento SLA','cumplimiento-sla','Porcentaje de cumplimiento de SLA',0,'%','porcentaje','chart-donut',0,5,1,'2026-05-07 14:27:35','2026-05-07 14:27:35'),(19,3,'Equipos reparados','equipos-reparados','Cantidad de equipos reparados',0,'equipos','numero','devices-pc',0,6,1,'2026-05-07 14:28:24','2026-05-07 14:28:24'),(20,3,'Equipos instalados','equipos-instalados','Cantidad de equipos nuevos instalados',0,'equipos','numero','device-desktop',0,7,1,'2026-05-07 14:29:21','2026-05-07 14:29:21'),(21,3,'Soporte agencias','soporte-agencias','Visitas de soporte a agencias',0,'visitas','numero','building',0,8,1,'2026-05-07 14:29:54','2026-05-07 14:29:54'),(22,3,'Soporte Puntos CIC y Agencia Móvil','soporte-puntos-cic-y-agencia-m-vil','Visitas de soporte a Puntos CIC y Agencia Móvil',0,'visitas','numero','building-store',0,9,1,'2026-05-07 14:30:59','2026-05-07 14:30:59'),(23,3,'Soporte Anexos','soporte-anexos','Visitas de soporte a anexos',0,'visitas','numero','building-bank',0,10,1,'2026-05-07 14:31:49','2026-05-07 14:32:39'),(24,3,'Soporte Total','soporte-total','Visitas de soporte',0,'visitas','numero','building-community',1,11,1,'2026-05-07 14:32:31','2026-05-07 14:32:56'),(25,4,'Cumplimiento de Políticas (SGSI)','cumplimiento-de-pol-ticas-sgsi-','Nivel de formalización y unificación del Sistema de Gestión de Seguridad de la Información',0,'%','porcentaje','shield-check',0,1,1,'2026-05-07 14:34:10','2026-05-07 14:34:10'),(26,4,'Gestión de Identidades','gesti-n-de-identidades','Cantidad de altas, bajas y cambios de credenciales en Office 365 y sistemas core',0,'usuarios o gestiones','numero','user-check',0,2,1,'2026-05-07 14:34:45','2026-05-07 14:34:45'),(27,4,'Análisis Proactivos Realizados','an-lisis-proactivos-realizados','Investigaciones y remediaciones de brechas potenciales antes de su explotación',0,'análisis','numero','check',0,3,1,'2026-05-07 14:35:11','2026-05-07 14:35:11'),(28,4,'Activos Críticos Gestionados','activos-cr-ticos-gestionados','Sistemas y licencias corporativas bajo la administración directa de Ciberseguridad',0,'gestiones','numero','server-2',0,4,1,'2026-05-07 14:35:51','2026-05-07 14:36:04'),(29,5,'Transacciones CIC Virtual (Consolidado)','transacciones-cic-virtual-consolidado-','Total de transacciones realizadas a través de la plataforma web CIC Virtual',0,'tx','numero','device-desktop',0,1,1,'2026-05-07 14:37:51','2026-05-07 14:37:51'),(30,5,'Transacciones Billetera fri','transacciones-billetera-fri','Total de transacciones movilizadas a través de la billetera virtual fri',0,'tx','numero','device-mobile',0,2,1,'2026-05-07 14:38:42','2026-05-07 14:38:42'),(31,5,'Transacciones Tarjetas de Débito','transacciones-tarjetas-de-d-bito','Volumen operativo total de transacciones realizadas con tarjetas de débito (Consolidado Dock y Cooitza)',0,'tx','numero','credit-card',0,3,1,'2026-05-07 14:39:08','2026-05-07 14:39:08'),(32,5,'Monto CIC Virtual (Consolidado)','monto-cic-virtual-consolidado-','Monto total transaccionado a través del canal CIC Virtual en el período',0,'Millones Q','decimal','report-money',0,4,1,'2026-05-07 14:39:48','2026-05-08 18:30:00'),(33,5,'Monto fri','monto-fri','Monto total transaccionado a través de la billetera virtual fri en el período',0,'Millones Q','decimal','report-money',0,5,1,'2026-05-07 14:40:16','2026-05-08 18:30:06'),(34,5,'Monto Tarjetas de Débito','monto-tarjetas-de-d-bito','Monto total monetario transaccionado a través de tarjetas de débito (Consolidado Dock y Cooitza)',0,'Millones Q','decimal','credit-card',0,6,1,'2026-05-07 14:40:48','2026-05-08 18:30:12'),(35,4,'Políticas Desarrolladas','pol-ticas-desarrolladas','',0,'políticas','numero','file-check',0,5,1,'2026-05-08 17:44:33','2026-05-08 17:44:33');
/*!40000 ALTER TABLE `metricas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `metricas_componentes`
--

DROP TABLE IF EXISTS `metricas_componentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `metricas_componentes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `metrica_calculada_id` int NOT NULL,
  `metrica_componente_id` int NOT NULL,
  `operacion` enum('suma','resta','promedio') COLLATE utf8mb4_unicode_ci DEFAULT 'suma',
  `orden` int DEFAULT '0',
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_calculada` (`metrica_calculada_id`),
  KEY `idx_componente` (`metrica_componente_id`),
  CONSTRAINT `metricas_componentes_ibfk_1` FOREIGN KEY (`metrica_calculada_id`) REFERENCES `metricas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `metricas_componentes_ibfk_2` FOREIGN KEY (`metrica_componente_id`) REFERENCES `metricas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metricas_componentes`
--

LOCK TABLES `metricas_componentes` WRITE;
/*!40000 ALTER TABLE `metricas_componentes` DISABLE KEYS */;
INSERT INTO `metricas_componentes` VALUES (40,24,22,'suma',0,0),(41,24,23,'suma',1,0),(42,24,21,'suma',2,0),(43,24,22,'suma',0,1),(44,24,23,'suma',1,1),(45,24,21,'suma',2,1);
/*!40000 ALTER TABLE `metricas_componentes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periodos`
--

DROP TABLE IF EXISTS `periodos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periodos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ejercicio` int NOT NULL,
  `periodo` int NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ejercicio_periodo` (`ejercicio`,`periodo`),
  KEY `idx_activo` (`activo`),
  KEY `idx_ejercicio` (`ejercicio`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodos`
--

LOCK TABLES `periodos` WRITE;
/*!40000 ALTER TABLE `periodos` DISABLE KEYS */;
INSERT INTO `periodos` VALUES (1,2026,1,'Enero 2026','2026-01-01','2026-01-31',1,'2026-05-07 14:41:53'),(2,2026,2,'Febrero 2026','2026-02-01','2026-02-28',1,'2026-05-07 14:41:53'),(3,2026,3,'Marzo 2026','2026-03-01','2026-03-31',1,'2026-05-07 14:41:53'),(4,2026,4,'Abril 2026','2026-04-01','2026-04-30',1,'2026-05-07 14:41:53'),(5,2026,5,'Mayo 2026','2026-05-01','2026-05-31',1,'2026-05-07 14:41:53'),(6,2026,6,'Junio 2026','2026-06-01','2026-06-30',1,'2026-05-07 14:41:53'),(7,2026,7,'Julio 2026','2026-07-01','2026-07-31',1,'2026-05-07 14:41:53'),(8,2026,8,'Agosto 2026','2026-08-01','2026-08-31',1,'2026-05-07 14:41:53'),(9,2026,9,'Septiembre 2026','2026-09-01','2026-09-30',1,'2026-05-07 14:41:53'),(10,2026,10,'Octubre 2026','2026-10-01','2026-10-31',1,'2026-05-07 14:41:53'),(11,2026,11,'Noviembre 2026','2026-11-01','2026-11-30',1,'2026-05-07 14:41:53'),(12,2026,12,'Diciembre 2026','2026-12-01','2026-12-31',1,'2026-05-07 14:41:53');
/*!40000 ALTER TABLE `periodos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportes`
--

DROP TABLE IF EXISTS `reportes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reportes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `area_id` int NOT NULL,
  `periodo_id` int DEFAULT NULL COMMENT 'NULL para reportes anuales',
  `anio` int NOT NULL COMMENT 'A├▒o del reporte (2026, 2027, etc)',
  `tipo_reporte` enum('mensual','trimestral','semestral','anual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mensual',
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci COMMENT 'Descripci├│n breve/resumen ejecutivo',
  `contenido` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'HTML generado por TinyMCE',
  `estado` enum('borrador','revision','publicado','archivado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `fecha_publicacion` datetime DEFAULT NULL,
  `version` int DEFAULT '1',
  `usuario_creacion_id` int NOT NULL,
  `usuario_modificacion_id` int DEFAULT NULL,
  `usuario_publicacion_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_reporte_unico` (`area_id`,`periodo_id`,`anio`,`tipo_reporte`,`version`),
  KEY `usuario_creacion_id` (`usuario_creacion_id`),
  KEY `usuario_modificacion_id` (`usuario_modificacion_id`),
  KEY `usuario_publicacion_id` (`usuario_publicacion_id`),
  KEY `idx_area` (`area_id`),
  KEY `idx_periodo` (`periodo_id`),
  KEY `idx_anio` (`anio`),
  KEY `idx_estado` (`estado`),
  KEY `idx_tipo` (`tipo_reporte`),
  KEY `idx_fecha_pub` (`fecha_publicacion`),
  CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`),
  CONSTRAINT `reportes_ibfk_2` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reportes_ibfk_3` FOREIGN KEY (`usuario_creacion_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `reportes_ibfk_4` FOREIGN KEY (`usuario_modificacion_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reportes_ibfk_5` FOREIGN KEY (`usuario_publicacion_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportes`
--

LOCK TABLES `reportes` WRITE;
/*!40000 ALTER TABLE `reportes` DISABLE KEYS */;
INSERT INTO `reportes` VALUES (2,1,NULL,2026,'mensual','Nuevo Reporte','','ñlaksjdfkjañskldfj asd\r\nfalksñdjfñlkajsdf\r\nasñdlkfjaslkdjf\r\n\r\n\r\nlañksdjñflk alksj df\r\nñlkasj ñdflkj asdf\r\nklajdñflkjñlaksjdf\r\nalñksdfjñlkjd\r\n\r\n\r\nlas metas se cumplieron correctamente. ','borrador',NULL,1,1,1,NULL,'2026-05-08 19:06:40','2026-05-08 19:07:01');
/*!40000 ALTER TABLE `reportes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportes_adjuntos`
--

DROP TABLE IF EXISTS `reportes_adjuntos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reportes_adjuntos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reporte_id` int NOT NULL,
  `nombre_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `archivo_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamano_bytes` int NOT NULL,
  `usuario_subida_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_subida_id` (`usuario_subida_id`),
  KEY `idx_reporte` (`reporte_id`),
  CONSTRAINT `reportes_adjuntos_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reportes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reportes_adjuntos_ibfk_2` FOREIGN KEY (`usuario_subida_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportes_adjuntos`
--

LOCK TABLES `reportes_adjuntos` WRITE;
/*!40000 ALTER TABLE `reportes_adjuntos` DISABLE KEYS */;
/*!40000 ALTER TABLE `reportes_adjuntos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportes_comentarios`
--

DROP TABLE IF EXISTS `reportes_comentarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reportes_comentarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reporte_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_reporte_momento` enum('borrador','revision','publicado','archivado') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reporte` (`reporte_id`),
  KEY `idx_usuario` (`usuario_id`),
  CONSTRAINT `reportes_comentarios_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reportes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reportes_comentarios_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportes_comentarios`
--

LOCK TABLES `reportes_comentarios` WRITE;
/*!40000 ALTER TABLE `reportes_comentarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `reportes_comentarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportes_graficos`
--

DROP TABLE IF EXISTS `reportes_graficos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reportes_graficos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reporte_id` int NOT NULL,
  `grafico_id` int NOT NULL COMMENT 'ID del gr├ífico original en configuracion_graficos',
  `imagen_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ruta de la captura PNG del gr├ífico',
  `imagen_thumbnail` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Miniatura 150x150 para galer├¡a',
  `imagen_width` int DEFAULT NULL COMMENT 'Ancho original en px',
  `imagen_height` int DEFAULT NULL COMMENT 'Alto original en px',
  `periodo_captura_id` int DEFAULT NULL COMMENT 'Per├¡odo cuando se captur├│',
  `titulo_grafico` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'T├¡tulo del gr├ífico en ese momento',
  `posicion_en_reporte` int DEFAULT '1' COMMENT 'Orden de aparici├│n (1, 2, 3...)',
  `alineacion` enum('left','center','right','justify') COLLATE utf8mb4_unicode_ci DEFAULT 'center',
  `ajuste_texto` enum('inline','wrap','square','tight','through','top-bottom','behind','front') COLLATE utf8mb4_unicode_ci DEFAULT 'inline',
  `ancho_display` int DEFAULT NULL COMMENT 'Ancho en px cuando se inserta (puede ser diferente al original)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `periodo_captura_id` (`periodo_captura_id`),
  KEY `idx_reporte` (`reporte_id`),
  KEY `idx_grafico` (`grafico_id`),
  KEY `idx_posicion` (`posicion_en_reporte`),
  CONSTRAINT `reportes_graficos_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reportes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reportes_graficos_ibfk_2` FOREIGN KEY (`grafico_id`) REFERENCES `configuracion_graficos` (`id`),
  CONSTRAINT `reportes_graficos_ibfk_3` FOREIGN KEY (`periodo_captura_id`) REFERENCES `periodos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportes_graficos`
--

LOCK TABLES `reportes_graficos` WRITE;
/*!40000 ALTER TABLE `reportes_graficos` DISABLE KEYS */;
/*!40000 ALTER TABLE `reportes_graficos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportes_versiones`
--

DROP TABLE IF EXISTS `reportes_versiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reportes_versiones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reporte_id` int NOT NULL,
  `version` int NOT NULL,
  `contenido` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Copia del HTML en esa versi├│n',
  `usuario_id` int NOT NULL,
  `nota_version` text COLLATE utf8mb4_unicode_ci COMMENT 'Nota explicando el cambio',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_reporte_version` (`reporte_id`,`version`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_reporte` (`reporte_id`),
  KEY `idx_version` (`version`),
  CONSTRAINT `reportes_versiones_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reportes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reportes_versiones_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportes_versiones`
--

LOCK TABLES `reportes_versiones` WRITE;
/*!40000 ALTER TABLE `reportes_versiones` DISABLE KEYS */;
/*!40000 ALTER TABLE `reportes_versiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_eventos`
--

DROP TABLE IF EXISTS `timeline_eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeline_eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `area_id` int NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo` enum('release','incidente','mejora','otro') COLLATE utf8mb4_unicode_ci DEFAULT 'otro',
  `fecha_evento` date NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_area` (`area_id`),
  KEY `idx_fecha` (`fecha_evento`),
  KEY `idx_tipo` (`tipo`),
  CONSTRAINT `timeline_eventos_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timeline_eventos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_eventos`
--

LOCK TABLES `timeline_eventos` WRITE;
/*!40000 ALTER TABLE `timeline_eventos` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_eventos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('super_admin','dept_admin','area_admin','dept_viewer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dept_viewer',
  `departamento_id` int DEFAULT NULL,
  `area_id` int DEFAULT NULL,
  `foto_perfil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tema` enum('light','dark') COLLATE utf8mb4_unicode_ci DEFAULT 'light',
  `avatar_icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `avatar_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#3b82f6',
  `activo` tinyint(1) DEFAULT '1',
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `area_id` (`area_id`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_rol` (`rol`),
  KEY `idx_departamento` (`departamento_id`),
  KEY `idx_activo` (`activo`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'superadmin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Super Administrador','superadmin@metricas.com','super_admin',NULL,NULL,NULL,'dark','user-circle','#1756ba',1,'2026-05-08 08:12:51','2026-04-20 15:30:21','2026-05-08 17:33:29');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `v_metricas_completas`
--

DROP TABLE IF EXISTS `v_metricas_completas`;
/*!50001 DROP VIEW IF EXISTS `v_metricas_completas`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_metricas_completas` AS SELECT 
 1 AS `id`,
 1 AS `nombre`,
 1 AS `slug`,
 1 AS `descripcion`,
 1 AS `unidad`,
 1 AS `tipo_valor`,
 1 AS `es_calculada`,
 1 AS `area_id`,
 1 AS `area_nombre`,
 1 AS `area_slug`,
 1 AS `departamento_id`,
 1 AS `departamento_nombre`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_usuarios_completos`
--

DROP TABLE IF EXISTS `v_usuarios_completos`;
/*!50001 DROP VIEW IF EXISTS `v_usuarios_completos`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_usuarios_completos` AS SELECT 
 1 AS `id`,
 1 AS `username`,
 1 AS `nombre`,
 1 AS `email`,
 1 AS `rol`,
 1 AS `activo`,
 1 AS `ultimo_acceso`,
 1 AS `departamento_id`,
 1 AS `departamento_nombre`,
 1 AS `area_id`,
 1 AS `area_nombre`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `valores_metricas`
--

DROP TABLE IF EXISTS `valores_metricas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `valores_metricas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `metrica_id` int NOT NULL,
  `periodo_id` int NOT NULL,
  `valor_numero` int DEFAULT NULL,
  `valor_decimal` decimal(10,2) DEFAULT NULL,
  `nota` text COLLATE utf8mb4_unicode_ci,
  `usuario_registro_id` int DEFAULT NULL,
  `usuario_modificacion_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_metrica_periodo` (`metrica_id`,`periodo_id`),
  KEY `usuario_registro_id` (`usuario_registro_id`),
  KEY `usuario_modificacion_id` (`usuario_modificacion_id`),
  KEY `idx_metrica` (`metrica_id`),
  KEY `idx_periodo` (`periodo_id`),
  CONSTRAINT `valores_metricas_ibfk_1` FOREIGN KEY (`metrica_id`) REFERENCES `metricas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `valores_metricas_ibfk_2` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `valores_metricas_ibfk_3` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `valores_metricas_ibfk_4` FOREIGN KEY (`usuario_modificacion_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `valores_metricas`
--

LOCK TABLES `valores_metricas` WRITE;
/*!40000 ALTER TABLE `valores_metricas` DISABLE KEYS */;
INSERT INTO `valores_metricas` VALUES (1,1,3,18,NULL,'',1,NULL,'2026-05-07 14:49:17','2026-05-07 14:49:17'),(2,2,3,10,NULL,'',1,NULL,'2026-05-07 14:49:18','2026-05-07 14:49:18'),(3,3,3,27,NULL,'',1,NULL,'2026-05-07 14:49:18','2026-05-07 14:49:18'),(4,4,3,38,NULL,'',1,NULL,'2026-05-07 14:49:18','2026-05-07 14:49:18'),(5,5,3,25,NULL,'',1,NULL,'2026-05-07 14:49:18','2026-05-07 14:49:18'),(6,6,3,42,NULL,'',1,NULL,'2026-05-07 14:49:18','2026-05-07 14:49:18'),(7,7,3,NULL,98.00,'',1,NULL,'2026-05-07 14:50:15','2026-05-07 14:50:15'),(8,8,3,3,NULL,'',1,NULL,'2026-05-07 14:50:15','2026-05-07 14:50:15'),(9,9,3,2,NULL,'',1,NULL,'2026-05-07 14:50:15','2026-05-07 14:50:15'),(10,10,3,2,NULL,'',1,NULL,'2026-05-07 14:50:15','2026-05-07 14:50:15'),(11,11,3,2,NULL,'',1,NULL,'2026-05-07 14:50:15','2026-05-07 14:50:15'),(12,13,3,10,NULL,'',1,NULL,'2026-05-07 14:50:15','2026-05-07 14:50:15'),(13,14,3,109,NULL,'',1,1,'2026-05-07 14:52:09','2026-05-08 16:56:25'),(14,15,3,105,NULL,'',1,1,'2026-05-07 14:52:09','2026-05-08 16:56:25'),(15,16,3,4,NULL,'',1,1,'2026-05-07 14:52:09','2026-05-08 16:56:25'),(16,17,3,30,NULL,'',1,1,'2026-05-07 14:52:09','2026-05-08 16:56:25'),(17,18,3,NULL,97.00,'',1,1,'2026-05-07 14:52:09','2026-05-08 16:56:25'),(18,19,3,35,NULL,'',1,1,'2026-05-07 14:52:09','2026-05-08 16:56:25'),(19,20,3,55,NULL,'',1,1,'2026-05-07 14:52:09','2026-05-08 16:56:25'),(20,21,3,9,NULL,'',1,1,'2026-05-07 14:52:09','2026-05-08 16:56:25'),(21,22,3,5,NULL,'',1,1,'2026-05-07 14:52:09','2026-05-08 16:56:25'),(22,23,3,3,NULL,'',1,1,'2026-05-07 14:52:09','2026-05-08 16:56:25'),(23,24,3,17,NULL,'Calculado automáticamente',1,1,'2026-05-07 14:52:09','2026-05-07 14:52:09'),(24,25,3,NULL,85.00,'',1,NULL,'2026-05-07 14:56:13','2026-05-07 14:56:13'),(25,26,3,25,NULL,'',1,NULL,'2026-05-07 14:56:13','2026-05-07 14:56:13'),(26,27,3,1,NULL,'',1,NULL,'2026-05-07 14:56:13','2026-05-07 14:56:13'),(27,28,3,2,NULL,'',1,NULL,'2026-05-07 14:56:13','2026-05-07 14:56:13'),(28,29,3,2615,NULL,'',1,1,'2026-05-07 14:57:58','2026-05-07 14:58:16'),(29,30,3,7823,NULL,'',1,1,'2026-05-07 14:57:58','2026-05-07 14:58:16'),(30,31,3,17733,NULL,'',1,1,'2026-05-07 14:57:58','2026-05-07 14:58:16'),(31,32,3,NULL,7.90,'',1,1,'2026-05-07 14:57:58','2026-05-08 18:32:27'),(32,33,3,NULL,10.21,'',1,1,'2026-05-07 14:57:58','2026-05-08 18:32:27'),(33,34,3,NULL,13.73,'',1,1,'2026-05-07 14:57:58','2026-05-08 18:32:27'),(34,21,4,4,NULL,'',1,1,'2026-05-07 15:38:54','2026-05-07 15:41:47'),(35,22,4,4,NULL,'',1,1,'2026-05-07 15:38:54','2026-05-07 15:41:47'),(36,23,4,3,NULL,'',1,1,'2026-05-07 15:38:54','2026-05-07 15:41:47'),(37,24,4,11,NULL,'Calculado automáticamente',1,1,'2026-05-07 15:38:54','2026-05-07 15:38:54'),(38,19,4,32,NULL,'',1,1,'2026-05-07 15:41:47','2026-05-08 16:57:05'),(39,20,4,13,NULL,'',1,1,'2026-05-07 15:41:47','2026-05-08 16:57:05'),(40,7,4,NULL,99.00,'Operación normal sin interrupciones críticas en servicios fundamentales durante el periodo.',1,NULL,'2026-05-07 17:36:19','2026-05-07 17:36:19'),(41,8,4,0,NULL,'Sin reportes de fallas de red o hardware; enfoque únicamente en soporte preventivo.',1,NULL,'2026-05-07 17:36:19','2026-05-07 17:36:19'),(42,9,4,4,NULL,'Identificación de riesgos en accesos a redes sociales, canales no oficiales y segmentación de red.',1,NULL,'2026-05-07 17:36:19','2026-05-07 17:36:19'),(43,10,4,3,NULL,'Mitigación mediante cambio de credenciales y eliminación de accesos no autorizados en plataformas digitales.',1,NULL,'2026-05-07 17:36:19','2026-05-07 17:36:19'),(44,11,4,2,NULL,'Gestión exitosa y baja de canales externos que intentaban suplantación de identidad institucional.',1,NULL,'2026-05-07 17:36:19','2026-05-07 17:36:19'),(45,13,4,4,NULL,'Ejecución de validaciones preventivas de capacidad y planificación para la transición a nueva infraestructura.',1,NULL,'2026-05-07 17:36:19','2026-05-07 17:36:19'),(46,25,4,NULL,35.00,'Avance en el desarrollo de políticas de clasificación, respaldo y manejo de información según cronograma.',1,1,'2026-05-07 17:37:12','2026-05-08 17:44:51'),(47,26,4,4,NULL,'Atención oportuna de solicitudes de credenciales para nuevos ingresos y depuración de accesos externos.',1,1,'2026-05-07 17:37:12','2026-05-08 17:44:51'),(48,27,4,5,NULL,'Ejecución de IP Planning y revisiones estratégicas de seguridad para la implementación del nuevo Firewall.',1,1,'2026-05-07 17:37:12','2026-05-08 17:44:51'),(49,28,4,5,NULL,'Seguimiento preventivo de redes sociales, dominio, seguridad de red y procesos de fortalecimiento de servicios.',1,1,'2026-05-07 17:37:12','2026-05-08 17:44:51'),(50,1,4,11,NULL,'',1,NULL,'2026-05-08 16:53:15','2026-05-08 16:53:15'),(51,2,4,16,NULL,'',1,NULL,'2026-05-08 16:53:15','2026-05-08 16:53:15'),(52,3,4,26,NULL,'',1,NULL,'2026-05-08 16:53:15','2026-05-08 16:53:15'),(53,4,4,48,NULL,'',1,NULL,'2026-05-08 16:53:15','2026-05-08 16:53:15'),(54,5,4,14,NULL,'',1,NULL,'2026-05-08 16:53:15','2026-05-08 16:53:15'),(55,6,4,11,NULL,'',1,NULL,'2026-05-08 16:53:15','2026-05-08 16:53:15'),(56,14,4,102,NULL,'',1,NULL,'2026-05-08 16:57:05','2026-05-08 16:57:05'),(57,15,4,100,NULL,'',1,NULL,'2026-05-08 16:57:05','2026-05-08 16:57:05'),(58,16,4,2,NULL,'',1,NULL,'2026-05-08 16:57:05','2026-05-08 16:57:05'),(59,17,4,30,NULL,'',1,NULL,'2026-05-08 16:57:05','2026-05-08 16:57:05'),(60,18,4,NULL,97.00,'',1,NULL,'2026-05-08 16:57:05','2026-05-08 16:57:05'),(61,29,4,8435,NULL,'',1,1,'2026-05-08 17:17:24','2026-05-08 17:18:24'),(62,30,4,1936,NULL,'',1,1,'2026-05-08 17:17:24','2026-05-08 17:18:24'),(63,31,4,6854,NULL,'',1,1,'2026-05-08 17:17:24','2026-05-08 17:25:56'),(64,32,4,NULL,10.85,'',1,1,'2026-05-08 17:17:24','2026-05-08 18:31:43'),(65,33,4,NULL,4.56,'',1,1,'2026-05-08 17:17:24','2026-05-08 18:31:43'),(66,34,4,NULL,1.92,'',1,1,'2026-05-08 17:17:24','2026-05-08 18:31:43'),(67,35,4,3,NULL,'Total de políticas definidas en el plan de implementación del SGSI',1,1,'2026-05-08 17:44:51','2026-05-08 17:45:08');
/*!40000 ALTER TABLE `valores_metricas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `v_metricas_completas`
--

/*!50001 DROP VIEW IF EXISTS `v_metricas_completas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_metricas_completas` AS select `m`.`id` AS `id`,`m`.`nombre` AS `nombre`,`m`.`slug` AS `slug`,`m`.`descripcion` AS `descripcion`,`m`.`unidad` AS `unidad`,`m`.`tipo_valor` AS `tipo_valor`,`m`.`es_calculada` AS `es_calculada`,`a`.`id` AS `area_id`,`a`.`nombre` AS `area_nombre`,`a`.`slug` AS `area_slug`,`d`.`id` AS `departamento_id`,`d`.`nombre` AS `departamento_nombre` from ((`metricas` `m` join `areas` `a` on((`m`.`area_id` = `a`.`id`))) join `departamentos` `d` on((`a`.`departamento_id` = `d`.`id`))) where ((`m`.`activo` = 1) and (`a`.`activo` = 1) and (`d`.`activo` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_usuarios_completos`
--

/*!50001 DROP VIEW IF EXISTS `v_usuarios_completos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_usuarios_completos` AS select `u`.`id` AS `id`,`u`.`username` AS `username`,`u`.`nombre` AS `nombre`,`u`.`email` AS `email`,`u`.`rol` AS `rol`,`u`.`activo` AS `activo`,`u`.`ultimo_acceso` AS `ultimo_acceso`,`d`.`id` AS `departamento_id`,`d`.`nombre` AS `departamento_nombre`,`a`.`id` AS `area_id`,`a`.`nombre` AS `area_nombre` from ((`usuarios` `u` left join `departamentos` `d` on((`u`.`departamento_id` = `d`.`id`))) left join `areas` `a` on((`u`.`area_id` = `a`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-08 15:26:36
