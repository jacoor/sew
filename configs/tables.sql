-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas wygenerowania: 06 Gru 2008, 12:30
-- Wersja serwera: 5.0.67
-- Wersja PHP: 5.2.6-2ubuntu4

SET FOREIGN_KEY_CHECKS=0;

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

SET AUTOCOMMIT=0;
START TRANSACTION;

--
-- Baza danych: `SEW`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `meetings`
--

CREATE TABLE IF NOT EXISTS `meetings` (
  `id` int(11) NOT NULL auto_increment,
  `date` date NOT NULL default '2000-01-01',
  `time` time NOT NULL default '00:00:00',
  `persons_limit` tinyint(4) NOT NULL default '0' COMMENT 'limit ludków na dany termin',
  `r_amount` tinyint(4) NOT NULL default '0' COMMENT 'ilu juz sie zglosilo\n',
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='terminy rekrutacji' AUTO_INCREMENT=45 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `notices`
--

CREATE TABLE IF NOT EXISTS `notices` (
  `id` int(11) NOT NULL auto_increment,
  `vid` int(11) NOT NULL COMMENT 'id wolontariusza\n',
  `data` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `type_of` enum('rozliczenie','spotkanie','policja','nagroda','inne','numer identyfikatora') NOT NULL COMMENT 'typ uwag',
  `mid` int(11) default NULL COMMENT 'id terminu spotkania (nie klucz obcy)\n ',
  `m_date` datetime default NULL COMMENT 'data spotkania - pamiętać zeby zrobic klucz obcy',
  `amount` int(11) NOT NULL default '0' COMMENT 'kwota rozliczenia\n',
  `valuables` text COMMENT 'dary - biżuteria itp',
  `text_value` text COMMENT 'opis zdarzenia',
  `author` varchar(100) NOT NULL COMMENT 'kto zgłosił - nie id, pełen tekst (imię, nazwisko, login)\n',
  `m_presence` binary(1) NOT NULL default '0' COMMENT 'czy był wogole na spotkaniu?\n',
  `ident_nr` int(11) default NULL COMMENT 'numer identyfikatora',
  `final_nr` int(11) NOT NULL default '0' COMMENT 'numer finalu - do wykorzystania np przy kolorowaniu ludzi',
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `vid` (`vid`),
  KEY `mid` (`mid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='zdarzenia dotyczące wolontariuszy' AUTO_INCREMENT=72 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `volunteers`
--

CREATE TABLE IF NOT EXISTS `volunteers` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `surname` varchar(45) NOT NULL,
  `login` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  `email` varchar(45) default NULL,
  `h_street` varchar(100) NOT NULL COMMENT 'Ulica zamieszkania',
  `h_city` varchar(50) NOT NULL COMMENT 'MIasto zamieszkania z kodem',
  `school_address` text NOT NULL COMMENT '''adres szkoły lub miejsca pracy''',
  `birth_date` date NOT NULL COMMENT '''typ zmiennej date''',
  `PESEL` bigint(11) NOT NULL,
  `phone` varchar(45) default NULL COMMENT '''numer kontaktowy''',
  `p_phone` varchar(45) default NULL COMMENT '''numer do rodzica''',
  `r_date` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT '''data rejestracji''',
  `rank` tinyint(4) NOT NULL default '0' COMMENT '''ocena''',
  `active` binary(1) NOT NULL default '0' COMMENT 'do potwierdzania adresu email - po potwierdzeniu 1 co uaktywnia konto',
  `doc_id` varchar(45) NOT NULL default '0' COMMENT 'numer dokumentu tożsamości',
  `doc_type` enum('legitymacja szkolna','legitymacja studencka','dowód osobisty','paszport','karta stałego pobytu','prawo jazdy','książeczka wojskowa','inne') NOT NULL,
  `ACL` varchar(200) NOT NULL default 'a:7:{s:4:"self";i:1;s:4:"view";i:0;s:7:"notices";i:0;s:4:"edit";i:0;s:6:"a_edit";i:0;s:5:"admin";i:0;s:10:"superadmin";i:0;}' COMMENT 'zserializowana tablica zawierająca listę operacji jakie dany użyszkodnik może wykonać',
  `type` enum('ppatrol','sztab','zaufany','czarna lista','nie dotyczy','zakwalifikowany na finał') default 'nie dotyczy',
  `token` varchar(45) NOT NULL default '0' COMMENT 'token do potwierdzenia maila',
  `deleted` binary(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `PESEL` (`PESEL`),
  UNIQUE KEY `email` (`email`),
  KEY `name` (`surname`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=94 ;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `notices`
--
ALTER TABLE `notices`
  ADD CONSTRAINT `notices_ibfk_1` FOREIGN KEY (`vid`) REFERENCES `volunteers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS=1;

COMMIT;
