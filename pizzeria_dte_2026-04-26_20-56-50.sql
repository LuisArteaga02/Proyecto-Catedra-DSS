# ************************************************************
# Antares - SQL Client
# Version 0.7.35
# 
# https://antares-sql.app/
# https://github.com/antares-sql/antares
# 
# Host: 127.0.0.1 (Source distribution 8.4.8)
# Database: pizzeria_dte
# Generation time: 2026-04-26T20:57:13-06:00
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table cat_actividad_economica
# ------------------------------------------------------------

CREATE DATABASE pizzeria_dte;
USE DATABASE pizzeria_dte;
DROP TABLE IF EXISTS `cat_actividad_economica`;

CREATE TABLE `cat_actividad_economica` (
  `codigo` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `cat_actividad_economica` WRITE;
/*!40000 ALTER TABLE `cat_actividad_economica` DISABLE KEYS */;

INSERT INTO `cat_actividad_economica` (`codigo`, `descripcion`) VALUES
	("10005", "Consumo final");

/*!40000 ALTER TABLE `cat_actividad_economica` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table cat_departamento
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cat_departamento`;

CREATE TABLE `cat_departamento` (
  `codigo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `cat_departamento` WRITE;
/*!40000 ALTER TABLE `cat_departamento` DISABLE KEYS */;

INSERT INTO `cat_departamento` (`codigo`, `nombre`) VALUES
	("06", "San Salvador");

/*!40000 ALTER TABLE `cat_departamento` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table cat_medio_pago
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cat_medio_pago`;

CREATE TABLE `cat_medio_pago` (
  `codigo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table cat_municipio
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cat_municipio`;

CREATE TABLE `cat_municipio` (
  `codigo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_depto` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`codigo`,`codigo_depto`),
  KEY `fk_muni_dep` (`codigo_depto`),
  CONSTRAINT `fk_muni_dep` FOREIGN KEY (`codigo_depto`) REFERENCES `cat_departamento` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `cat_municipio` WRITE;
/*!40000 ALTER TABLE `cat_municipio` DISABLE KEYS */;

INSERT INTO `cat_municipio` (`codigo`, `codigo_depto`, `nombre`) VALUES
	("01", "06", "Soyapango");

/*!40000 ALTER TABLE `cat_municipio` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table cat_tipo_documento
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cat_tipo_documento`;

CREATE TABLE `cat_tipo_documento` (
  `codigo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `cat_tipo_documento` WRITE;
/*!40000 ALTER TABLE `cat_tipo_documento` DISABLE KEYS */;

INSERT INTO `cat_tipo_documento` (`codigo`, `nombre`) VALUES
	("13", "DUI");

/*!40000 ALTER TABLE `cat_tipo_documento` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table cat_tipo_dte
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cat_tipo_dte`;

CREATE TABLE `cat_tipo_dte` (
  `codigo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `cat_tipo_dte` WRITE;
/*!40000 ALTER TABLE `cat_tipo_dte` DISABLE KEYS */;

INSERT INTO `cat_tipo_dte` (`codigo`, `nombre`, `descripcion`) VALUES
	("01", "Factura", "Factura electrónica para consumidor final"),
	("03", "Comprobante de Crédito Fiscal", "CCF electrónico para contribuyentes"),
	("11", "Factura de Exportación", "Documento para operaciones de exportación"),
	("14", "Factura de Sujeto Excluido", "Documento para compras a sujetos excluidos");

/*!40000 ALTER TABLE `cat_tipo_dte` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table cat_tributo
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cat_tributo`;

CREATE TABLE `cat_tributo` (
  `codigo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tasa` decimal(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table cat_unidad_medida
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cat_unidad_medida`;

CREATE TABLE `cat_unidad_medida` (
  `codigo` int NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `cat_unidad_medida` WRITE;
/*!40000 ALTER TABLE `cat_unidad_medida` DISABLE KEYS */;

INSERT INTO `cat_unidad_medida` (`codigo`, `nombre`) VALUES
	(1, "Tonelada métrica"),
	(2, "Kilogramo"),
	(3, "Gramo"),
	(10, "Litro"),
	(20, "Metro"),
	(58, "Docena"),
	(59, "Unidad"),
	(99, "Otro");

/*!40000 ALTER TABLE `cat_unidad_medida` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table emisor
# ------------------------------------------------------------

DROP TABLE IF EXISTS `emisor`;

CREATE TABLE `emisor` (
  `id_emisor` int NOT NULL AUTO_INCREMENT,
  `nit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nrc` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_comercial` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_establecimiento` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_actividad_economica` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `municipio` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departamento` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_estable_mh` char(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_estable_interno` char(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_punto_venta_mh` char(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_punto_venta_int` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_emisor`),
  KEY `fk_muni` (`municipio`,`departamento`),
  KEY `fk_act_eco` (`codigo_actividad_economica`),
  CONSTRAINT `fk_act_eco` FOREIGN KEY (`codigo_actividad_economica`) REFERENCES `cat_actividad_economica` (`codigo`),
  CONSTRAINT `fk_muni` FOREIGN KEY (`municipio`, `departamento`) REFERENCES `cat_municipio` (`codigo`, `codigo_depto`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `emisor` WRITE;
/*!40000 ALTER TABLE `emisor` DISABLE KEYS */;

INSERT INTO `emisor` (`id_emisor`, `nit`, `nrc`, `nombre`, `nombre_comercial`, `tipo_establecimiento`, `codigo_actividad_economica`, `municipio`, `departamento`, `correo`, `telefono`, `cod_estable_mh`, `cod_estable_interno`, `cod_punto_venta_mh`, `cod_punto_venta_int`, `activo`) VALUES
	(1, "0000-000000-000-0", "000000-0", "Pizzeria El Salvador (Default)", NULL, "01", "10005", "01", "06", "info@pizzeria.com.sv", "2222-2222", NULL, NULL, NULL, NULL, 1);

/*!40000 ALTER TABLE `emisor` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table factura
# ------------------------------------------------------------

DROP TABLE IF EXISTS `factura`;

CREATE TABLE `factura` (
  `id_factura` int NOT NULL AUTO_INCREMENT,
  `id_receptor` int DEFAULT NULL,
  `codigo_generacion` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_control` varchar(31) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_dte` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '01',
  `ambiente` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '00',
  `fecha_emision` date NOT NULL,
  `hora_emision` time NOT NULL,
  `condicion_pago` int NOT NULL DEFAULT '1',
  `total_no_sujeto` decimal(16,2) NOT NULL DEFAULT '0.00',
  `total_exento` decimal(16,2) NOT NULL DEFAULT '0.00',
  `total_gravado` decimal(16,2) NOT NULL DEFAULT '0.00',
  `sub_total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `iva_retenido` decimal(16,2) NOT NULL DEFAULT '0.00',
  `retencion_renta` decimal(16,2) NOT NULL DEFAULT '0.00',
  `monto_total` decimal(16,2) NOT NULL DEFAULT '0.00',
  `total_iva` decimal(16,2) NOT NULL DEFAULT '0.00',
  `total_letras` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firma_digital` text COLLATE utf8mb4_unicode_ci,
  `sello_recibido` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_mh` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `codigo_msg` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion_msg` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modo_contingencia` tinyint(1) NOT NULL DEFAULT '0',
  `intentos_envio` int NOT NULL DEFAULT '0',
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_envio` datetime DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT NULL,
  PRIMARY KEY (`id_factura`),
  UNIQUE KEY `uq_cod_gen` (`codigo_generacion`),
  UNIQUE KEY `uq_num_ctrl` (`numero_control`),
  KEY `fk_fact_dte` (`tipo_dte`),
  KEY `fk_factura_receptor` (`id_receptor`),
  CONSTRAINT `fk_fact_dte` FOREIGN KEY (`tipo_dte`) REFERENCES `cat_tipo_dte` (`codigo`),
  CONSTRAINT `fk_factura_receptor` FOREIGN KEY (`id_receptor`) REFERENCES `receptor` (`id_receptor`),
  CONSTRAINT `chk_estado_mh` CHECK ((`estado_mh` in (_utf8mb4'PENDIENTE',_utf8mb4'ACEPTADO',_utf8mb4'RECHAZADO',_utf8mb4'ANULADO',_utf8mb4'CONTINGENCIA')))
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `factura` WRITE;
/*!40000 ALTER TABLE `factura` DISABLE KEYS */;

INSERT INTO `factura` (`id_factura`, `id_receptor`, `codigo_generacion`, `numero_control`, `tipo_dte`, `ambiente`, `fecha_emision`, `hora_emision`, `condicion_pago`, `total_no_sujeto`, `total_exento`, `total_gravado`, `sub_total`, `iva_retenido`, `retencion_renta`, `monto_total`, `total_iva`, `total_letras`, `firma_digital`, `sello_recibido`, `estado_mh`, `codigo_msg`, `descripcion_msg`, `modo_contingencia`, `intentos_envio`, `fecha_registro`, `fecha_envio`, `fecha_respuesta`) VALUES
	(3, NULL, "B44DB3DAA612DEEFCF0C8C200CBC1CE5", "DTE-01-A001-000000000007054", "01", "00", "2026-04-25", "05:56:58", 1, 0, 0, 5, 5, 0, 0, 5, 0.58, "CANTIDAD EN LETRAS", NULL, NULL, "PENDIENTE", NULL, NULL, 0, 0, "2026-04-24 23:56:58", NULL, NULL),
	(4, 6, "B04FB729-9C8D-4D05-BC24-20F551FED210", "DTE-01-00000001-000000000007055", "01", "00", "2026-04-25", "23:42:42", 1, 5, 0, 5, 5, 0, 0, 5, 0.58, "Total generado por sistema", NULL, NULL, "PENDIENTE", NULL, NULL, 0, 0, "2026-04-25 23:43:17", NULL, NULL),
	(5, 7, "8A3FD0B2-044B-4E8C-9E39-FC424D5C0A2D", "DTE-01-00000001-000000000007056", "01", "00", "2026-04-26", "20:34:55", 1, 5, 0, 5, 5, 0, 0, 5, 0.58, "Total generado por sistema", NULL, NULL, "PENDIENTE", NULL, NULL, 0, 0, "2026-04-26 20:35:38", NULL, NULL),
	(6, 7, "0E81E70C-57F5-435E-A760-AF9D34922B68", "DTE-01-00000001-000000000007057", "01", "00", "2026-04-26", "20:39:51", 1, 0, 0, 5, 5, 0, 0, 5, 0.58, "Total generado por sistema", NULL, NULL, "PENDIENTE", NULL, NULL, 0, 0, "2026-04-26 20:40:49", NULL, NULL),
	(7, 6, "C7811E7A-D737-403D-8F95-6E91B4B8F9C6", "DTE-01-00000001-000000000007058", "01", "00", "2026-04-26", "20:49:11", 1, 6, 0, 6, 6, 0, 0, 6, 0.69, "Total generado por sistema", NULL, NULL, "PENDIENTE", NULL, NULL, 0, 0, "2026-04-26 20:50:36", NULL, NULL),
	(8, 8, "CF3A0408-0ACB-424B-BB44-1801A03262BE", "DTE-01-00000001-000000000007059", "01", "00", "2026-04-26", "20:51:09", 1, 5, 0, 5, 5, 0, 0, 5, 0.58, "Total generado por sistema", NULL, NULL, "PENDIENTE", NULL, NULL, 0, 0, "2026-04-26 20:51:39", NULL, NULL);

/*!40000 ALTER TABLE `factura` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table factura_detalle
# ------------------------------------------------------------

DROP TABLE IF EXISTS `factura_detalle`;

CREATE TABLE `factura_detalle` (
  `id_detalle` int NOT NULL AUTO_INCREMENT,
  `id_factura` int NOT NULL,
  `id_producto` int NOT NULL,
  `num_item` int NOT NULL,
  `cantidad` decimal(16,8) NOT NULL,
  `precio_unitario` decimal(16,8) NOT NULL,
  `descuento` decimal(16,2) NOT NULL DEFAULT '0.00',
  `venta_gravada` decimal(16,2) NOT NULL DEFAULT '0.00',
  `iva_item` decimal(16,8) GENERATED ALWAYS AS (((`venta_gravada` / 1.13) * 0.13)) STORED,
  `precio_venta` decimal(16,2) GENERATED ALWAYS AS (((`cantidad` * `precio_unitario`) - `descuento`)) STORED,
  PRIMARY KEY (`id_detalle`),
  UNIQUE KEY `uq_item_fact` (`id_factura`,`num_item`),
  KEY `fk_det_prod` (`id_producto`),
  CONSTRAINT `fk_det_fact` FOREIGN KEY (`id_factura`) REFERENCES `factura` (`id_factura`) ON DELETE CASCADE,
  CONSTRAINT `fk_det_prod` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `factura_detalle` WRITE;
/*!40000 ALTER TABLE `factura_detalle` DISABLE KEYS */;

INSERT INTO `factura_detalle` (`id_detalle`, `id_factura`, `id_producto`, `num_item`, `cantidad`, `precio_unitario`, `descuento`, `venta_gravada`, `iva_item`, `precio_venta`) VALUES
	(1, 3, 1, 1, 1, 5, 0, 5, 0.57522124, 5),
	(2, 4, 1, 1, 1, 5, 0, 5, 0.57522124, 5),
	(3, 5, 1, 1, 1, 5, 0, 5, 0.57522124, 5),
	(4, 6, 1, 1, 1, 5, 0, 5, 0.57522124, 5),
	(5, 7, 2, 1, 1, 6, 0, 6, 0.69026549, 6),
	(6, 8, 1, 1, 1, 5, 0, 5, 0.57522124, 5);

/*!40000 ALTER TABLE `factura_detalle` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table factura_detalle_tributo
# ------------------------------------------------------------

DROP TABLE IF EXISTS `factura_detalle_tributo`;

CREATE TABLE `factura_detalle_tributo` (
  `id_factura_tributo` int NOT NULL AUTO_INCREMENT,
  `id_detalle` int NOT NULL,
  `codigo_tributo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_factura_tributo`),
  KEY `fk_id_detalle` (`id_detalle`),
  KEY `fk_cod_tributo` (`codigo_tributo`),
  CONSTRAINT `fk_cod_tributo` FOREIGN KEY (`codigo_tributo`) REFERENCES `cat_tributo` (`codigo`),
  CONSTRAINT `fk_id_detalle` FOREIGN KEY (`id_detalle`) REFERENCES `factura_detalle` (`id_detalle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table factura_evento
# ------------------------------------------------------------

DROP TABLE IF EXISTS `factura_evento`;

CREATE TABLE `factura_evento` (
  `id_evento` int NOT NULL AUTO_INCREMENT,
  `id_factura` int NOT NULL,
  `tipo_evento` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsable` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_generacion_r` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_evento` text COLLATE utf8mb4_unicode_ci,
  `estado_evento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `fecha_evento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_evento`),
  KEY `fk_ev_fact` (`id_factura`),
  CONSTRAINT `fk_ev_fact` FOREIGN KEY (`id_factura`) REFERENCES `factura` (`id_factura`),
  CONSTRAINT `chk_tipo_ev` CHECK ((`tipo_evento` in (_utf8mb4'05',_utf8mb4'06')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table factura_pago
# ------------------------------------------------------------

DROP TABLE IF EXISTS `factura_pago`;

CREATE TABLE `factura_pago` (
  `id_pago` int NOT NULL AUTO_INCREMENT,
  `id_factura` int NOT NULL,
  `codigo_pago` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(16,8) NOT NULL DEFAULT '0.00000000',
  `referencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_pago`),
  KEY `fk_factura` (`id_factura`),
  KEY `fk_cod_pago` (`codigo_pago`),
  CONSTRAINT `fk_cod_pago` FOREIGN KEY (`codigo_pago`) REFERENCES `cat_medio_pago` (`codigo`),
  CONSTRAINT `fk_factura` FOREIGN KEY (`id_factura`) REFERENCES `factura` (`id_factura`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table producto
# ------------------------------------------------------------

DROP TABLE IF EXISTS `producto`;

CREATE TABLE `producto` (
  `id_producto` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_mh` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_tipo` int NOT NULL DEFAULT '1',
  `unidad_medida` int NOT NULL DEFAULT '59',
  `precio` decimal(16,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_producto`),
  UNIQUE KEY `uq_codigo_mh` (`codigo_mh`),
  KEY `fk_prod_uni_med` (`unidad_medida`),
  CONSTRAINT `fk_prod_uni_med` FOREIGN KEY (`unidad_medida`) REFERENCES `cat_unidad_medida` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `producto` WRITE;
/*!40000 ALTER TABLE `producto` DISABLE KEYS */;

INSERT INTO `producto` (`id_producto`, `product_name`, `codigo_mh`, `item_tipo`, `unidad_medida`, `precio`, `activo`) VALUES
	(1, "Pizza de Pepperoni", "PZ-PEP", 1, 59, 5, 1),
	(2, "Pizza de Carne", "PZ-CAR", 1, 59, 6, 1),
	(3, "Coca Cola", "BEB-CC", 1, 59, 0.75, 1),
	(4, "Pepsi", "BEB-PE", 1, 59, 0.75, 1),
	(5, "Panes con Ajo", "ENT-PA", 1, 59, 2, 1),
	(6, "Palitos de Canela", "POS-PC", 1, 59, 2, 1);

/*!40000 ALTER TABLE `producto` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table receptor
# ------------------------------------------------------------

DROP TABLE IF EXISTS `receptor`;

CREATE TABLE `receptor` (
  `id_receptor` int NOT NULL AUTO_INCREMENT,
  `tipo_documento` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_documento` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nrc` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dir_departamento` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dir_municipio` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dir_complemento` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cod_actividad` char(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contrasena_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_receptor`),
  UNIQUE KEY `usuario` (`usuario`),
  KEY `fk_doc` (`tipo_documento`),
  KEY `fk_mun` (`dir_municipio`,`dir_departamento`),
  KEY `fk_act` (`cod_actividad`),
  CONSTRAINT `fk_act` FOREIGN KEY (`cod_actividad`) REFERENCES `cat_actividad_economica` (`codigo`),
  CONSTRAINT `fk_doc` FOREIGN KEY (`tipo_documento`) REFERENCES `cat_tipo_documento` (`codigo`),
  CONSTRAINT `fk_mun` FOREIGN KEY (`dir_municipio`, `dir_departamento`) REFERENCES `cat_municipio` (`codigo`, `codigo_depto`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `receptor` WRITE;
/*!40000 ALTER TABLE `receptor` DISABLE KEYS */;

INSERT INTO `receptor` (`id_receptor`, `tipo_documento`, `num_documento`, `nrc`, `nombre`, `dir_departamento`, `dir_municipio`, `dir_complemento`, `telefono`, `cod_actividad`, `correo`, `usuario`, `contrasena_hash`, `activo`, `fecha_registro`) VALUES
	(6, "13", "99999999-9", "", "luis", "06", "01", "aaa", "2222-2222", "10005", "luis@gmail.com", NULL, NULL, 1, "2026-04-25 23:43:17"),
	(7, "13", "11111111-1", "", "CACAS", "06", "01", "CACA HOUSE", "2222-2222", "10005", "caca@gmail.com", NULL, NULL, 1, "2026-04-26 20:35:38"),
	(8, "13", "77777777-7", "", "yugo", "06", "01", "EEEEEEEEE", "2222-2222", "10005", "yugo@gmail.com", NULL, NULL, 1, "2026-04-26 20:51:39");

/*!40000 ALTER TABLE `receptor` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table usuario
# ------------------------------------------------------------

DROP TABLE IF EXISTS `usuario`;

CREATE TABLE `usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `id_emisor` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uq_correo` (`correo`),
  KEY `fk_usr_emisor` (`id_emisor`),
  CONSTRAINT `fk_usr_emisor` FOREIGN KEY (`id_emisor`) REFERENCES `emisor` (`id_emisor`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;

INSERT INTO `usuario` (`id_usuario`, `id_emisor`, `nombre`, `correo`, `contrasena_hash`, `activo`, `fecha_registro`) VALUES
	(1, 1, "Tilin02", "tilinaso@gmail.com", "$2y$12$Y3aGijlswv2xu9/3yc1bhOkbu6IXXAduMYifKokOI3.28troCcpo2", 1, "2026-04-25 14:18:29");

/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of views
# ------------------------------------------------------------

# Creating temporary tables to overcome VIEW dependency errors


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

# Dump completed on 2026-04-26T20:57:13-06:00
