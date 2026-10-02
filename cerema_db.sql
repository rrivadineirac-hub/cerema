-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: cerema_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `cerema_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `cerema_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `cerema_db`;

--
-- Table structure for table `activos`
--

DROP TABLE IF EXISTS `activos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activos` (
  `id_activo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_activo` varchar(100) NOT NULL,
  `tipo_activo` enum('Inmueble','Maquinaria','Herramienta','Infraestructura','Otro') NOT NULL,
  `valor_estimado` decimal(12,2) DEFAULT 0.00,
  `fecha_adquisicion` date DEFAULT NULL,
  `estado_operativo` enum('Excelente','Bueno','Regular','En Mantenimiento','Fuera de Servicio') NOT NULL DEFAULT 'Bueno',
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id_activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activos`
--

LOCK TABLES `activos` WRITE;
/*!40000 ALTER TABLE `activos` DISABLE KEYS */;
/*!40000 ALTER TABLE `activos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aporte_extraordinario`
--

DROP TABLE IF EXISTS `aporte_extraordinario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aporte_extraordinario` (
  `id_aporte` int(11) NOT NULL AUTO_INCREMENT,
  `id_socio` int(11) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_aporte` date NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `numero_accion` int(11) DEFAULT 1,
  `numero_recibo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_aporte`),
  KEY `id_socio` (`id_socio`),
  CONSTRAINT `aporte_extraordinario_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `asociados` (`id_socio`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aporte_extraordinario`
--

