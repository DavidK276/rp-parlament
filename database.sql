-- Adminer 4.8.1 MySQL 10.6.5-MariaDB-log dump

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin` (`id`, `id_udaje`, `heslo`) VALUES
(1,	1,	'$2y$10$haJYoPWSflcbs83Svf1P/ORdRRD26IRweXa8Bw9liBfkyXJmn5Wmm'),
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `bezp_previerka` (`id`, `uroven`, `kto_udelil`, `datum`, `platnost`) VALUES
(1,	'Prísne tajné',	1,	'2022-05-09',	CONV('1', 2, 10) + 0);

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
  CONSTRAINT `osobne_udaje_ibfk_1` FOREIGN KEY (`id_previerka`) REFERENCES `bezp_previerka` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `osobne_udaje` (`id`, `id_previerka`, `email`, `titul`, `meno`, `priezvisko`, `adresa`) VALUES
(1,	NULL,	'test@example.com',	'',	'David',	'Krchňavý',	'Testovacia adresa 20, Bratislava'),
(5,	NULL,	'jozko@test.com',	'',	'Jožko',	'Púčik',	'Pod mostom SNP v Bratislave'),
(6,	NULL,	'vajda@jozko.sk',	'',	'Jožko',	'Vajda',	'Bejby'),
(8,	NULL,	'slopa@znamafirma.xyz',	'',	'Ján',	'Slopa',	'Tankistov, Žilina'),
(9,	1,	'danko@example.com',	'Kpt. Plg.',	'Andrej',	'Danko',	'Stožiarová ulica, Bratislava'),
(10,	NULL,	'testovac@example.com',	'',	'Test',	'Testu',	'Testovacia');

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
  CONSTRAINT `poslanec_ibfk_4` FOREIGN KEY (`id_udaje`) REFERENCES `osobne_udaje` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `poslanec` (`id`, `id_udaje`, `id_klub`, `specializacia`, `heslo`) VALUES
(5,	5,	1,	'Zahraničná politika',	'$2y$10$5tV7qMSyhXFZUv104dJZq.55q1JFuvlhV8yLWrs7R1M1Exs6sswUu'),
(6,	8,	1,	'Zahraničná politika,Vnútroštátna bezpečnosť,Kultúra,Vzdelanie',	'$2y$10$pPz2cRNxZVYUOMFLl3hUdeFD.AV0LQLbyB0B4erFnZoyTQ4NAs1Pu'),
(7,	9,	1,	'',	'$2y$10$Z5UfT/K5tT5oMCAeLMgF1e1AeJXUixvTHJyTB6MndoauElzvFiwI2'),
(8,	10,	1,	'Právo',	'$2y$10$3HmPYbLt2pmXgXKJYzJACO2XxmVXPzjQBKRIV/0IoQTniaucHIZfu');

DROP TABLE IF EXISTS `poslanecky_klub`;
CREATE TABLE `poslanecky_klub` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nazov` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `poslanecky_klub` (`id`, `nazov`) VALUES
(1,	'Nezaradený');

-- 2022-05-10 04:45:15
