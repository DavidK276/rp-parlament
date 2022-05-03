-- MariaDB dump 10.19  Distrib 10.6.5-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: parlament
-- ------------------------------------------------------
-- Server version	10.6.5-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `parlament`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `parlament` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `parlament`;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_udaje` int(11) DEFAULT NULL,
  `id_previerka` int(11) DEFAULT NULL,
  `heslo` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_previerka` (`id_previerka`),
  KEY `id_udaje` (`id_udaje`),
  CONSTRAINT `admin_ibfk_2` FOREIGN KEY (`id_previerka`) REFERENCES `bezp_previerka` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `admin_ibfk_3` FOREIGN KEY (`id_udaje`) REFERENCES `osobne_udaje` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,1,NULL,'$2y$10$haJYoPWSflcbs83Svf1P/ORdRRD26IRweXa8Bw9liBfkyXJmn5Wmm');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bezp_previerka`
--

DROP TABLE IF EXISTS `bezp_previerka`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bezp_previerka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uroven` enum('Tajné','Prísne tajné','Najprísnejšie tajné') NOT NULL,
  `kto_udelil` int(11) NOT NULL,
  `datum` date NOT NULL DEFAULT current_timestamp(),
  `platnost` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`),
  KEY `kto_udelil` (`kto_udelil`),
  CONSTRAINT `bezp_previerka_ibfk_1` FOREIGN KEY (`kto_udelil`) REFERENCES `admin` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bezp_previerka`
--

LOCK TABLES `bezp_previerka` WRITE;
/*!40000 ALTER TABLE `bezp_previerka` DISABLE KEYS */;
/*!40000 ALTER TABLE `bezp_previerka` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osobne_udaje`
--

DROP TABLE IF EXISTS `osobne_udaje`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osobne_udaje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL,
  `meno` varchar(30) NOT NULL,
  `priezvisko` varchar(30) NOT NULL,
  `adresa` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `osobne_udaje`
--

LOCK TABLES `osobne_udaje` WRITE;
/*!40000 ALTER TABLE `osobne_udaje` DISABLE KEYS */;
INSERT INTO `osobne_udaje` VALUES (1,'test@example.com','David','Krchňavý','Testovacia adresa 20, Bratislava'),(5,'jozko@test.com','Jožko','Púčik','Pod mostom SNP v Bratislave');
/*!40000 ALTER TABLE `osobne_udaje` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poslanec`
--

DROP TABLE IF EXISTS `poslanec`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poslanec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_udaje` int(11) DEFAULT NULL,
  `id_klub` int(11) NOT NULL DEFAULT 1 COMMENT 'predvolene je 1, co je nezaradeny',
  `id_previerka` int(11) DEFAULT NULL,
  `specializacia` set('Financie','Ekonomika','Zdravotníctvo','Zahraničná politika','Bezpečnosť','Životné prostredie','Právo','Kultúra','Vzdelanie') DEFAULT NULL,
  `titul` varchar(20) NOT NULL,
  `heslo` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_klub` (`id_klub`),
  KEY `id_previerka` (`id_previerka`),
  KEY `id_udaje` (`id_udaje`),
  CONSTRAINT `poslanec_ibfk_2` FOREIGN KEY (`id_klub`) REFERENCES `poslanecky_klub` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `poslanec_ibfk_3` FOREIGN KEY (`id_previerka`) REFERENCES `bezp_previerka` (`id`) ON DELETE NO ACTION,
  CONSTRAINT `poslanec_ibfk_4` FOREIGN KEY (`id_udaje`) REFERENCES `osobne_udaje` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poslanec`
--

LOCK TABLES `poslanec` WRITE;
/*!40000 ALTER TABLE `poslanec` DISABLE KEYS */;
INSERT INTO `poslanec` VALUES (5,5,1,NULL,'Zahraničná politika,Bezpečnosť','Bla.','$2y$10$5tV7qMSyhXFZUv104dJZq.55q1JFuvlhV8yLWrs7R1M1Exs6sswUu');
/*!40000 ALTER TABLE `poslanec` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poslanecky_klub`
--

DROP TABLE IF EXISTS `poslanecky_klub`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poslanecky_klub` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nazov` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poslanecky_klub`
--

LOCK TABLES `poslanecky_klub` WRITE;
/*!40000 ALTER TABLE `poslanecky_klub` DISABLE KEYS */;
INSERT INTO `poslanecky_klub` VALUES (1,'Nezaradený');
/*!40000 ALTER TABLE `poslanecky_klub` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-05-03  7:57:32