LOCK TABLES `aporte_extraordinario` WRITE;
/*!40000 ALTER TABLE `aporte_extraordinario` DISABLE KEYS */;
INSERT INTO `aporte_extraordinario` VALUES (1,1,'CONSTRUCCION',500.00,'2026-08-25','2026-08-25 03:08:07',1,NULL),(4,1,'CONSTRUCCION',2000.00,'2026-08-25','2026-08-25 04:27:14',1,'000326'),(5,2,'CONSTRUCCION',500.00,'2026-08-25','2026-08-25 04:31:04',1,'000327');
/*!40000 ALTER TABLE `aporte_extraordinario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aportes_especiales`
--

DROP TABLE IF EXISTS `aportes_especiales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aportes_especiales` (
  `id_aporte_esp` int(11) NOT NULL AUTO_INCREMENT,
  `id_socio` int(11) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_aporte` date NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `numero_accion` int(11) DEFAULT 1,
  `numero_recibo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_aporte_esp`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aportes_especiales`
--

LOCK TABLES `aportes_especiales` WRITE;
/*!40000 ALTER TABLE `aportes_especiales` DISABLE KEYS */;
INSERT INTO `aportes_especiales` VALUES (1,1,'RIFA',10.00,'2026-08-25','2026-08-25 04:07:52',1,NULL);
/*!40000 ALTER TABLE `aportes_especiales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asociados`
--

DROP TABLE IF EXISTS `asociados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asociados` (
  `id_socio` int(11) NOT NULL AUTO_INCREMENT,
  `ci` varchar(20) NOT NULL,
  `complemento` varchar(10) DEFAULT '',
  `ap_paterno` varchar(25) NOT NULL,
  `ap_materno` varchar(25) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `acciones` int(2) DEFAULT 1,
  `cuota_inicial` decimal(10,2) NOT NULL DEFAULT 0.00,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `fechaRegistro` datetime DEFAULT NULL,
  `fechaEdicion` datetime DEFAULT NULL,
  `estado` enum('Activo','Inactivo','Pasivo') NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`id_socio`),
  UNIQUE KEY `ci` (`ci`)
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asociados`
--

LOCK TABLES `asociados` WRITE;
/*!40000 ALTER TABLE `asociados` DISABLE KEYS */;
INSERT INTO `asociados` VALUES (1,'SOC-01','AGUILERA','GALVIZ','JUAN JOSE',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(2,'SOC-02','ALVAREZ','','BERNARDO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(3,'SOC-03','ANTELO','ROJAS','JORGE ENRIQUE',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(4,'SOC-04','ARAUZ','MONTENEGRO','FATIMA ELFY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(5,'SOC-05','ARCE','PATIÑO','JUAN CARLOS',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(6,'SOC-06','ARCE','VDA DE ROCA','LOURDES',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(7,'SOC-07','ARCE','VELASCO','CARMEN PATRICIA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(8,'SOC-08','ARCE','VELASCO','OSCAR ANDRES',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(9,'SOC-09','ARCE','VELASCO','REINA ROSMERY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(10,'SOC-10','ARTEAGA','HERRERA','CARLOS',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(11,'SOC-11','BARRANCOS','SAAVEDRA','REMBERTO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(12,'SOC-12','BAZAN','HERRERA','ANA BEATRIZ',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(13,'SOC-13','BAZAN','HERRERA','CARMEN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(14,'SOC-14','BAZAN','HERRERA','GABRIELA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(15,'SOC-15','BAZAN','HERRERA','VICTOR HUGO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(16,'SOC-16','BAZAN','SUAREZ','ILDEBERTO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(17,'SOC-17','CALLEJAS','DE MONTAÑO','ELENA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(18,'SOC-18','CALLEJAS','DE ROCA','OLGA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(19,'SOC-19','CALLEJAS','VIUDA DE PEÑA','MATILDE',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(20,'SOC-20','CARDOZO','NUÑEZ','ABEL',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(21,'SOC-21','CONCHARI','DE ROMERO','EDITH',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(22,'SOC-22','CORTEZ','ARTEAGA','BEIMAR',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(23,'SOC-23','CORTEZ','ARTEAGA','MAXIMO ALEJANDRO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(24,'SOC-24','CORTEZ','ARTEAGA','RONALD',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(25,'SOC-25','CORTEZ','GUTIERREZ','FREDDY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(26,'SOC-26','CORTEZ','GUTIERREZ','HERNAN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(27,'SOC-27','CORTEZ','LINO','ROLY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(28,'SOC-28','CORTEZ','SENAS','DAMICEL',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(29,'SOC-29','CUELLAR','CUELLAR','ENRIQUE',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(30,'SOC-30','CUELLAR','ROJAS','BLADIMIR',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(31,'SOC-31','EID','ERLAN','ADALY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(32,'SOC-32','ENCINAS','OLIVA','ROBERTO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(33,'SOC-33','FERNANDEZ','DE HURTADO','LIZ GERALDINE',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(34,'SOC-34','FERNANDEZ','GUZMAN','TATIANA SHIRLEY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(35,'SOC-35','FERNANDEZ','LORENA','DINA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(36,'SOC-36','FERREIRA','VERA','CESAR ANDRE',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(37,'SOC-37','FIERRO','DE SEJAS','MARIA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(38,'SOC-38','GARCIA','HURTADO','ROSARIO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(39,'SOC-39','GONZALES','MONTENEGRO','VICTOR HUGO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(40,'SOC-40','GONZALES','MONTENEGRO','WILLAMS',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(41,'SOC-41','GUTIERREZ','CABALLERO','CARLOS ALFREDO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(42,'SOC-42','GUTIERREZ','CABALLERO','JORGE LUIS',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(43,'SOC-43','GUTIERREZ','MONTAÑO','EDGAR JHON',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(44,'SOC-44','GUTIERREZ','MONTAÑO','JHON WILLIAMS',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(45,'SOC-45','GUZMAN','SALDIAS','CARMEN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(46,'SOC-46','GUZMAN','SALDIAS','CARMEN Nº2',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(47,'SOC-47','HERRERA','DE MONTENEGRO','DENNY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(48,'SOC-48','HERRERA','PADILLA','ALVERCIO SERGIO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(49,'SOC-49','HERRERA','VARGAS','AUGUSTO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(50,'SOC-50','HINOJOSA','CARMONA','VICTOR CELIN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(51,'SOC-51','HINOJOSA','NUÑEZ','VICTOR RODRIGO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(52,'SOC-52','HINOJOSA','ROCA','KEVIN RICARDO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(53,'SOC-53','HURTADO','LINO','EDUARDO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(54,'SOC-54','HURTADO','LINO','JAIME',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(55,'SOC-55','JORDAN','PEREIRA','EDER',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(56,'SOC-56','LINO','MORALES','GUEIDER',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(57,'SOC-57','LINO','MORALES','RUBI',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(58,'SOC-58','LINO','MORALES','WILSON',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(59,'SOC-59','LUJAN','PATIÑO','JOSE FELIX',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(60,'SOC-60','MACHUCA','CALLEJAS','MARIA NIEVES',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(61,'SOC-61','MARIACA','FLORES','LUCIANA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(62,'SOC-62','MARIACA','MONTENEGRO','ROGER RIDER',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(63,'SOC-63','MENDOZA','CHAVEZ','DERY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(64,'SOC-64','MENDOZA','CHAVEZ','ROSARIO FELICIDAD',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(65,'SOC-65','MENDOZA','FERNANDEZ','JOSE LITO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(66,'SOC-66','MENDOZA','AGUILERA','GERARDO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(67,'SOC-67','MENDOZA','MONTENEGRO','YOVANA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(68,'SOC-68','MONTAÑO','CALLEJAS','ELENA MARGARITA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(69,'SOC-69','MONTAÑO','CALLEJAS','GABRIEL RODRIGO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(70,'SOC-70','MONTAÑO','CALLEJAS','IGNACIO SMITH',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(71,'SOC-71','MONTAÑO','CALLEJAS','LUIS ALBERTO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(72,'SOC-72','MONTAÑO','HERRERA','NORMA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(73,'SOC-73','MONTAÑO','PEÑA','JUNISSE',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(74,'SOC-74','MONTAÑO','TERCEROS','MARIA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(75,'SOC-75','MONTENEGRO','PATIÑO','GENARO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(76,'SOC-76','MONTENEGRO','PATIÑO','LIDIA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(77,'SOC-77','MONTENEGRO','PATIÑO','ROSARIO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(78,'SOC-78','NAVIA','VEIZAGA','POLICARPIO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(79,'SOC-79','NAZARIO','ARCE','PABLO CESAR',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(80,'SOC-80','NUMBELA','ARCE','JOSUE',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(81,'SOC-81','NUMBELA','ARCE','VALENTINA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(82,'SOC-82','NUÑEZ','ALBA','WALTER ERVIN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(83,'SOC-83','NUÑEZ','PAREDES','FRANCISCO XAVIER',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(84,'SOC-84','NUÑEZ','VILLAGOMEZ','REINA CAROLINA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(85,'SOC-85','ORTUÑO','HERRERA','JORGE LUIS',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(86,'SOC-86','PADILLA','CUELLAR','ELVIS',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(87,'SOC-87','PARDO','QUIROZ','YANETH',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(88,'SOC-88','PARRA','DE HERRERA','NELFY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(89,'SOC-89','PAZ','SAAVEEDRA','VANESA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(90,'SOC-90','PEÑA','CALLEJAS','NELCY JUDITH',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(91,'SOC-91','PEÑA','SOLAR','GABRIEL',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(92,'SOC-92','PEÑA','SOLAR','JULIO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(93,'SOC-93','PEREZ','ROCHA','DANNA CAMILA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(94,'SOC-94','PEREZ','ROCHA','DYLAN AGUSTIN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(95,'SOC-95','PEREZ','ROCHA','ELENA GABRIELA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(96,'SOC-96','PIMENTEL','CORTEZ','EVELIN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(97,'SOC-97','PIMENTEL','CORTEZ','YESENIA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(98,'SOC-98','PIMENTEL','PATIÑO','JORGE',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(99,'SOC-99','PIMENTEL','PATIÑO','MILRLEN GABRIELA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(100,'SOC-100','PRADO','ARCE','ALICIA DEL CARMEN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(101,'SOC-101','QUINTELA','CABRERA','CARLOS',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(102,'SOC-102','QUINTEROS','','JAIME',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(103,'SOC-103','RIVAS','VDA DE MANSILLA','MERY NARDY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(104,'SOC-104','ROBLES','SOMOYA','EMILIO ELIAS',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(105,'SOC-105','ROBLES','VELEZ','MATHIAS',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(106,'SOC-106','ROCA','CALLEJAS','JUAN DE DIOS',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(107,'SOC-107','ROCA','CALLEJAS','MARIA LORENA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(108,'SOC-108','ROCA','DE HINOJOSA','MARIA DEL CARMEN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(109,'SOC-109','ROCA','DE SANCHEZ','WILMA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(110,'SOC-110','ROCA','MENDOZA','JUAN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(111,'SOC-111','ROJAS','ZURITA','JUAN PABLO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(112,'SOC-112','ROMERO','DE CARDOZO','SONIA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(113,'SOC-113','ROMERO','SAAVEDRA','GOLDY YOBANA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(114,'SOC-114','ROMERO','SINGO','JUAN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(115,'SOC-115','ROSALES','DE ZEBALLOS','LIDIA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(116,'SOC-116','RUEDA','PRADO','DIEGO ALONZO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(117,'SOC-117','SAAVEDRA','ARTEAGA','ISIDRO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(118,'SOC-118','SAAVEDRA','MIREISA','JESUS',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(119,'SOC-119','SAAVEDRA','ROMERO','AURORA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(120,'SOC-120','SAAVEDRA','SANCHEZ','ROSADRIO HEYDI',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(121,'SOC-121','SALDIAS','CALZADILLAS','ROGER',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(122,'SOC-122','SALDIAS','CALZADILLAS','WILSON HUMBERTO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(123,'SOC-123','SALDIAS','LEON','HERNANDO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(124,'SOC-124','SANABRIA','FLORES','JOSE JULIO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(125,'SOC-125','SANCHEZ','PRADO','DILLMA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(126,'SOC-126','SANCHEZ','ROCA','ERICKA MARIOLY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(127,'SOC-127','SANCHEZ','ROCA','SANDRA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(128,'SOC-128','SEJAS','GARCIA','CARLA PAOLA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(129,'SOC-129','SEJAS','ROMERO','OSVALDO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(130,'SOC-130','SEJAS','VELASCO','STEPHANY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(131,'SOC-131','SENAS','CONTRERAS','EDILBERTO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(132,'SOC-132','SENAS','CORTEZ','YARITA ANDREA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(133,'SOC-133','TALAMAS','MENDOZA','ROBIN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(134,'SOC-134','TERAN','AGUILAR','MARIFLOR',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(135,'SOC-135','TERAN','AGUILAR','ROBIN DAVID',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(136,'SOC-136','TERAN','AGUILAR','SANTIAGO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(137,'SOC-137','TERAN','AGUILAR','WENDY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(138,'SOC-138','TERAN','RIFARACHY','SANTIAGO',5,'','','2025-08-01','2026-08-24 09:26:04',NULL,'2026-08-25 08:44:32','Activo'),(139,'SOC-139','TERCEROS','RIBERA','ALQUILINA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(140,'SOC-140','VELASCO','DE ARCE','ROXANA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(141,'SOC-141','VELASCO','PRADO','ROSMERY',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(142,'SOC-142','VELEZ','','EVER',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(143,'SOC-143','VELEZ','','EVELIN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(144,'SOC-144','VERA','ARCE','MARI JAZMIN',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(145,'SOC-145','VERA','ARCE','PABLO ANDRES',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(146,'SOC-146','VERA','CABALLERO','KARINA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(147,'SOC-147','VILLAGOMEZ','FLORES','MARIA LOURDES',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(148,'SOC-148','VILLAGOMEZ','FLORES','ROBIN',5,'','','2025-08-01','2026-08-24 09:26:04',NULL,'2026-08-25 08:43:07','Activo'),(149,'SOC-149','VILLAGOMEZ','FLORES','MARIA ELISA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(150,'SOC-150','ZEBALLOS','CONTRERAS','ALVARO',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(151,'SOC-151','ZEBALLOS','NUÑEZ','ZACARIAS',2,'','','2025-08-01','2026-08-24 09:26:04',NULL,'2026-08-25 08:43:43','Activo'),(152,'SOC-152','ZEBALLOS','NUÑEZ','ZACARIAS Nº2',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo'),(153,'SOC-153','ZEBALLOS','ROSALES','CAROLA',1,NULL,NULL,'2025-08-01','2026-08-24 09:26:04',NULL,NULL,'Activo');
/*!40000 ALTER TABLE `asociados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `balances`
--

DROP TABLE IF EXISTS `balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `balances` (
  `id_balance` int(11) NOT NULL AUTO_INCREMENT,
  `id_gestion` int(11) NOT NULL,
  `tipo_balance` enum('Apertura','Mensual','Semestral','Anual') NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `total_ingresos` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_gastos` decimal(12,2) NOT NULL DEFAULT 0.00,
  `saldo_neto` decimal(12,2) GENERATED ALWAYS AS (`total_ingresos` - `total_gastos`) STORED,
  `fecha_generacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id_balance`),
  KEY `id_gestion` (`id_gestion`),
  CONSTRAINT `balances_ibfk_1` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `balances`
--

LOCK TABLES `balances` WRITE;
/*!40000 ALTER TABLE `balances` DISABLE KEYS */;
/*!40000 ALTER TABLE `balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cargos`
--

DROP TABLE IF EXISTS `cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cargos` (
  `id_cargo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_cargo` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_cargo`),
  UNIQUE KEY `nombre_cargo` (`nombre_cargo`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargos`
--

LOCK TABLES `cargos` WRITE;
/*!40000 ALTER TABLE `cargos` DISABLE KEYS */;
INSERT INTO `cargos` VALUES (1,'Presidente',NULL),(2,'Vicepresidente',NULL),(3,'Secretario General',NULL),(4,'Secretario de Actas',NULL),(5,'Secretario de Hacienda',NULL),(6,'Secretario Planificaci¾n',NULL),(7,'Secretario de Cultura y deportes',NULL),(8,'1er Vocal',NULL),(9,'2do Vocal',NULL);
/*!40000 ALTER TABLE `cargos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias_gasto`
--

DROP TABLE IF EXISTS `categorias_gasto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias_gasto` (
  `id_cat_gasto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `grupo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_cat_gasto`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_gasto`
--

LOCK TABLES `categorias_gasto` WRITE;
/*!40000 ALTER TABLE `categorias_gasto` DISABLE KEYS */;
/*!40000 ALTER TABLE `categorias_gasto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias_ingreso`
--

DROP TABLE IF EXISTS `categorias_ingreso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias_ingreso` (
  `id_cat_ingreso` int(11) NOT NULL AUTO_INCREMENT,
  `id_gestion` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `requiere_socio` tinyint(1) NOT NULL DEFAULT 1,
  `monto_sugerido` decimal(10,2) DEFAULT 0.00,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_cat_ingreso`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_ingreso`
--

LOCK TABLES `categorias_ingreso` WRITE;
/*!40000 ALTER TABLE `categorias_ingreso` DISABLE KEYS */;
INSERT INTO `categorias_ingreso` VALUES (4,1,'Pago de Mensualidades de socios',1,50.00,'Cuotas ordinarias de los Miembros'),(5,1,'CONSTRUCCION',1,2500.00,''),(6,1,'RIFA',2,20.00,'');
/*!40000 ALTER TABLE `categorias_ingreso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos`
--

DROP TABLE IF EXISTS `eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventos` (
  `id_evento` int(11) NOT NULL AUTO_INCREMENT,
  `id_gestion` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `tipo_evento` varchar(255) NOT NULL,
  `fecha_evento` date NOT NULL,
  `hora_evento` time DEFAULT NULL,
  `lugar` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('Programado','Realizado','Cancelado') DEFAULT 'Programado',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_evento`),
  KEY `id_gestion` (`id_gestion`),
  CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`id_gestion`) REFERENCES `gestiones` (`id_gestion`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos`
--

LOCK TABLES `eventos` WRITE;
/*!40000 ALTER TABLE `eventos` DISABLE KEYS */;
INSERT INTO `eventos` VALUES (1,1,'Reunion Mensual','Reunion Mensual','2026-08-25','20:00:00','CASA DEL SOCIO','','Realizado','2026-08-25 11:37:45'),(2,1,'Fiesta Aniversario','Fiesta de Aniversario','2026-11-28','20:00:00','FRATERNIDAD CEREMA','','Programado','2026-08-25 11:55:33'),(3,1,'KERMESSE','KERMESSE','2026-10-25','10:00:00','CASA DEL SOCIO','','Cancelado','2026-08-25 12:05:02');
/*!40000 ALTER TABLE `eventos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gastos`
--

DROP TABLE IF EXISTS `gastos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gastos` (
  `id_gasto` int(11) NOT NULL AUTO_INCREMENT,
  `id_cat_gasto` int(11) NOT NULL,
  `id_activo` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_gasto` datetime NOT NULL DEFAULT current_timestamp(),
  `concepto_detalle` varchar(255) NOT NULL,
  `proveedor_beneficiario` varchar(150) DEFAULT NULL,
  `num_factura_recibo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_gasto`),
  KEY `id_cat_gasto` (`id_cat_gasto`),
  KEY `id_activo` (`id_activo`),
  CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`id_cat_gasto`) REFERENCES `categorias_gasto` (`id_cat_gasto`) ON UPDATE CASCADE,
  CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`id_activo`) REFERENCES `activos` (`id_activo`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gastos`
--

LOCK TABLES `gastos` WRITE;
/*!40000 ALTER TABLE `gastos` DISABLE KEYS */;
/*!40000 ALTER TABLE `gastos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gestiones`
--

DROP TABLE IF EXISTS `gestiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gestiones` (
  `id_gestion` int(11) NOT NULL AUTO_INCREMENT,
  `gestion` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('Planificada','En Curso','Cerrada') NOT NULL DEFAULT 'Planificada',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_gestion`),
  UNIQUE KEY `gestion` (`gestion`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gestiones`
--

LOCK TABLES `gestiones` WRITE;
/*!40000 ALTER TABLE `gestiones` DISABLE KEYS */;
INSERT INTO `gestiones` VALUES 
(1,2024,'2024-01-01','2024-12-31','Cerrada','2026-08-25 06:56:22'),
(8,2025,'2025-01-01','2025-12-31','Cerrada','2026-09-09 17:39:33'),
(9,2026,'2026-01-01','2026-12-31','En Curso','2026-09-09 17:39:33'),
(10,2027,'2027-01-01','2027-12-31','Planificada','2026-09-09 18:15:26');
/*!40000 ALTER TABLE `gestiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingresos`
--

DROP TABLE IF EXISTS `ingresos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ingresos` (
  `id_ingreso` int(11) NOT NULL AUTO_INCREMENT,
  `id_cat_ingreso` int(11) NOT NULL,
  `id_socio` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` datetime NOT NULL DEFAULT current_timestamp(),
  `concepto` varchar(255) NOT NULL,
  `metodo_pago` enum('Efectivo','Transferencia','QR','Otro') NOT NULL DEFAULT 'Efectivo',
  `comprobante_num` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_ingreso`),
  KEY `id_cat_ingreso` (`id_cat_ingreso`),
  KEY `id_socio` (`id_socio`),
  CONSTRAINT `ingresos_ibfk_1` FOREIGN KEY (`id_cat_ingreso`) REFERENCES `categorias_ingreso` (`id_cat_ingreso`) ON UPDATE CASCADE,
  CONSTRAINT `ingresos_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `asociados` (`id_socio`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingresos`
--

LOCK TABLES `ingresos` WRITE;
/*!40000 ALTER TABLE `ingresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `ingresos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensualidad`
--

DROP TABLE IF EXISTS `mensualidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensualidad` (
  `id_mensualidad` int(11) NOT NULL AUTO_INCREMENT,
  `id_socio` int(11) NOT NULL,
  `mes` varchar(20) NOT NULL,
  `anio` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `numero_resivo` varchar(10) DEFAULT NULL,
  `fecha_pago` datetime DEFAULT current_timestamp(),
  `numero_accion` int(11) DEFAULT 1,
  `numero_recibo` varchar(50) DEFAULT NULL,
  `estado` enum('Pagado','Pendiente','Anulado') DEFAULT 'Pagado',
  `mensualidadcol` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_mensualidad`),
  KEY `id_socio` (`id_socio`),
  CONSTRAINT `mensualidad_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `asociados` (`id_socio`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensualidad`
--

LOCK TABLES `mensualidad` WRITE;
/*!40000 ALTER TABLE `mensualidad` DISABLE KEYS */;
INSERT INTO `mensualidad` VALUES (1,138,'Agosto',2026,50.00,NULL,'2026-08-24 00:00:00',1,'000325','Pagado',NULL);
/*!40000 ALTER TABLE `mensualidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesa_directiva`
--

DROP TABLE IF EXISTS `mesa_directiva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesa_directiva` (
  `id_directiva` int(11) NOT NULL AUTO_INCREMENT,
  `gestion` int(11) NOT NULL,
  `id_socio` int(11) NOT NULL,
  `id_cargo` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  PRIMARY KEY (`id_directiva`),
  UNIQUE KEY `uk_socio_cargo_gestion` (`gestion`,`id_cargo`),
  KEY `id_socio` (`id_socio`),
  KEY `id_cargo` (`id_cargo`),
  CONSTRAINT `mesa_directiva_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `asociados` (`id_socio`) ON UPDATE CASCADE,
  CONSTRAINT `mesa_directiva_ibfk_3` FOREIGN KEY (`id_cargo`) REFERENCES `cargos` (`id_cargo`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesa_directiva`
--

LOCK TABLES `mesa_directiva` WRITE;
/*!40000 ALTER TABLE `mesa_directiva` DISABLE KEYS */;
INSERT INTO `mesa_directiva` VALUES (1,2026,138,1,'2026-08-26',NULL);
/*!40000 ALTER TABLE `mesa_directiva` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otros_gastos`
--

DROP TABLE IF EXISTS `otros_gastos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `otros_gastos` (
  `id_otro_gasto` int(11) NOT NULL AUTO_INCREMENT,
  `id_gestion` int(11) DEFAULT NULL,
  `nombre_gasto` varchar(255) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  `unidad_medida` varchar(50) DEFAULT '',
  `cantidad` decimal(10,2) DEFAULT 1.00,
  `precio` decimal(10,2) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `comprobante` varchar(100) DEFAULT NULL,
  `estado` enum('Pagado','Anulado') DEFAULT 'Pagado',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_otro_gasto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otros_gastos`
--

LOCK TABLES `otros_gastos` WRITE;
/*!40000 ALTER TABLE `otros_gastos` DISABLE KEYS */;
/*!40000 ALTER TABLE `otros_gastos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otros_ingresos`
--

DROP TABLE IF EXISTS `otros_ingresos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `otros_ingresos` (
  `id_otro_ingreso` int(11) NOT NULL AUTO_INCREMENT,
  `id_gestion` int(11) NOT NULL,
  `detalle` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `comprobante` varchar(100) DEFAULT NULL,
  `estado` enum('Cobrado','Pagado','Anulado') DEFAULT 'Cobrado',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_otro_ingreso`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otros_ingresos`
--

LOCK TABLES `otros_ingresos` WRITE;
/*!40000 ALTER TABLE `otros_ingresos` DISABLE KEYS */;
INSERT INTO `otros_ingresos` VALUES (1,1,'ALQUIELE PICINA',300.00,'2026-08-27','00345','Cobrado','2026-08-27 15:27:50');
/*!40000 ALTER TABLE `otros_ingresos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servicios_basicos`
--

DROP TABLE IF EXISTS `servicios_basicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servicios_basicos` (
  `id_servicio` int(11) NOT NULL AUTO_INCREMENT,
  `id_gestion` int(11) DEFAULT NULL,
  `tipo_servicio` enum('Electricidad','Agua','Telefonia') NOT NULL,
  `mes_pago` varchar(50) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `comprobante` varchar(100) DEFAULT NULL,
  `estado` enum('Pagado','Anulado') DEFAULT 'Pagado',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_servicio`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servicios_basicos`
--

LOCK TABLES `servicios_basicos` WRITE;
/*!40000 ALTER TABLE `servicios_basicos` DISABLE KEYS */;
/*!40000 ALTER TABLE `servicios_basicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sueldos`
--

DROP TABLE IF EXISTS `sueldos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sueldos` (
  `id_sueldo` int(11) NOT NULL AUTO_INCREMENT,
  `id_gestion` int(11) DEFAULT NULL,
  `cargo` varchar(100) NOT NULL,
  `empleado` varchar(150) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `comprobante` varchar(100) DEFAULT NULL,
  `estado` enum('Pagado','Anulado') DEFAULT 'Pagado',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_sueldo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sueldos`
--

LOCK TABLES `sueldos` WRITE;
/*!40000 ALTER TABLE `sueldos` DISABLE KEYS */;
/*!40000 ALTER TABLE `sueldos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios_sistema`
--

DROP TABLE IF EXISTS `usuarios_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios_sistema` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `id_socio` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol_sistema` enum('Administrador','Presidente','Tesorero','Secretario','Socio') NOT NULL DEFAULT 'Socio',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `id_socio` (`id_socio`),
  CONSTRAINT `usuarios_sistema_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `asociados` (`id_socio`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios_sistema`
--

LOCK TABLES `usuarios_sistema` WRITE;
/*!40000 ALTER TABLE `usuarios_sistema` DISABLE KEYS */;
INSERT INTO `usuarios_sistema` VALUES (2,NULL,'Infoser76','$2y$10$SoXbqzLOK3fuTuMQ1mIVd.vcw6sfPFeyMp4zshgN7W59vTXWjOMUe','Administrador',1,'2026-08-24 09:44:01');
/*!40000 ALTER TABLE `usuarios_sistema` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-27 15:32:34
