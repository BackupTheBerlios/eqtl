-- MySQL dump 10.13  Distrib 5.1.48, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: qtl
-- ------------------------------------------------------
-- Server version	5.1.48-1

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
-- Table structure for table `cia_qtl`
--

DROP TABLE IF EXISTS `cia_qtl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cia_qtl` (
  `entry` int(3) unsigned NOT NULL DEFAULT '0',
  `Locus_type` char(10) NOT NULL DEFAULT '',
  `name` char(10) NOT NULL DEFAULT '',
  `chr` char(3) NOT NULL DEFAULT '',
  `start_mrk` char(20) DEFAULT NULL,
  `stop_mrk` char(20) DEFAULT NULL,
  `start_cm` char(10) DEFAULT NULL,
  `stop_cm` char(10) DEFAULT NULL,
  `start_cr` char(10) DEFAULT NULL,
  `stop_cr` char(10) DEFAULT NULL,
  `start_bps` bigint(20) DEFAULT NULL,
  `infer_start` tinyint(4) DEFAULT NULL,
  `stop_bps` bigint(20) unsigned DEFAULT NULL,
  `infer_stop` tinyint(4) DEFAULT NULL,
  `species` char(20) NOT NULL DEFAULT '',
  `trait` char(150) DEFAULT NULL,
  `source` char(12) DEFAULT NULL,
  `start_centroid` bigint(20) unsigned DEFAULT NULL,
  `stop_centroid` bigint(20) unsigned DEFAULT NULL,
  `start_ens` bigint(20) unsigned DEFAULT NULL,
  `stop_ens` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`entry`),
  KEY `ind_cia_chr_start_end_bps` (`chr`,`start_bps`,`stop_bps`),
  KEY `ind_cia_trait` (`trait`),
  KEY `ind_cia_species` (`species`),
  KEY `ind_cia_locus` (`Locus_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cia_qtl_backup`
--

DROP TABLE IF EXISTS `cia_qtl_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cia_qtl_backup` (
  `entry` int(3) unsigned NOT NULL DEFAULT '0',
  `Locus_type` char(10) NOT NULL DEFAULT '',
  `name` char(10) NOT NULL DEFAULT '',
  `chr` char(3) NOT NULL DEFAULT '',
  `start_mrk` char(20) DEFAULT NULL,
  `stop_mrk` char(20) DEFAULT NULL,
  `start_cm` char(10) DEFAULT NULL,
  `stop_cm` char(10) DEFAULT NULL,
  `start_cr` char(10) DEFAULT NULL,
  `stop_cr` char(10) DEFAULT NULL,
  `start_bps` bigint(20) DEFAULT NULL,
  `infer_start` tinyint(4) DEFAULT NULL,
  `stop_bps` bigint(20) unsigned DEFAULT NULL,
  `infer_stop` tinyint(4) DEFAULT NULL,
  `species` char(20) NOT NULL DEFAULT '',
  `trait` char(150) DEFAULT NULL,
  `source` char(12) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eae_qtl`
--

DROP TABLE IF EXISTS `eae_qtl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eae_qtl` (
  `entry` int(3) unsigned NOT NULL DEFAULT '0',
  `Locus_type` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(10) NOT NULL DEFAULT '',
  `chr` char(3) NOT NULL DEFAULT '',
  `start_mrk` varchar(20) DEFAULT NULL,
  `stop_mrk` varchar(20) DEFAULT NULL,
  `start_cm` varchar(10) DEFAULT NULL,
  `stop_cm` varchar(10) DEFAULT NULL,
  `start_cr` varchar(10) DEFAULT NULL,
  `stop_cr` varchar(10) DEFAULT NULL,
  `start_bps` bigint(20) DEFAULT NULL,
  `infer_start` tinyint(4) DEFAULT NULL,
  `stop_bps` bigint(20) unsigned DEFAULT NULL,
  `infer_stop` tinyint(4) DEFAULT NULL,
  `species` varchar(20) NOT NULL DEFAULT '',
  `trait` varchar(150) DEFAULT NULL,
  `source` varchar(150) DEFAULT NULL,
  `start_centroid` bigint(20) unsigned DEFAULT NULL,
  `stop_centroid` bigint(20) unsigned DEFAULT NULL,
  `start_ens` bigint(20) unsigned DEFAULT NULL,
  `stop_ens` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`entry`),
  KEY `ind_eae_chr_start_end_bps` (`chr`,`start_bps`,`stop_bps`),
  KEY `ind_eae_trait` (`trait`),
  KEY `ind_eae_species` (`species`),
  KEY `ind_eae_locus` (`Locus_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eae_qtl_games`
--

DROP TABLE IF EXISTS `eae_qtl_games`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eae_qtl_games` (
  `entry` int(3) unsigned NOT NULL DEFAULT '0',
  `Locus_type` char(10) NOT NULL DEFAULT '',
  `name` char(10) NOT NULL DEFAULT '',
  `chr` char(3) NOT NULL DEFAULT '',
  `start_mrk` char(20) DEFAULT NULL,
  `stop_mrk` char(20) DEFAULT NULL,
  `start_cm` char(10) DEFAULT NULL,
  `stop_cm` char(10) DEFAULT NULL,
  `start_cr` char(10) DEFAULT NULL,
  `stop_cr` char(10) DEFAULT NULL,
  `start_bps` bigint(20) DEFAULT NULL,
  `infer_start` tinyint(4) DEFAULT NULL,
  `stop_bps` bigint(20) unsigned DEFAULT NULL,
  `infer_stop` tinyint(4) DEFAULT NULL,
  `species` char(20) NOT NULL DEFAULT '',
  `trait` char(150) DEFAULT NULL,
  `source` char(12) DEFAULT NULL,
  `start_centroid` bigint(20) unsigned DEFAULT NULL,
  `stop_centroid` bigint(20) unsigned DEFAULT NULL,
  `start_ens` bigint(20) unsigned DEFAULT NULL,
  `stop_ens` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`entry`),
  KEY `ind_eae_chr_start_end_bps` (`chr`,`start_bps`,`stop_bps`),
  KEY `ind_eae_trait` (`trait`),
  KEY `ind_eae_species` (`species`),
  KEY `ind_eae_locus` (`Locus_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gam_qtl`
--

DROP TABLE IF EXISTS `gam_qtl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gam_qtl` (
  `entry` int(3) unsigned NOT NULL DEFAULT '0',
  `Locus_type` char(10) NOT NULL DEFAULT '',
  `name` char(10) NOT NULL DEFAULT '',
  `chr` char(3) NOT NULL DEFAULT '',
  `start_mrk` char(20) DEFAULT NULL,
  `stop_mrk` char(20) DEFAULT NULL,
  `start_cm` char(10) DEFAULT NULL,
  `stop_cm` char(10) DEFAULT NULL,
  `start_cr` char(10) DEFAULT NULL,
  `stop_cr` char(10) DEFAULT NULL,
  `start_bps` bigint(20) DEFAULT NULL,
  `infer_start` tinyint(4) DEFAULT NULL,
  `stop_bps` bigint(20) unsigned DEFAULT NULL,
  `infer_stop` tinyint(4) DEFAULT NULL,
  `species` char(20) NOT NULL DEFAULT '',
  `trait` char(150) DEFAULT NULL,
  `source` char(12) DEFAULT NULL,
  `start_centroid` bigint(20) unsigned DEFAULT NULL,
  `stop_centroid` bigint(20) unsigned DEFAULT NULL,
  `start_ens` bigint(20) unsigned DEFAULT NULL,
  `stop_ens` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`entry`),
  KEY `ind_eae_chr_start_end_bps` (`chr`,`start_bps`,`stop_bps`),
  KEY `ind_eae_trait` (`trait`),
  KEY `ind_eae_species` (`species`),
  KEY `ind_eae_locus` (`Locus_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ms_qtl`
--

DROP TABLE IF EXISTS `ms_qtl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ms_qtl` (
  `entry` int(3) unsigned NOT NULL DEFAULT '0',
  `Locus_type` char(10) NOT NULL DEFAULT '',
  `name` char(10) NOT NULL DEFAULT '',
  `chr` char(3) NOT NULL DEFAULT '',
  `start_mrk` char(20) DEFAULT NULL,
  `stop_mrk` char(20) DEFAULT NULL,
  `start_cm` char(10) DEFAULT NULL,
  `stop_cm` char(10) DEFAULT NULL,
  `start_cr` char(10) DEFAULT NULL,
  `stop_cr` char(10) DEFAULT NULL,
  `start_bps` bigint(20) DEFAULT NULL,
  `infer_start` tinyint(4) DEFAULT NULL,
  `stop_bps` bigint(20) unsigned DEFAULT NULL,
  `infer_stop` tinyint(4) DEFAULT NULL,
  `species` char(20) NOT NULL DEFAULT '',
  `trait` char(150) DEFAULT NULL,
  `source` char(12) DEFAULT NULL,
  `start_centroid` bigint(20) unsigned DEFAULT NULL,
  `stop_centroid` bigint(20) unsigned DEFAULT NULL,
  `start_ens` bigint(20) unsigned DEFAULT NULL,
  `stop_ens` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`entry`),
  KEY `ind_eae_chr_start_end_bps` (`chr`,`start_bps`,`stop_bps`),
  KEY `ind_eae_trait` (`trait`),
  KEY `ind_eae_species` (`species`),
  KEY `ind_eae_locus` (`Locus_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-07-27 13:30:08
