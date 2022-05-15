-- Adminer 4.8.1 MySQL 10.5.15-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS `parlament`;
CREATE DATABASE `parlament` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `parlament`;

DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_udaje` int(11) DEFAULT NULL,
  `heslo` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_udaje` (`id_udaje`),
  CONSTRAINT `admin_ibfk_3` FOREIGN KEY (`id_udaje`) REFERENCES `osobne_udaje` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin` (`id`, `id_udaje`, `heslo`) VALUES
(1,	1,	'$2y$10$q3u.0dGG4Y14Kq/NiukMnOhFzHoxcgsl3HJJwsemy0uBdTzNN2oR.'),
(3,	6,	'$2y$10$I4odyis78SBKmWhO4T3MO.6FYHqxOn98hEhVKvS.R0Q0UNGP9Mqci');

DROP TABLE IF EXISTS `bezp_previerka`;
CREATE TABLE `bezp_previerka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uroven` enum('Tajné','Prísne tajné','Najprísnejšie tajné') NOT NULL,
  `kto_udelil` int(11) NOT NULL,
  `datum` date NOT NULL DEFAULT current_timestamp(),
  `platnost` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`),
  KEY `kto_udelil` (`kto_udelil`),
  CONSTRAINT `bezp_previerka_ibfk_1` FOREIGN KEY (`kto_udelil`) REFERENCES `admin` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

INSERT INTO `bezp_previerka` (`id`, `uroven`, `kto_udelil`, `datum`, `platnost`) VALUES
(1,	'Prísne tajné',	1,	'2022-05-09',	CONV('1', 2, 10) + 0),
(2,	'Najprísnejšie tajné',	1,	'2022-05-12',	CONV('1', 2, 10) + 0),
(3,	'Tajné',	1,	'2022-05-13',	CONV('0', 2, 10) + 0),
(4,	'Najprísnejšie tajné',	1,	'2022-05-15',	CONV('1', 2, 10) + 0),
(5,	'Tajné',	1,	'2022-05-15',	CONV('1', 2, 10) + 0);

DROP TABLE IF EXISTS `osobne_udaje`;
CREATE TABLE `osobne_udaje` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_previerka` int(11) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `titul` varchar(20) NOT NULL DEFAULT '',
  `meno` varchar(30) NOT NULL,
  `priezvisko` varchar(30) NOT NULL,
  `adresa` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `id_previerka` (`id_previerka`),
  CONSTRAINT `osobne_udaje_ibfk_3` FOREIGN KEY (`id_previerka`) REFERENCES `bezp_previerka` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;

INSERT INTO `osobne_udaje` (`id`, `id_previerka`, `email`, `titul`, `meno`, `priezvisko`, `adresa`) VALUES
(1,	NULL,	'test@example.com',	'',	'David',	'Krchňavý',	'Cyprichova 44,\r\n831 06 Bratislava,\r\nSlovenská republika'),
(5,	3,	'jozko@test.com',	'',	'Jožko',	'Púčik',	'Pod mostom SNP v Bratislave'),
(6,	2,	'vajda@gmail.com',	'',	'Jozef',	'Vajda',	'Hodžovo námesie 1,\r\n820 11 Bratislava'),
(8,	NULL,	'trava@uniba.sk',	'Mgr. et Mgr.',	'Ján',	'Tráva',	'Ulica tankistov 14, \r\n011 01 Žilina'),
(9,	1,	'rychly@uniba.sk',	'',	'Andrej',	'Rýchly',	'Pri stožiari 86,\r\n821 05 Bratislava'),
(11,	5,	'adus@uniba.sk',	'',	'Aduš',	'Domanický',	'Kozia ulica 12,\r\n830 25 Bratislava\r\nSlovensko'),
(13,	NULL,	'hrasko@priklad.sk',	'',	'Janko',	'Hraško',	'Zimná ulica 10,\r\n123 45 Horný Štvrtok'),
(14,	NULL,	'vcela@uniba.sk',	'',	'Rudolf',	'Včela',	'Dlhá ulica 799,\r\n123 45 Slovenský grob'),
(15,	NULL,	'rosa@uniba.sk',	'',	'Daniel',	'Rosa',	'Zimná ulica 72,\r\n123 45 Rosina'),
(16,	4,	'hrusicky@uniba.sk',	'',	'Peter',	'Hrušický',	'Pod malým vŕškom 12,\r\n678 90 Veľký Krtíš');

DELIMITER ;;

CREATE TRIGGER `osobne_udaje_ad` AFTER DELETE ON `osobne_udaje` FOR EACH ROW
BEGIN DELETE FROM bezp_previerka WHERE id=OLD.id_previerka; END;;

DELIMITER ;

DROP TABLE IF EXISTS `poslanec`;
CREATE TABLE `poslanec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_udaje` int(11) DEFAULT NULL,
  `id_klub` int(11) NOT NULL DEFAULT 1 COMMENT 'predvolene je 1, co je nezaradeny',
  `specializacia` set('Financie','Ekonomika','Zdravotníctvo','Zahraničná politika','Vnútroštátna bezpečnosť','Životné prostredie','Právo','Kultúra','Vzdelanie') DEFAULT NULL,
  `heslo` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_udaje` (`id_udaje`),
  KEY `id_klub` (`id_klub`),
  CONSTRAINT `poslanec_ibfk_2` FOREIGN KEY (`id_klub`) REFERENCES `poslanecky_klub` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `poslanec_ibfk_5` FOREIGN KEY (`id_udaje`) REFERENCES `osobne_udaje` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

