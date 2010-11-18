-- MySQL dump 10.13  Distrib 5.1.49, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: eQTL_Braunschweig_Treg
-- ------------------------------------------------------
-- Server version	5.1.49-1ubuntu8

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
-- Table structure for table `Affymetrix_Mouse_430_2_Chip_Details`
--

DROP TABLE IF EXISTS `Affymetrix_Mouse_430_2_Chip_Details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Affymetrix_Mouse_430_2_Chip_Details` (
  `Probe_Set_ID` varchar(200) NOT NULL DEFAULT '',
  `GeneChip_Array` varchar(200) DEFAULT NULL,
  `Species_Scientific_Name` varchar(200) DEFAULT NULL,
  `Annotation_Date` varchar(200) DEFAULT NULL,
  `Sequence_Type` varchar(200) DEFAULT NULL,
  `Sequence_Source` varchar(200) DEFAULT NULL,
  `Transcript_ID_Array_Design_` varchar(200) DEFAULT NULL,
  `Target_Description` varchar(200) DEFAULT NULL,
  `Representative_Public_ID` varchar(200) DEFAULT NULL,
  `Archival_UniGene_Cluster` varchar(200) DEFAULT NULL,
  `UniGene_ID` varchar(200) DEFAULT NULL,
  `Genome_Version` varchar(200) DEFAULT NULL,
  `Alignments` varchar(200) DEFAULT NULL,
  `Gene_Title` varchar(200) DEFAULT NULL,
  `Gene_Symbol` varchar(200) DEFAULT NULL,
  `Chromosomal_Location` varchar(200) DEFAULT NULL,
  `Unigene_Cluster_Type` varchar(200) DEFAULT NULL,
  `Ensembl` varchar(200) DEFAULT NULL,
  `Entrez_Gene` varchar(200) DEFAULT NULL,
  `SwissProt` varchar(200) DEFAULT NULL,
  `EC` varchar(200) DEFAULT NULL,
  `OMIM` varchar(200) DEFAULT NULL,
  `RefSeq_Protein_ID` varchar(200) DEFAULT NULL,
  `RefSeq_Transcript_ID` varchar(200) DEFAULT NULL,
  `FlyBase` varchar(200) DEFAULT NULL,
  `AGI` varchar(200) DEFAULT NULL,
  `WormBase` varchar(200) DEFAULT NULL,
  `MGI_Name` varchar(200) DEFAULT NULL,
  `RGD_Name` varchar(200) DEFAULT NULL,
  `SGD_accession_number` varchar(200) DEFAULT NULL,
  `Gene_Ontology_Biological_Process` varchar(200) DEFAULT NULL,
  `Gene_Ontology_Cellular_Component` varchar(200) DEFAULT NULL,
  `Gene_Ontology_Molecular_Function` varchar(200) DEFAULT NULL,
  `Pathway` varchar(200) DEFAULT NULL,
  `InterPro` varchar(200) DEFAULT NULL,
  `Trans_Membrane` varchar(200) DEFAULT NULL,
  `QTL` varchar(200) DEFAULT NULL,
  `Annotation_Description` varchar(200) DEFAULT NULL,
  `Annotation_Transcript_Cluster` varchar(200) DEFAULT NULL,
  `Transcript_Assignments` varchar(200) DEFAULT NULL,
  `Annotation_Notes` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`Probe_Set_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `characteristics`
--

DROP TABLE IF EXISTS `characteristics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `characteristics` (
  `term_id` varchar(70) NOT NULL,
  `description` varchar(300) DEFAULT NULL,
  `url` varchar(130) DEFAULT NULL,
  PRIMARY KEY (`term_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `computation`
--

DROP TABLE IF EXISTS `computation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `computation` (
  `computation_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'All data stored in the expression QTL database will receive a reference to a computation ID to indicate its provenance.',
  `status` enum('UNKNOWN','QUEUED','PROCESSING','DONE','RECALCULATE','REPROCESSING') NOT NULL DEFAULT 'UNKNOWN' COMMENT 'The state an entry in this table is in.',
  `version` int(11) NOT NULL DEFAULT '0',
  `application` enum('UNKNOWN','SCANONE','SCANTWO') NOT NULL DEFAULT 'UNKNOWN' COMMENT 'This field allows',
  `timestamp` datetime DEFAULT NULL COMMENT 'The moment that is assigned to the last state change, which may differ from the moment that the entry is changed in the database.',
  `trait_id` int(11) DEFAULT NULL COMMENT 'Direct assignment to a particular trait of interest.',
  `jobname` varchar(255) NOT NULL COMMENT 'Information that is difficult to formally represent that should have a similarity with the name of the file that keeps the raw data on the disk.',
  `filename` varchar(255) NOT NULL COMMENT 'Pointer to data stored locally.',
  PRIMARY KEY (`computation_id`),
  UNIQUE KEY `computation_jobname` (`jobname`),
  UNIQUE KEY `computation_filename` (`filename`),
  KEY `computation_application` (`application`),
  KEY `computation_status` (`status`),
  KEY `computation_timestamp` (`timestamp`),
  KEY `computation_stat_appl` (`status`,`application`),
  KEY `idx_computation_application_status` (`application`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=5946532 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`stockholm_eae`@`master.dermacloud.uni-luebeck.de`*/ /*!50003 TRIGGER removeQtlWithComputation
AFTER DELETE ON computation 
FOR EACH ROW DELETE FROM qtl where computation_id=OLD.computation_id */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `group_characteristics`
--

DROP TABLE IF EXISTS `group_characteristics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_characteristics` (
  `group_characteristics_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) DEFAULT NULL,
  `term_id` varchar(70) DEFAULT NULL,
  `pvalue` float DEFAULT NULL,
  PRIMARY KEY (`group_characteristics_id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4156 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `group_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `covariates` set('eae_add','eae_int','sum_add','sum_int','max_add','max_int','weight_add','weight_int','sud_add','sud_int','cage_add','cage_int','dose_add','dose_int','dpw_add','dpw_int','ons_add','ons_int','sd_add','sd_int','s35_add','s35_int','D12G1_add','D12G1_int','D12G2B_add','D12G2B_int','D12G2C_add','D12G2C_int','D35G1_add','D35G1_int','D35G2B_add','D35G2B_int','D35G2C_add','D35G2C_int','cross_add','cross_int','dud_add','dud_int','t12p_add','t12p_int','t35p_add','t35p_int','bd_add','bd_int','wl0_add','wl0_int','totalIgG_add','totalIgG_int','set_add','set_int') DEFAULT NULL,
  `trait_list` varchar(500) DEFAULT NULL,
  `query` varchar(700) DEFAULT NULL,
  `significant` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locus`
--

DROP TABLE IF EXISTS `locus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locus` (
  `No` int(11) NOT NULL AUTO_INCREMENT,
  `Name` char(15) DEFAULT NULL,
  `Chr` char(2) NOT NULL,
  `cMorgan` float(255,8) DEFAULT NULL,
  `Mbp` float DEFAULT NULL,
  `Organism` char(20) NOT NULL,
  `marker` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`No`),
  UNIQUE KEY `indLocusName` (`Name`),
  UNIQUE KEY `indLocusChrMorgan` (`Chr`,`cMorgan`)
) ENGINE=MyISAM AUTO_INCREMENT=5000 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locusInteraction`
--

DROP TABLE IF EXISTS `locusInteraction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locusInteraction` (
  `No` int(11) NOT NULL AUTO_INCREMENT,
  `computation_id` int(11) DEFAULT NULL,
  `Trait` char(100) NOT NULL,
  `A` char(10) NOT NULL,
  `B` char(10) NOT NULL,
  `LogP` char(100) DEFAULT NULL,
  `covariates` set('eae_add','eae_int','sum_add','sum_int','max_add','max_int','weight_add','weight_int','sud_add','sud_int','cage_add','cage_int','dose_add','dose_int','dpw_add','dpw_int','ons_add','ons_int','sd_add','sd_int','s35_add','s35_int','D12G1_add','D12G1_int','D12G2B_add','D12G2B_int','D12G2C_add','D12G2C_int','D35G1_add','D35G1_int','D35G2B_add','D35G2B_int','D35G2C_add','D35G2C_int','cross_add','cross_int','dud_add','dud_int','t12p_add','t12p_int','t35p_add','t35p_int','bd_add','bd_int','wl0_add','wl0_int','totalIgG_add','totalIgG_int','set_add','set_int') DEFAULT NULL,
  `lod_full` float(255,8) DEFAULT NULL,
  `lod_fv1` float(255,8) DEFAULT NULL,
  `lod_int` float(255,8) DEFAULT NULL,
  `lod_add` float(255,8) DEFAULT NULL,
  `lod_av1` float(255,8) DEFAULT NULL,
  `qlod_full` float(255,8) DEFAULT NULL,
  `qlod_fv1` float(255,8) DEFAULT NULL,
  `qlod_int` float(255,8) DEFAULT NULL,
  `qlod_add` float(255,8) DEFAULT NULL,
  `qlod_av1` float(255,8) DEFAULT NULL,
  `cis` enum('00','01','10','11') DEFAULT NULL,
  `locComb` int(11) DEFAULT NULL,
  `cis_dist_A` int(11) DEFAULT NULL,
  `cis_dist_B` int(11) DEFAULT NULL,
  `pvalue_full` float DEFAULT NULL,
  `pvalue_full_conf_min` float DEFAULT NULL,
  `pvalue_full_conf_max` float DEFAULT NULL,
  `pvalue_fv1` float DEFAULT NULL,
  `pvalue_int` float DEFAULT NULL,
  `pvalue_add` float DEFAULT NULL,
  `pvalue_av1` float DEFAULT NULL,
  PRIMARY KEY (`No`),
  UNIQUE KEY `Trait_2` (`Trait`,`A`,`B`),
  UNIQUE KEY `Trait_3` (`Trait`,`A`,`B`,`covariates`),
  KEY `indLocIntAB` (`A`,`B`),
  KEY `indLocIntBA` (`B`,`A`),
  KEY `Trait` (`Trait`)
) ENGINE=MyISAM AUTO_INCREMENT=896036 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `map`
--

DROP TABLE IF EXISTS `map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `map` (
  `marker` char(25) DEFAULT NULL,
  `bp` float DEFAULT NULL,
  `Mbp` float DEFAULT NULL,
  `cmorgan_rqtl` float DEFAULT NULL,
  `cmorgan_ensembl` float DEFAULT NULL,
  `chr` char(2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qtl`
--

DROP TABLE IF EXISTS `qtl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qtl` (
  `No` int(11) NOT NULL AUTO_INCREMENT,
  `computation_id` int(11) DEFAULT NULL,
  `Name` char(255) DEFAULT NULL,
  `Locus` char(15) NOT NULL,
  `Trait` char(100) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LOD` float(255,8) DEFAULT NULL,
  `Chromosome` varchar(2) DEFAULT NULL,
  `Locus_min` char(15) DEFAULT NULL,
  `Locus_max` char(15) DEFAULT NULL,
  `cMorgan_Min` float DEFAULT NULL,
  `cMorgan_Max` float DEFAULT NULL,
  `cMorgan_Peak` float DEFAULT NULL,
  `Mbp_Peak` float DEFAULT NULL,
  `Quantile` float DEFAULT NULL,
  `covariates` set('sex_add','sex_int') DEFAULT NULL,
  `phenocol` char(10) DEFAULT NULL,
  `cis` tinyint(1) DEFAULT NULL,
  `cis_dist` int(11) DEFAULT NULL,
  `pvalue` float DEFAULT NULL COMMENT 'quantile in permutations that the LOD score is found at - 0 is best',
  `pvalue_conf_min` float DEFAULT NULL COMMENT 'lower bound of confidence interval for p-value, 0 is best and expected for most eQTL',
  `pvalue_conf_max` float DEFAULT NULL COMMENT 'upper bound of confidence interval for p-value, 0 is best but unreachable',
  PRIMARY KEY (`No`),
  UNIQUE KEY `Trait` (`Trait`,`Locus`,`covariates`),
  KEY `indQtlLocus` (`Locus`),
  KEY `indQtlLOD` (`LOD`),
  KEY `indQtlTrait` (`Trait`,`LOD`),
  KEY `indQtlName` (`Name`),
  KEY `qtl_location_peak` (`Chromosome`,`cMorgan_Peak`),
  KEY `qtl_location_min` (`Chromosome`,`cMorgan_Min`),
  KEY `qtl_location_max` (`Chromosome`,`cMorgan_Max`),
  KEY `qtl_computation` (`computation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1602416 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qtl_groups`
--

DROP TABLE IF EXISTS `qtl_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qtl_groups` (
  `qtl_group_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) DEFAULT NULL,
  `locus_id` int(11) NOT NULL,
  PRIMARY KEY (`qtl_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trait`
--

DROP TABLE IF EXISTS `trait`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trait` (
  `trait_id` varchar(200) NOT NULL DEFAULT '',
  `name` varchar(50) DEFAULT NULL,
  `chromosome` varchar(2) DEFAULT NULL,
  `start` int(11) DEFAULT NULL,
  `stop` int(11) DEFAULT NULL,
  `strand` tinyint(2) DEFAULT NULL,
  `band` varchar(10) DEFAULT NULL,
  `ensembl_stable_gene_id` varchar(25) DEFAULT NULL,
  `gene_name` varchar(250) DEFAULT NULL,
  `mean` float DEFAULT NULL,
  `sd` float DEFAULT NULL,
  `vals` text,
  `individuals` text,
  `median` float DEFAULT NULL,
  `variance` float DEFAULT NULL,
  `traits_pos_cor` text,
  `traits_pos_cor_rho` text,
  `traits_pos_cor_most` varchar(20) DEFAULT NULL,
  `traits_pos_cor_most_rho` float DEFAULT NULL,
  `traits_neg_cor` text,
  `traits_neg_cor_rho` text,
  `traits_neg_cor_most` varchar(20) DEFAULT NULL,
  `traits_neg_cor_most_rho` float DEFAULT NULL,
  PRIMARY KEY (`trait_id`),
  KEY `trait_id` (`trait_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trait_phen_analysis`
--

DROP TABLE IF EXISTS `trait_phen_analysis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trait_phen_analysis` (
  `trait_phen_analysis_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `analysis` varchar(20) DEFAULT NULL COMMENT 'Verbal description of analysis, short identifier.',
  `description` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`trait_phen_analysis_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COMMENT='Overview on analyses being performed.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trait_phen_analysis_value`
--

DROP TABLE IF EXISTS `trait_phen_analysis_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trait_phen_analysis_value` (
  `trait_phen_analysis_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Reference to table trait_phen_analysis.',
  `trait_id` varchar(20) NOT NULL DEFAULT '' COMMENT 'Reference to table trait, the expression value analysed.',
  `value` double DEFAULT NULL COMMENT 'The result of the analysis.',
  PRIMARY KEY (`trait_id`,`trait_phen_analysis_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Linking the analyses with traits and the results.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trait_phen_cor`
--

DROP TABLE IF EXISTS `trait_phen_cor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trait_phen_cor` (
  `trait_id` varchar(20) DEFAULT NULL,
  `phen` varchar(20) DEFAULT NULL,
  `rho` float DEFAULT NULL,
  `p` float DEFAULT NULL,
  KEY `cor_phen_trait` (`phen`,`trait_id`),
  KEY `cor_trait_phen` (`trait_id`,`phen`)
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

-- Dump completed on 2010-09-22 15:13:02


-- Manual additions

DROP TABLE IF EXISTS `individuals_group`;
CREATE TABLE individuals_group (
  individuals_group_id int AUTO_INCREMENT,
  name varchar(20),
  phen varchar(40),
  relation enum('=','<','>','<=','>='),
  value float,
  PRIMARY KEY (individuals_group_id)
);

