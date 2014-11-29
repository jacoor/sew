# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.6.21)
# Database: sew
# Generation Time: 2014-11-28 18:13:36 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table meetings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `meetings`;

CREATE TABLE `meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL DEFAULT '2000-01-01',
  `time` time NOT NULL DEFAULT '00:00:00',
  `persons_limit` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'limit ludków na dany termin',
  `r_amount` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'ilu juz sie zglosilo\n',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `place` varchar(255) DEFAULT NULL COMMENT 'Miejsce',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='terminy rekrutacji';



# Dump of table notices
# ------------------------------------------------------------

DROP TABLE IF EXISTS `notices`;

CREATE TABLE `notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vid` int(11) NOT NULL COMMENT 'id wolontariusza\n',
  `data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type_of` enum('rozliczenie','spotkanie','policja','nagroda','inne','numer identyfikatora') NOT NULL COMMENT 'typ uwag',
  `mid` int(11) DEFAULT NULL COMMENT 'id terminu spotkania (nie klucz obcy)\n ',
  `m_date` datetime DEFAULT NULL COMMENT 'data spotkania - pamiętać zeby zrobic klucz obcy',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT 'kwota rozliczenia\n',
  `valuables` text COMMENT 'dary - biżuteria itp',
  `text_value` text COMMENT 'opis zdarzenia',
  `author` varchar(100) NOT NULL COMMENT 'kto zgłosił - nie id, pełen tekst (imię, nazwisko, login)\n',
  `m_presence` binary(1) NOT NULL DEFAULT '0' COMMENT 'czy był wogole na spotkaniu?\n',
  `ident_nr` int(11) DEFAULT NULL COMMENT 'numer identyfikatora',
  `final_nr` int(11) NOT NULL DEFAULT '0' COMMENT 'numer finalu - do wykorzystania np przy kolorowaniu ludzi',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `vid` (`vid`),
  KEY `mid` (`mid`),
  CONSTRAINT `notices_ibfk_1` FOREIGN KEY (`vid`) REFERENCES `volunteers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='zdarzenia dotyczące wolontariuszy';



# Dump of table volunteers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `volunteers`;

CREATE TABLE `volunteers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `surname` varchar(45) NOT NULL,
  `login` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  `email` varchar(45) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `h_street` varchar(100) NOT NULL COMMENT 'Ulica zamieszkania',
  `h_building` varchar(20) NOT NULL COMMENT 'numer budynku',
  `h_loc` varchar(20) DEFAULT NULL COMMENT 'numer mieszkania',
  `h_zip` varchar(6) NOT NULL COMMENT 'kod pocztowy',
  `h_city` varchar(50) NOT NULL COMMENT 'MIasto zamieszkania z kodem',
  `school_name` varchar(255) NOT NULL COMMENT 'nazwa szkoły',
  `school_street` varchar(255) NOT NULL COMMENT 'Ulica',
  `school_building` varchar(20) NOT NULL COMMENT 'Numer budynku',
  `school_loc` varchar(20) DEFAULT NULL COMMENT 'Numer lokalu',
  `school_zip` varchar(6) NOT NULL COMMENT 'kod pocztowy xx-xxx',
  `school_city` varchar(255) NOT NULL COMMENT 'Miasto',
  `birth_date` date NOT NULL COMMENT '''typ zmiennej date''',
  `PESEL` varchar(11) NOT NULL,
  `phone` varchar(45) DEFAULT NULL COMMENT '''numer kontaktowy''',
  `p_phone` varchar(45) DEFAULT NULL COMMENT '''numer do rodzica''',
  `r_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '''data rejestracji''',
  `rank` tinyint(4) NOT NULL DEFAULT '0' COMMENT '''ocena''',
  `statement_file` varchar(255) DEFAULT NULL,
  `statement_downloaded` binary(1) NOT NULL DEFAULT '0',
  `statement_downloaded_timestamp` timestamp NULL DEFAULT NULL,
  `active` binary(1) NOT NULL DEFAULT '0' COMMENT 'do potwierdzania adresu email - po potwierdzeniu 1 co uaktywnia konto',
  `doc_id` varchar(45) NOT NULL DEFAULT '0' COMMENT 'numer dokumentu tożsamości',
  `doc_type` enum('legitymacja szkolna','legitymacja studencka','dowód osobisty','paszport','karta stałego pobytu','prawo jazdy','książeczka wojskowa','inne') NOT NULL,
  `consent_processing_of_personal_data` varchar(2) NOT NULL DEFAULT 'on' COMMENT '''zgoda na przetwarzanie danych, wymagana do rejestracji w systemie.''',
  `date_consent_processing_of_personal_data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '''zgoda na przetwarzanie danych - data wyrażenia zgody.''',
  `accept_of_sending_data_to_WOSP` varchar(2) NOT NULL DEFAULT 'on' COMMENT '''zgoda na przekazanie danych do Fundacji WOSP, wymagana do rejestracji w systemie.''',
  `date_accept_of_sending_data_to_WOSP` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '''data wyrażenia zgody na przekazanie danych do fundacji WOSP zgody''',
  `ACL` varchar(200) NOT NULL DEFAULT 'a:7:{s:4:"self";i:1;s:4:"view";i:0;s:7:"notices";i:0;s:4:"edit";i:0;s:6:"a_edit";i:0;s:5:"admin";i:0;s:10:"superadmin";i:0;}' COMMENT 'zserializowana tablica zawierająca listę operacji jakie dany użyszkodnik może wykonać',
  `type` enum('ppatrol','sztab','zaufany','czarna lista','nie dotyczy','zakwalifikowany na finał','dane w systemie fundacyjnym (zakwalifikowany na finał)') DEFAULT 'nie dotyczy',
  `token` varchar(45) NOT NULL DEFAULT '0' COMMENT 'token do potwierdzenia maila',
  `deleted` binary(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `PESEL` (`PESEL`),
  UNIQUE KEY `email` (`email`),
  KEY `surname` (`surname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