INSERT INTO `poslanec` (`id`, `id_udaje`, `id_klub`, `specializacia`, `heslo`) VALUES
(5,	5,	1,	'Financie,Zahraničná politika',	'$2y$10$5tV7qMSyhXFZUv104dJZq.55q1JFuvlhV8yLWrs7R1M1Exs6sswUu'),
(6,	8,	6,	'Financie,Ekonomika,Zahraničná politika,Vnútroštátna bezpečnosť,Právo',	'$2y$10$pPz2cRNxZVYUOMFLl3hUdeFD.AV0LQLbyB0B4erFnZoyTQ4NAs1Pu'),
(7,	9,	7,	'',	'$2y$10$Z5UfT/K5tT5oMCAeLMgF1e1AeJXUixvTHJyTB6MndoauElzvFiwI2'),
(9,	11,	5,	'Zahraničná politika,Právo',	'$2y$10$5xV5cPqiaRb2VBhN/aRchOsdKkaOtSSFEED8p4m8J22n19p8QFp6e'),
(10,	13,	4,	'Kultúra',	'$2y$10$JSbGSm3m4B3dwxdz54R8GuxhyD0Zx3AzQEULwjktRMkG7eXqGQ/Wu'),
(11,	14,	6,	'Financie,Ekonomika,Zdravotníctvo,Zahraničná politika,Vnútroštátna bezpečnosť,Životné prostredie,Právo,Kultúra,Vzdelanie',	'$2y$10$bAztug/eP2F9mBztLuH/peOte/tsnRbwgV379ULrifSOuxuatWWBq'),
(12,	15,	7,	'',	'$2y$10$dFzMaPj.Ff3xb/UGAPcWEe4iSgmaOHAatCoi/aHkIVVCuWr12gYTy'),
(13,	16,	1,	'Vzdelanie',	'$2y$10$BQkHXS6vtrt.ldwvfh1zT.6reyVnt6UtDyPK32ziSmKAyiO/QZnai');

DROP TABLE IF EXISTS `poslanecky_klub`;
CREATE TABLE `poslanecky_klub` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nazov` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

INSERT INTO `poslanecky_klub` (`id`, `nazov`) VALUES
(1,	'Nezaradení'),
(4,	'Ovocinári'),
(5,	'Zeleninári'),
(6,	'Cukrovinkári'),
(7,	'Pekári');

-- 2022-05-15 14:11:10
