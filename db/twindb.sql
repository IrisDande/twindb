
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
DROP TABLE IF EXISTS `adj_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adj_list` (
  `adj_list_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cluster_id` int(10) unsigned NOT NULL,
  `vertex` char(36) NOT NULL,
  `neighbor` char(36) NOT NULL,
  PRIMARY KEY (`adj_list_id`),
  KEY `fk_adj_list_cgnode_idx` (`cluster_id`),
  KEY `fk_adj_list_server_idx` (`vertex`),
  KEY `fk_adj_list_server_2_idx` (`neighbor`),
  CONSTRAINT `fk_adj_list_cgnode` FOREIGN KEY (`cluster_id`) REFERENCES `cgnode` (`cluster_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_adj_list_server_1` FOREIGN KEY (`vertex`) REFERENCES `server` (`server_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_adj_list_server_2` FOREIGN KEY (`neighbor`) REFERENCES `server` (`server_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `adj_list` WRITE;
/*!40000 ALTER TABLE `adj_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `adj_list` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `backup_copy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_copy` (
  `backup_copy_id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `size` bigint(19) unsigned NOT NULL DEFAULT '0',
  `backup_type` enum('Hourly','Daily','Weekly','Monthly','Quarterly','Yearly') DEFAULT NULL,
  `volume_id` int(10) unsigned NOT NULL,
  `lsn` varchar(255) DEFAULT NULL,
  `full_backup` enum('Y','N') DEFAULT 'Y',
  PRIMARY KEY (`backup_copy_id`),
  KEY `fk_backup_copy_job1_idx` (`job_id`),
  KEY `fk_backup_copy_volume` (`volume_id`),
  CONSTRAINT `fk_backup_copy_job_id` FOREIGN KEY (`job_id`) REFERENCES `job` (`job_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_backup_copy_volume` FOREIGN KEY (`volume_id`) REFERENCES `volume` (`volume_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `backup_copy` WRITE;
/*!40000 ALTER TABLE `backup_copy` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_copy` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `backup_copy_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_copy_tree` (
  `ancestor` bigint(20) unsigned NOT NULL,
  `descendant` bigint(20) unsigned NOT NULL,
  `length` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ancestor`,`descendant`),
  KEY `fk_backup_copy_tree_backup_copy_id_1_idx` (`ancestor`),
  KEY `fk_backup_copy_tree_backup_copy_id_2_idx` (`descendant`),
  CONSTRAINT `fk_backup_copy_tree_backup_copy_id_1` FOREIGN KEY (`ancestor`) REFERENCES `backup_copy` (`backup_copy_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_backup_copy_tree_backup_copy_id_2` FOREIGN KEY (`descendant`) REFERENCES `backup_copy` (`backup_copy_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `backup_copy_tree` WRITE;
/*!40000 ALTER TABLE `backup_copy_tree` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_copy_tree` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cg_treepath`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cg_treepath` (
  `ancestor` int(10) unsigned NOT NULL,
  `descendant` int(10) unsigned NOT NULL,
  `length` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `fk_treepath_server_id_1_idx` (`ancestor`),
  KEY `fk_treepath_server_id_2_idx` (`descendant`),
  CONSTRAINT `fk_treepath_server_id_1` FOREIGN KEY (`ancestor`) REFERENCES `cgnode` (`cluster_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_treepath_server_id_2` FOREIGN KEY (`descendant`) REFERENCES `cgnode` (`cluster_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cg_treepath` WRITE;
/*!40000 ALTER TABLE `cg_treepath` DISABLE KEYS */;
INSERT INTO `cg_treepath` VALUES (13,13,0),(14,14,0);
/*!40000 ALTER TABLE `cg_treepath` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cgnode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cgnode` (
  `cluster_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT 'Default cluster group',
  `user_id` int(10) unsigned NOT NULL,
  `config_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cluster_id`),
  KEY `fk_user_idx` (`user_id`),
  KEY `fk_config_idx` (`config_id`),
  CONSTRAINT `fk_config` FOREIGN KEY (`config_id`) REFERENCES `config` (`config_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cgnode` WRITE;
/*!40000 ALTER TABLE `cgnode` DISABLE KEYS */;
INSERT INTO `cgnode` VALUES (13,'Default cluster group',1,1),(14,'Default cluster group',1,1);
/*!40000 ALTER TABLE `cgnode` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `name` varchar(45) NOT NULL DEFAULT 'Default',
  `schedule_id` int(10) unsigned NOT NULL,
  `retention_policy_id` int(10) unsigned NOT NULL,
  `volume_id` int(10) unsigned NOT NULL,
  `mysql_user` varchar(45) NOT NULL DEFAULT 'root',
  `mysql_password` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`config_id`),
  KEY `schedule_id_idx` (`schedule_id`),
  KEY `fk_config_retention_policy1_idx` (`retention_policy_id`),
  KEY `fk_config_user1_idx` (`user_id`),
  KEY `fk_config_storage_id_idx` (`volume_id`),
  KEY `idx_user_id_priority` (`user_id`,`priority`),
  CONSTRAINT `fk_config_retention_policy_id` FOREIGN KEY (`retention_policy_id`) REFERENCES `retention_policy` (`retention_policy_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_config_schedule_id` FOREIGN KEY (`schedule_id`) REFERENCES `schedule` (`schedule_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_config_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_config_volume` FOREIGN KEY (`volume_id`) REFERENCES `volume` (`volume_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES (1,1,0,'Default',10,10,5,'twindb_agent','9e6c5a18d5008a13');
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `db_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `db_version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `version` int(10) unsigned DEFAULT NULL,
  `deployed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `db_version` WRITE;
/*!40000 ALTER TABLE `db_version` DISABLE KEYS */;
/*!40000 ALTER TABLE `db_version` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job` (
  `job_id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` char(36) NOT NULL,
  `type` enum('backup','restore') NOT NULL DEFAULT 'backup',
  `start_scheduled` timestamp NULL DEFAULT NULL,
  `status` enum('Scheduled','In progress','Finished','Failed') NOT NULL DEFAULT 'Scheduled',
  `start_actual` timestamp NULL DEFAULT NULL,
  `finish_actual` timestamp NULL DEFAULT NULL,
  `return_code` int(11) DEFAULT NULL,
  `full_backup` enum('Y','N') DEFAULT 'Y',
  `restore_dir` varchar(255) DEFAULT NULL,
  `restore_backup_copy` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`job_id`),
  KEY `idx_server_stat` (`server_id`,`status`),
  KEY `fk_job_server_idx` (`server_id`),
  KEY `idx_server_scheduled` (`server_id`,`start_scheduled`),
  KEY `idx_server_full_date` (`server_id`,`full_backup`,`start_scheduled`),
  CONSTRAINT `fk_job_server_id` FOREIGN KEY (`server_id`) REFERENCES `server` (`server_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `job` WRITE;
/*!40000 ALTER TABLE `job` DISABLE KEYS */;
/*!40000 ALTER TABLE `job` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `log_id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `job_id` bigint(19) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `server_id` char(36) DEFAULT NULL,
  `msg` text,
  PRIMARY KEY (`log_id`),
  KEY `fk_job_id_idx` (`job_id`),
  KEY `fs_log_user_idx` (`user_id`),
  KEY `fk_log_server_idx` (`server_id`),
  CONSTRAINT `fk_log_job` FOREIGN KEY (`job_id`) REFERENCES `job` (`job_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_log_server` FOREIGN KEY (`server_id`) REFERENCES `server` (`server_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fs_log_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `menu_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_item` (
  `menuitem_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `text` text CHARACTER SET utf8,
  `iconCls` varchar(255) DEFAULT NULL,
  `className` varchar(255) DEFAULT NULL,
  `show` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`menuitem_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `menu_item` WRITE;
/*!40000 ALTER TABLE `menu_item` DISABLE KEYS */;
INSERT INTO `menu_item` VALUES (0,'Root',NULL,NULL,'Y'),(1,'Status','report',NULL,'Y'),(2,'Dashboard','map','panel','Y'),(3,'Profile','user_suit',NULL,'Y'),(4,'General','book_addresses','generalprofile','Y'),(5,'Security','shield','securityprofile','Y'),(6,'Notifications','bell','panel','N'),(7,'Server farm','server_database',NULL,'Y'),(8,'Schedule','calendar',NULL,'Y'),(9,'Retention policy','page_white_stack',NULL,'Y'),(10,'Storage','drive',NULL,'Y'),(11,'My subscriptions','coins','ordergrid','Y');
/*!40000 ALTER TABLE `menu_item` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `menu_item_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_item_tree` (
  `ancestor` int(10) unsigned NOT NULL,
  `descendant` int(10) unsigned NOT NULL,
  `depth` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ancestor`,`descendant`),
  KEY `fk_menu_item_tree_menu_item_2_idx` (`descendant`),
  CONSTRAINT `fk_menu_item_tree_menu_item_1` FOREIGN KEY (`ancestor`) REFERENCES `menu_item` (`menuitem_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_menu_item_tree_menu_item_2` FOREIGN KEY (`descendant`) REFERENCES `menu_item` (`menuitem_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `menu_item_tree` WRITE;
/*!40000 ALTER TABLE `menu_item_tree` DISABLE KEYS */;
INSERT INTO `menu_item_tree` VALUES (0,0,0),(0,1,1),(0,2,2),(0,3,1),(0,4,2),(0,5,2),(0,6,2),(0,7,1),(0,8,1),(0,9,1),(0,10,1),(0,11,2),(1,1,0),(1,2,1),(2,2,0),(3,3,0),(3,4,1),(3,5,1),(3,6,1),(3,11,1),(4,4,0),(5,5,0),(6,6,0),(7,7,0),(8,8,0),(9,9,0),(10,10,0),(11,11,0);
/*!40000 ALTER TABLE `menu_item_tree` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order` (
  `order_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `stop_date` date DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `fk_order_package_idx` (`package_id`),
  KEY `fk_order_user_idx` (`user_id`),
  CONSTRAINT `fk_order_package` FOREIGN KEY (`package_id`) REFERENCES `package` (`package_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `order` WRITE;
/*!40000 ALTER TABLE `order` DISABLE KEYS */;
INSERT INTO `order` VALUES (2,2,'2014-08-25',NULL,1),(3,2,'2014-08-25',NULL,1);
/*!40000 ALTER TABLE `order` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package` (
  `package_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `storage` bigint(19) unsigned NOT NULL DEFAULT '53687091200',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '10.00',
  PRIMARY KEY (`package_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `package` WRITE;
/*!40000 ALTER TABLE `package` DISABLE KEYS */;
INSERT INTO `package` VALUES (2,'50G',53687091200,10.00),(3,'100G',107374182400,10.00),(4,'500G',536870912000,10.00),(5,'1T',1073741824000,10.00);
/*!40000 ALTER TABLE `package` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `retention_policy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `retention_policy` (
  `retention_policy_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT 'Default retention policy',
  `Hourly` tinyint(3) unsigned DEFAULT '0',
  `Daily` tinyint(3) unsigned DEFAULT '7',
  `Weekly` tinyint(3) unsigned DEFAULT '0',
  `Monthly` tinyint(3) unsigned DEFAULT '12',
  `Quarterly` tinyint(3) unsigned DEFAULT '0',
  `Yearly` tinyint(3) unsigned DEFAULT '3',
  PRIMARY KEY (`retention_policy_id`),
  KEY `fk_user_idx` (`user_id`),
  KEY `idx_user_id_priority` (`user_id`,`priority`),
  CONSTRAINT `fk__retention_policy_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `retention_policy` WRITE;
/*!40000 ALTER TABLE `retention_policy` DISABLE KEYS */;
INSERT INTO `retention_policy` VALUES (10,1,0,'Default retention policy',0,7,0,12,0,3),(14,1,0,'Super store',0,7,0,12,0,3);
/*!40000 ALTER TABLE `retention_policy` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule` (
  `schedule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `name` varchar(127) NOT NULL DEFAULT 'Default',
  `start_time` time NOT NULL DEFAULT '00:00:00',
  `day` set('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL DEFAULT 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
  `run_once_day` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Yes if backup is run one time per day',
  `period` tinyint(4) DEFAULT NULL COMMENT 'Time between backups in hours',
  `ntimes` tinyint(4) DEFAULT NULL COMMENT 'How many times per day backups if run more than once per day',
  `full_copy` enum('Daily','Weekly','Monthly','Quarterly','Yearly') NOT NULL DEFAULT 'Weekly',
  `time_zone` char(64) NOT NULL DEFAULT 'UTC',
  PRIMARY KEY (`schedule_id`),
  KEY `fk_schedule_user_idx` (`user_id`),
  KEY `idx_user_id_priority` (`user_id`,`priority`),
  CONSTRAINT `fk_schedule_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `schedule` WRITE;
/*!40000 ALTER TABLE `schedule` DISABLE KEYS */;
INSERT INTO `schedule` VALUES (10,1,0,'Default schedule','00:00:00','Mon,Tue,Wed,Thu,Fri,Sat,Sun','Y',NULL,NULL,'Weekly','UTC'),(13,1,0,'Schedule #3','00:00:00','Mon,Tue,Wed,Thu,Fri,Sat,Sun','Y',NULL,NULL,'Weekly','UTC');
/*!40000 ALTER TABLE `schedule` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `server` (
  `server_id` char(36) NOT NULL,
  `cluster_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `time_zone` char(64) NOT NULL DEFAULT 'UTC',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `ssh_public_key` text,
  `enc_public_key` text,
  PRIMARY KEY (`server_id`),
  KEY `fk_server_cgnode_idx` (`cluster_id`),
  CONSTRAINT `fk_server_cgnode` FOREIGN KEY (`cluster_id`) REFERENCES `cgnode` (`cluster_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `server` WRITE;
/*!40000 ALTER TABLE `server` DISABLE KEYS */;
/*!40000 ALTER TABLE `server` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `server_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `server_ip` (
  `server_ip_id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` char(36) NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  PRIMARY KEY (`server_ip_id`),
  KEY `fk_server_idx` (`server_id`),
  CONSTRAINT `fk_server` FOREIGN KEY (`server_id`) REFERENCES `server` (`server_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `server_ip` WRITE;
/*!40000 ALTER TABLE `server_ip` DISABLE KEYS */;
/*!40000 ALTER TABLE `server_ip` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `storage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `storage` (
  `storage_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT 'Default storage',
  `params` text,
  `type` enum('ssh','s3') NOT NULL DEFAULT 'ssh',
  `size` bigint(19) unsigned NOT NULL,
  `used_size` bigint(19) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`storage_id`),
  KEY `fk_storage_user_idx` (`user_id`),
  CONSTRAINT `fk_storage_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `storage` WRITE;
/*!40000 ALTER TABLE `storage` DISABLE KEYS */;
INSERT INTO `storage` VALUES (2,0,'Default storage',NULL,'ssh',9663676416,0);
/*!40000 ALTER TABLE `storage` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `time_zone_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `time_zone_name` (
  `Name` char(64) NOT NULL,
  `Time_zone_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Time zone names';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `time_zone_name` WRITE;
/*!40000 ALTER TABLE `time_zone_name` DISABLE KEYS */;
INSERT INTO `time_zone_name` VALUES ('Africa/Abidjan',1),('Africa/Accra',2),('Africa/Addis_Ababa',3),('Africa/Algiers',4),('Africa/Asmara',5),('Africa/Asmera',6),('Africa/Bamako',7),('Africa/Bangui',8),('Africa/Banjul',9),('Africa/Bissau',10),('Africa/Blantyre',11),('Africa/Brazzaville',12),('Africa/Bujumbura',13),('Africa/Cairo',14),('Africa/Casablanca',15),('Africa/Ceuta',16),('Africa/Conakry',17),('Africa/Dakar',18),('Africa/Dar_es_Salaam',19),('Africa/Djibouti',20),('Africa/Douala',21),('Africa/El_Aaiun',22),('Africa/Freetown',23),('Africa/Gaborone',24),('Africa/Harare',25),('Africa/Johannesburg',26),('Africa/Juba',27),('Africa/Kampala',28),('Africa/Khartoum',29),('Africa/Kigali',30),('Africa/Kinshasa',31),('Africa/Lagos',32),('Africa/Libreville',33),('Africa/Lome',34),('Africa/Luanda',35),('Africa/Lubumbashi',36),('Africa/Lusaka',37),('Africa/Malabo',38),('Africa/Maputo',39),('Africa/Maseru',40),('Africa/Mbabane',41),('Africa/Mogadishu',42),('Africa/Monrovia',43),('Africa/Nairobi',44),('Africa/Ndjamena',45),('Africa/Niamey',46),('Africa/Nouakchott',47),('Africa/Ouagadougou',48),('Africa/Porto-Novo',49),('Africa/Sao_Tome',50),('Africa/Timbuktu',51),('Africa/Tripoli',52),('Africa/Tunis',53),('Africa/Windhoek',54),('America/Adak',55),('America/Anchorage',56),('America/Anguilla',57),('America/Antigua',58),('America/Araguaina',59),('America/Argentina/Buenos_Aires',60),('America/Argentina/Catamarca',61),('America/Argentina/ComodRivadavia',62),('America/Argentina/Cordoba',63),('America/Argentina/Jujuy',64),('America/Argentina/La_Rioja',65),('America/Argentina/Mendoza',66),('America/Argentina/Rio_Gallegos',67),('America/Argentina/Salta',68),('America/Argentina/San_Juan',69),('America/Argentina/San_Luis',70),('America/Argentina/Tucuman',71),('America/Argentina/Ushuaia',72),('America/Aruba',73),('America/Asuncion',74),('America/Atikokan',75),('America/Atka',76),('America/Bahia',77),('America/Bahia_Banderas',78),('America/Barbados',79),('America/Belem',80),('America/Belize',81),('America/Blanc-Sablon',82),('America/Boa_Vista',83),('America/Bogota',84),('America/Boise',85),('America/Buenos_Aires',86),('America/Cambridge_Bay',87),('America/Campo_Grande',88),('America/Cancun',89),('America/Caracas',90),('America/Catamarca',91),('America/Cayenne',92),('America/Cayman',93),('America/Chicago',94),('America/Chihuahua',95),('America/Coral_Harbour',96),('America/Cordoba',97),('America/Costa_Rica',98),('America/Creston',99),('America/Cuiaba',100),('America/Curacao',101),('America/Danmarkshavn',102),('America/Dawson',103),('America/Dawson_Creek',104),('America/Denver',105),('America/Detroit',106),('America/Dominica',107),('America/Edmonton',108),('America/Eirunepe',109),('America/El_Salvador',110),('America/Ensenada',111),('America/Fortaleza',113),('America/Fort_Wayne',112),('America/Glace_Bay',114),('America/Godthab',115),('America/Goose_Bay',116),('America/Grand_Turk',117),('America/Grenada',118),('America/Guadeloupe',119),('America/Guatemala',120),('America/Guayaquil',121),('America/Guyana',122),('America/Halifax',123),('America/Havana',124),('America/Hermosillo',125),('America/Indiana/Indianapolis',126),('America/Indiana/Knox',127),('America/Indiana/Marengo',128),('America/Indiana/Petersburg',129),('America/Indiana/Tell_City',130),('America/Indiana/Vevay',131),('America/Indiana/Vincennes',132),('America/Indiana/Winamac',133),('America/Indianapolis',134),('America/Inuvik',135),('America/Iqaluit',136),('America/Jamaica',137),('America/Jujuy',138),('America/Juneau',139),('America/Kentucky/Louisville',140),('America/Kentucky/Monticello',141),('America/Knox_IN',142),('America/Kralendijk',143),('America/La_Paz',144),('America/Lima',145),('America/Los_Angeles',146),('America/Louisville',147),('America/Lower_Princes',148),('America/Maceio',149),('America/Managua',150),('America/Manaus',151),('America/Marigot',152),('America/Martinique',153),('America/Matamoros',154),('America/Mazatlan',155),('America/Mendoza',156),('America/Menominee',157),('America/Merida',158),('America/Metlakatla',159),('America/Mexico_City',160),('America/Miquelon',161),('America/Moncton',162),('America/Monterrey',163),('America/Montevideo',164),('America/Montreal',165),('America/Montserrat',166),('America/Nassau',167),('America/New_York',168),('America/Nipigon',169),('America/Nome',170),('America/Noronha',171),('America/North_Dakota/Beulah',172),('America/North_Dakota/Center',173),('America/North_Dakota/New_Salem',174),('America/Ojinaga',175),('America/Panama',176),('America/Pangnirtung',177),('America/Paramaribo',178),('America/Phoenix',179),('America/Port-au-Prince',180),('America/Porto_Acre',182),('America/Porto_Velho',183),('America/Port_of_Spain',181),('America/Puerto_Rico',184),('America/Rainy_River',185),('America/Rankin_Inlet',186),('America/Recife',187),('America/Regina',188),('America/Resolute',189),('America/Rio_Branco',190),('America/Rosario',191),('America/Santarem',193),('America/Santa_Isabel',192),('America/Santiago',194),('America/Santo_Domingo',195),('America/Sao_Paulo',196),('America/Scoresbysund',197),('America/Shiprock',198),('America/Sitka',199),('America/St_Barthelemy',200),('America/St_Johns',201),('America/St_Kitts',202),('America/St_Lucia',203),('America/St_Thomas',204),('America/St_Vincent',205),('America/Swift_Current',206),('America/Tegucigalpa',207),('America/Thule',208),('America/Thunder_Bay',209),('America/Tijuana',210),('America/Toronto',211),('America/Tortola',212),('America/Vancouver',213),('America/Virgin',214),('America/Whitehorse',215),('America/Winnipeg',216),('America/Yakutat',217),('America/Yellowknife',218),('Antarctica/Casey',219),('Antarctica/Davis',220),('Antarctica/DumontDUrville',221),('Antarctica/Macquarie',222),('Antarctica/Mawson',223),('Antarctica/McMurdo',224),('Antarctica/Palmer',225),('Antarctica/Rothera',226),('Antarctica/South_Pole',227),('Antarctica/Syowa',228),('Antarctica/Troll',229),('Antarctica/Vostok',230),('Arctic/Longyearbyen',231),('Asia/Aden',232),('Asia/Almaty',233),('Asia/Amman',234),('Asia/Anadyr',235),('Asia/Aqtau',236),('Asia/Aqtobe',237),('Asia/Ashgabat',238),('Asia/Ashkhabad',239),('Asia/Baghdad',240),('Asia/Bahrain',241),('Asia/Baku',242),('Asia/Bangkok',243),('Asia/Beirut',244),('Asia/Bishkek',245),('Asia/Brunei',246),('Asia/Calcutta',247),('Asia/Choibalsan',248),('Asia/Chongqing',249),('Asia/Chungking',250),('Asia/Colombo',251),('Asia/Dacca',252),('Asia/Damascus',253),('Asia/Dhaka',254),('Asia/Dili',255),('Asia/Dubai',256),('Asia/Dushanbe',257),('Asia/Gaza',258),('Asia/Harbin',259),('Asia/Hebron',260),('Asia/Hong_Kong',262),('Asia/Hovd',263),('Asia/Ho_Chi_Minh',261),('Asia/Irkutsk',264),('Asia/Istanbul',265),('Asia/Jakarta',266),('Asia/Jayapura',267),('Asia/Jerusalem',268),('Asia/Kabul',269),('Asia/Kamchatka',270),('Asia/Karachi',271),('Asia/Kashgar',272),('Asia/Kathmandu',273),('Asia/Katmandu',274),('Asia/Khandyga',275),('Asia/Kolkata',276),('Asia/Krasnoyarsk',277),('Asia/Kuala_Lumpur',278),('Asia/Kuching',279),('Asia/Kuwait',280),('Asia/Macao',281),('Asia/Macau',282),('Asia/Magadan',283),('Asia/Makassar',284),('Asia/Manila',285),('Asia/Muscat',286),('Asia/Nicosia',287),('Asia/Novokuznetsk',288),('Asia/Novosibirsk',289),('Asia/Omsk',290),('Asia/Oral',291),('Asia/Phnom_Penh',292),('Asia/Pontianak',293),('Asia/Pyongyang',294),('Asia/Qatar',295),('Asia/Qyzylorda',296),('Asia/Rangoon',297),('Asia/Riyadh',298),('Asia/Saigon',299),('Asia/Sakhalin',300),('Asia/Samarkand',301),('Asia/Seoul',302),('Asia/Shanghai',303),('Asia/Singapore',304),('Asia/Taipei',305),('Asia/Tashkent',306),('Asia/Tbilisi',307),('Asia/Tehran',308),('Asia/Tel_Aviv',309),('Asia/Thimbu',310),('Asia/Thimphu',311),('Asia/Tokyo',312),('Asia/Ujung_Pandang',313),('Asia/Ulaanbaatar',314),('Asia/Ulan_Bator',315),('Asia/Urumqi',316),('Asia/Ust-Nera',317),('Asia/Vientiane',318),('Asia/Vladivostok',319),('Asia/Yakutsk',320),('Asia/Yekaterinburg',321),('Asia/Yerevan',322),('Atlantic/Azores',323),('Atlantic/Bermuda',324),('Atlantic/Canary',325),('Atlantic/Cape_Verde',326),('Atlantic/Faeroe',327),('Atlantic/Faroe',328),('Atlantic/Jan_Mayen',329),('Atlantic/Madeira',330),('Atlantic/Reykjavik',331),('Atlantic/South_Georgia',332),('Atlantic/Stanley',334),('Atlantic/St_Helena',333),('Australia/ACT',335),('Australia/Adelaide',336),('Australia/Brisbane',337),('Australia/Broken_Hill',338),('Australia/Canberra',339),('Australia/Currie',340),('Australia/Darwin',341),('Australia/Eucla',342),('Australia/Hobart',343),('Australia/LHI',344),('Australia/Lindeman',345),('Australia/Lord_Howe',346),('Australia/Melbourne',347),('Australia/North',349),('Australia/NSW',348),('Australia/Perth',350),('Australia/Queensland',351),('Australia/South',352),('Australia/Sydney',353),('Australia/Tasmania',354),('Australia/Victoria',355),('Australia/West',356),('Australia/Yancowinna',357),('Brazil/Acre',358),('Brazil/DeNoronha',359),('Brazil/East',360),('Brazil/West',361),('Canada/Atlantic',364),('Canada/Central',365),('Canada/East-Saskatchewan',366),('Canada/Eastern',367),('Canada/Mountain',368),('Canada/Newfoundland',369),('Canada/Pacific',370),('Canada/Saskatchewan',371),('Canada/Yukon',372),('CET',362),('Chile/Continental',373),('Chile/EasterIsland',374),('CST6CDT',363),('Cuba',375),('EET',376),('Egypt',379),('Eire',380),('EST',377),('EST5EDT',378),('Etc/GMT',381),('Etc/GMT+0',382),('Etc/GMT+1',383),('Etc/GMT+10',384),('Etc/GMT+11',385),('Etc/GMT+12',386),('Etc/GMT+2',387),('Etc/GMT+3',388),('Etc/GMT+4',389),('Etc/GMT+5',390),('Etc/GMT+6',391),('Etc/GMT+7',392),('Etc/GMT+8',393),('Etc/GMT+9',394),('Etc/GMT-0',395),('Etc/GMT-1',396),('Etc/GMT-10',397),('Etc/GMT-11',398),('Etc/GMT-12',399),('Etc/GMT-13',400),('Etc/GMT-14',401),('Etc/GMT-2',402),('Etc/GMT-3',403),('Etc/GMT-4',404),('Etc/GMT-5',405),('Etc/GMT-6',406),('Etc/GMT-7',407),('Etc/GMT-8',408),('Etc/GMT-9',409),('Etc/GMT0',410),('Etc/Greenwich',411),('Etc/UCT',412),('Etc/Universal',414),('Etc/UTC',413),('Etc/Zulu',415),('Europe/Amsterdam',416),('Europe/Andorra',417),('Europe/Athens',418),('Europe/Belfast',419),('Europe/Belgrade',420),('Europe/Berlin',421),('Europe/Bratislava',422),('Europe/Brussels',423),('Europe/Bucharest',424),('Europe/Budapest',425),('Europe/Busingen',426),('Europe/Chisinau',427),('Europe/Copenhagen',428),('Europe/Dublin',429),('Europe/Gibraltar',430),('Europe/Guernsey',431),('Europe/Helsinki',432),('Europe/Isle_of_Man',433),('Europe/Istanbul',434),('Europe/Jersey',435),('Europe/Kaliningrad',436),('Europe/Kiev',437),('Europe/Lisbon',438),('Europe/Ljubljana',439),('Europe/London',440),('Europe/Luxembourg',441),('Europe/Madrid',442),('Europe/Malta',443),('Europe/Mariehamn',444),('Europe/Minsk',445),('Europe/Monaco',446),('Europe/Moscow',447),('Europe/Nicosia',448),('Europe/Oslo',449),('Europe/Paris',450),('Europe/Podgorica',451),('Europe/Prague',452),('Europe/Riga',453),('Europe/Rome',454),('Europe/Samara',455),('Europe/San_Marino',456),('Europe/Sarajevo',457),('Europe/Simferopol',458),('Europe/Skopje',459),('Europe/Sofia',460),('Europe/Stockholm',461),('Europe/Tallinn',462),('Europe/Tirane',463),('Europe/Tiraspol',464),('Europe/Uzhgorod',465),('Europe/Vaduz',466),('Europe/Vatican',467),('Europe/Vienna',468),('Europe/Vilnius',469),('Europe/Volgograd',470),('Europe/Warsaw',471),('Europe/Zagreb',472),('Europe/Zaporozhye',473),('Europe/Zurich',474),('GB',475),('GB-Eire',476),('GMT',477),('GMT+0',478),('GMT-0',479),('GMT0',480),('Greenwich',481),('Hongkong',483),('HST',482),('Iceland',484),('Indian/Antananarivo',485),('Indian/Chagos',486),('Indian/Christmas',487),('Indian/Cocos',488),('Indian/Comoro',489),('Indian/Kerguelen',490),('Indian/Mahe',491),('Indian/Maldives',492),('Indian/Mauritius',493),('Indian/Mayotte',494),('Indian/Reunion',495),('Iran',496),('Israel',497),('Jamaica',498),('Japan',499),('Kwajalein',500),('Libya',501),('MET',502),('Mexico/BajaNorte',505),('Mexico/BajaSur',506),('Mexico/General',507),('MST',503),('MST7MDT',504),('Navajo',510),('NZ',508),('NZ-CHAT',509),('Pacific/Apia',513),('Pacific/Auckland',514),('Pacific/Chatham',515),('Pacific/Chuuk',516),('Pacific/Easter',517),('Pacific/Efate',518),('Pacific/Enderbury',519),('Pacific/Fakaofo',520),('Pacific/Fiji',521),('Pacific/Funafuti',522),('Pacific/Galapagos',523),('Pacific/Gambier',524),('Pacific/Guadalcanal',525),('Pacific/Guam',526),('Pacific/Honolulu',527),('Pacific/Johnston',528),('Pacific/Kiritimati',529),('Pacific/Kosrae',530),('Pacific/Kwajalein',531),('Pacific/Majuro',532),('Pacific/Marquesas',533),('Pacific/Midway',534),('Pacific/Nauru',535),('Pacific/Niue',536),('Pacific/Norfolk',537),('Pacific/Noumea',538),('Pacific/Pago_Pago',539),('Pacific/Palau',540),('Pacific/Pitcairn',541),('Pacific/Pohnpei',542),('Pacific/Ponape',543),('Pacific/Port_Moresby',544),('Pacific/Rarotonga',545),('Pacific/Saipan',546),('Pacific/Samoa',547),('Pacific/Tahiti',548),('Pacific/Tarawa',549),('Pacific/Tongatapu',550),('Pacific/Truk',551),('Pacific/Wake',552),('Pacific/Wallis',553),('Pacific/Yap',554),('Poland',555),('Portugal',556),('posix/Africa/Abidjan',580),('posix/Africa/Accra',581),('posix/Africa/Addis_Ababa',582),('posix/Africa/Algiers',583),('posix/Africa/Asmara',584),('posix/Africa/Asmera',585),('posix/Africa/Bamako',586),('posix/Africa/Bangui',587),('posix/Africa/Banjul',588),('posix/Africa/Bissau',589),('posix/Africa/Blantyre',590),('posix/Africa/Brazzaville',591),('posix/Africa/Bujumbura',592),('posix/Africa/Cairo',593),('posix/Africa/Casablanca',594),('posix/Africa/Ceuta',595),('posix/Africa/Conakry',596),('posix/Africa/Dakar',597),('posix/Africa/Dar_es_Salaam',598),('posix/Africa/Djibouti',599),('posix/Africa/Douala',600),('posix/Africa/El_Aaiun',601),('posix/Africa/Freetown',602),('posix/Africa/Gaborone',603),('posix/Africa/Harare',604),('posix/Africa/Johannesburg',605),('posix/Africa/Juba',606),('posix/Africa/Kampala',607),('posix/Africa/Khartoum',608),('posix/Africa/Kigali',609),('posix/Africa/Kinshasa',610),('posix/Africa/Lagos',611),('posix/Africa/Libreville',612),('posix/Africa/Lome',613),('posix/Africa/Luanda',614),('posix/Africa/Lubumbashi',615),('posix/Africa/Lusaka',616),('posix/Africa/Malabo',617),('posix/Africa/Maputo',618),('posix/Africa/Maseru',619),('posix/Africa/Mbabane',620),('posix/Africa/Mogadishu',621),('posix/Africa/Monrovia',622),('posix/Africa/Nairobi',623),('posix/Africa/Ndjamena',624),('posix/Africa/Niamey',625),('posix/Africa/Nouakchott',626),('posix/Africa/Ouagadougou',627),('posix/Africa/Porto-Novo',628),('posix/Africa/Sao_Tome',629),('posix/Africa/Timbuktu',630),('posix/Africa/Tripoli',631),('posix/Africa/Tunis',632),('posix/Africa/Windhoek',633),('posix/America/Adak',634),('posix/America/Anchorage',635),('posix/America/Anguilla',636),('posix/America/Antigua',637),('posix/America/Araguaina',638),('posix/America/Argentina/Buenos_Aires',639),('posix/America/Argentina/Catamarca',640),('posix/America/Argentina/ComodRivadavia',641),('posix/America/Argentina/Cordoba',642),('posix/America/Argentina/Jujuy',643),('posix/America/Argentina/La_Rioja',644),('posix/America/Argentina/Mendoza',645),('posix/America/Argentina/Rio_Gallegos',646),('posix/America/Argentina/Salta',647),('posix/America/Argentina/San_Juan',648),('posix/America/Argentina/San_Luis',649),('posix/America/Argentina/Tucuman',650),('posix/America/Argentina/Ushuaia',651),('posix/America/Aruba',652),('posix/America/Asuncion',653),('posix/America/Atikokan',654),('posix/America/Atka',655),('posix/America/Bahia',656),('posix/America/Bahia_Banderas',657),('posix/America/Barbados',658),('posix/America/Belem',659),('posix/America/Belize',660),('posix/America/Blanc-Sablon',661),('posix/America/Boa_Vista',662),('posix/America/Bogota',663),('posix/America/Boise',664),('posix/America/Buenos_Aires',665),('posix/America/Cambridge_Bay',666),('posix/America/Campo_Grande',667),('posix/America/Cancun',668),('posix/America/Caracas',669),('posix/America/Catamarca',670),('posix/America/Cayenne',671),('posix/America/Cayman',672),('posix/America/Chicago',673),('posix/America/Chihuahua',674),('posix/America/Coral_Harbour',675),('posix/America/Cordoba',676),('posix/America/Costa_Rica',677),('posix/America/Creston',678),('posix/America/Cuiaba',679),('posix/America/Curacao',680),('posix/America/Danmarkshavn',681),('posix/America/Dawson',682),('posix/America/Dawson_Creek',683),('posix/America/Denver',684),('posix/America/Detroit',685),('posix/America/Dominica',686),('posix/America/Edmonton',687),('posix/America/Eirunepe',688),('posix/America/El_Salvador',689),('posix/America/Ensenada',690),('posix/America/Fortaleza',692),('posix/America/Fort_Wayne',691),('posix/America/Glace_Bay',693),('posix/America/Godthab',694),('posix/America/Goose_Bay',695),('posix/America/Grand_Turk',696),('posix/America/Grenada',697),('posix/America/Guadeloupe',698),('posix/America/Guatemala',699),('posix/America/Guayaquil',700),('posix/America/Guyana',701),('posix/America/Halifax',702),('posix/America/Havana',703),('posix/America/Hermosillo',704),('posix/America/Indiana/Indianapolis',705),('posix/America/Indiana/Knox',706),('posix/America/Indiana/Marengo',707),('posix/America/Indiana/Petersburg',708),('posix/America/Indiana/Tell_City',709),('posix/America/Indiana/Vevay',710),('posix/America/Indiana/Vincennes',711),('posix/America/Indiana/Winamac',712),('posix/America/Indianapolis',713),('posix/America/Inuvik',714),('posix/America/Iqaluit',715),('posix/America/Jamaica',716),('posix/America/Jujuy',717),('posix/America/Juneau',718),('posix/America/Kentucky/Louisville',719),('posix/America/Kentucky/Monticello',720),('posix/America/Knox_IN',721),('posix/America/Kralendijk',722),('posix/America/La_Paz',723),('posix/America/Lima',724),('posix/America/Los_Angeles',725),('posix/America/Louisville',726),('posix/America/Lower_Princes',727),('posix/America/Maceio',728),('posix/America/Managua',729),('posix/America/Manaus',730),('posix/America/Marigot',731),('posix/America/Martinique',732),('posix/America/Matamoros',733),('posix/America/Mazatlan',734),('posix/America/Mendoza',735),('posix/America/Menominee',736),('posix/America/Merida',737),('posix/America/Metlakatla',738),('posix/America/Mexico_City',739),('posix/America/Miquelon',740),('posix/America/Moncton',741),('posix/America/Monterrey',742),('posix/America/Montevideo',743),('posix/America/Montreal',744),('posix/America/Montserrat',745),('posix/America/Nassau',746),('posix/America/New_York',747),('posix/America/Nipigon',748),('posix/America/Nome',749),('posix/America/Noronha',750),('posix/America/North_Dakota/Beulah',751),('posix/America/North_Dakota/Center',752),('posix/America/North_Dakota/New_Salem',753),('posix/America/Ojinaga',754),('posix/America/Panama',755),('posix/America/Pangnirtung',756),('posix/America/Paramaribo',757),('posix/America/Phoenix',758),('posix/America/Port-au-Prince',759),('posix/America/Porto_Acre',761),('posix/America/Porto_Velho',762),('posix/America/Port_of_Spain',760),('posix/America/Puerto_Rico',763),('posix/America/Rainy_River',764),('posix/America/Rankin_Inlet',765),('posix/America/Recife',766),('posix/America/Regina',767),('posix/America/Resolute',768),('posix/America/Rio_Branco',769),('posix/America/Rosario',770),('posix/America/Santarem',772),('posix/America/Santa_Isabel',771),('posix/America/Santiago',773),('posix/America/Santo_Domingo',774),('posix/America/Sao_Paulo',775),('posix/America/Scoresbysund',776),('posix/America/Shiprock',777),('posix/America/Sitka',778),('posix/America/St_Barthelemy',779),('posix/America/St_Johns',780),('posix/America/St_Kitts',781),('posix/America/St_Lucia',782),('posix/America/St_Thomas',783),('posix/America/St_Vincent',784),('posix/America/Swift_Current',785),('posix/America/Tegucigalpa',786),('posix/America/Thule',787),('posix/America/Thunder_Bay',788),('posix/America/Tijuana',789),('posix/America/Toronto',790),('posix/America/Tortola',791),('posix/America/Vancouver',792),('posix/America/Virgin',793),('posix/America/Whitehorse',794),('posix/America/Winnipeg',795),('posix/America/Yakutat',796),('posix/America/Yellowknife',797),('posix/Antarctica/Casey',798),('posix/Antarctica/Davis',799),('posix/Antarctica/DumontDUrville',800),('posix/Antarctica/Macquarie',801),('posix/Antarctica/Mawson',802),('posix/Antarctica/McMurdo',803),('posix/Antarctica/Palmer',804),('posix/Antarctica/Rothera',805),('posix/Antarctica/South_Pole',806),('posix/Antarctica/Syowa',807),('posix/Antarctica/Troll',808),('posix/Antarctica/Vostok',809),('posix/Arctic/Longyearbyen',810),('posix/Asia/Aden',811),('posix/Asia/Almaty',812),('posix/Asia/Amman',813),('posix/Asia/Anadyr',814),('posix/Asia/Aqtau',815),('posix/Asia/Aqtobe',816),('posix/Asia/Ashgabat',817),('posix/Asia/Ashkhabad',818),('posix/Asia/Baghdad',819),('posix/Asia/Bahrain',820),('posix/Asia/Baku',821),('posix/Asia/Bangkok',822),('posix/Asia/Beirut',823),('posix/Asia/Bishkek',824),('posix/Asia/Brunei',825),('posix/Asia/Calcutta',826),('posix/Asia/Choibalsan',827),('posix/Asia/Chongqing',828),('posix/Asia/Chungking',829),('posix/Asia/Colombo',830),('posix/Asia/Dacca',831),('posix/Asia/Damascus',832),('posix/Asia/Dhaka',833),('posix/Asia/Dili',834),('posix/Asia/Dubai',835),('posix/Asia/Dushanbe',836),('posix/Asia/Gaza',837),('posix/Asia/Harbin',838),('posix/Asia/Hebron',839),('posix/Asia/Hong_Kong',841),('posix/Asia/Hovd',842),('posix/Asia/Ho_Chi_Minh',840),('posix/Asia/Irkutsk',843),('posix/Asia/Istanbul',844),('posix/Asia/Jakarta',845),('posix/Asia/Jayapura',846),('posix/Asia/Jerusalem',847),('posix/Asia/Kabul',848),('posix/Asia/Kamchatka',849),('posix/Asia/Karachi',850),('posix/Asia/Kashgar',851),('posix/Asia/Kathmandu',852),('posix/Asia/Katmandu',853),('posix/Asia/Khandyga',854),('posix/Asia/Kolkata',855),('posix/Asia/Krasnoyarsk',856),('posix/Asia/Kuala_Lumpur',857),('posix/Asia/Kuching',858),('posix/Asia/Kuwait',859),('posix/Asia/Macao',860),('posix/Asia/Macau',861),('posix/Asia/Magadan',862),('posix/Asia/Makassar',863),('posix/Asia/Manila',864),('posix/Asia/Muscat',865),('posix/Asia/Nicosia',866),('posix/Asia/Novokuznetsk',867),('posix/Asia/Novosibirsk',868),('posix/Asia/Omsk',869),('posix/Asia/Oral',870),('posix/Asia/Phnom_Penh',871),('posix/Asia/Pontianak',872),('posix/Asia/Pyongyang',873),('posix/Asia/Qatar',874),('posix/Asia/Qyzylorda',875),('posix/Asia/Rangoon',876),('posix/Asia/Riyadh',877),('posix/Asia/Saigon',878),('posix/Asia/Sakhalin',879),('posix/Asia/Samarkand',880),('posix/Asia/Seoul',881),('posix/Asia/Shanghai',882),('posix/Asia/Singapore',883),('posix/Asia/Taipei',884),('posix/Asia/Tashkent',885),('posix/Asia/Tbilisi',886),('posix/Asia/Tehran',887),('posix/Asia/Tel_Aviv',888),('posix/Asia/Thimbu',889),('posix/Asia/Thimphu',890),('posix/Asia/Tokyo',891),('posix/Asia/Ujung_Pandang',892),('posix/Asia/Ulaanbaatar',893),('posix/Asia/Ulan_Bator',894),('posix/Asia/Urumqi',895),('posix/Asia/Ust-Nera',896),('posix/Asia/Vientiane',897),('posix/Asia/Vladivostok',898),('posix/Asia/Yakutsk',899),('posix/Asia/Yekaterinburg',900),('posix/Asia/Yerevan',901),('posix/Atlantic/Azores',902),('posix/Atlantic/Bermuda',903),('posix/Atlantic/Canary',904),('posix/Atlantic/Cape_Verde',905),('posix/Atlantic/Faeroe',906),('posix/Atlantic/Faroe',907),('posix/Atlantic/Jan_Mayen',908),('posix/Atlantic/Madeira',909),('posix/Atlantic/Reykjavik',910),('posix/Atlantic/South_Georgia',911),('posix/Atlantic/Stanley',913),('posix/Atlantic/St_Helena',912),('posix/Australia/ACT',914),('posix/Australia/Adelaide',915),('posix/Australia/Brisbane',916),('posix/Australia/Broken_Hill',917),('posix/Australia/Canberra',918),('posix/Australia/Currie',919),('posix/Australia/Darwin',920),('posix/Australia/Eucla',921),('posix/Australia/Hobart',922),('posix/Australia/LHI',923),('posix/Australia/Lindeman',924),('posix/Australia/Lord_Howe',925),('posix/Australia/Melbourne',926),('posix/Australia/North',928),('posix/Australia/NSW',927),('posix/Australia/Perth',929),('posix/Australia/Queensland',930),('posix/Australia/South',931),('posix/Australia/Sydney',932),('posix/Australia/Tasmania',933),('posix/Australia/Victoria',934),('posix/Australia/West',935),('posix/Australia/Yancowinna',936),('posix/Brazil/Acre',937),('posix/Brazil/DeNoronha',938),('posix/Brazil/East',939),('posix/Brazil/West',940),('posix/Canada/Atlantic',943),('posix/Canada/Central',944),('posix/Canada/East-Saskatchewan',945),('posix/Canada/Eastern',946),('posix/Canada/Mountain',947),('posix/Canada/Newfoundland',948),('posix/Canada/Pacific',949),('posix/Canada/Saskatchewan',950),('posix/Canada/Yukon',951),('posix/CET',941),('posix/Chile/Continental',952),('posix/Chile/EasterIsland',953),('posix/CST6CDT',942),('posix/Cuba',954),('posix/EET',955),('posix/Egypt',958),('posix/Eire',959),('posix/EST',956),('posix/EST5EDT',957),('posix/Etc/GMT',960),('posix/Etc/GMT+0',961),('posix/Etc/GMT+1',962),('posix/Etc/GMT+10',963),('posix/Etc/GMT+11',964),('posix/Etc/GMT+12',965),('posix/Etc/GMT+2',966),('posix/Etc/GMT+3',967),('posix/Etc/GMT+4',968),('posix/Etc/GMT+5',969),('posix/Etc/GMT+6',970),('posix/Etc/GMT+7',971),('posix/Etc/GMT+8',972),('posix/Etc/GMT+9',973),('posix/Etc/GMT-0',974),('posix/Etc/GMT-1',975),('posix/Etc/GMT-10',976),('posix/Etc/GMT-11',977),('posix/Etc/GMT-12',978),('posix/Etc/GMT-13',979),('posix/Etc/GMT-14',980),('posix/Etc/GMT-2',981),('posix/Etc/GMT-3',982),('posix/Etc/GMT-4',983),('posix/Etc/GMT-5',984),('posix/Etc/GMT-6',985),('posix/Etc/GMT-7',986),('posix/Etc/GMT-8',987),('posix/Etc/GMT-9',988),('posix/Etc/GMT0',989),('posix/Etc/Greenwich',990),('posix/Etc/UCT',991),('posix/Etc/Universal',993),('posix/Etc/UTC',992),('posix/Etc/Zulu',994),('posix/Europe/Amsterdam',995),('posix/Europe/Andorra',996),('posix/Europe/Athens',997),('posix/Europe/Belfast',998),('posix/Europe/Belgrade',999),('posix/Europe/Berlin',1000),('posix/Europe/Bratislava',1001),('posix/Europe/Brussels',1002),('posix/Europe/Bucharest',1003),('posix/Europe/Budapest',1004),('posix/Europe/Busingen',1005),('posix/Europe/Chisinau',1006),('posix/Europe/Copenhagen',1007),('posix/Europe/Dublin',1008),('posix/Europe/Gibraltar',1009),('posix/Europe/Guernsey',1010),('posix/Europe/Helsinki',1011),('posix/Europe/Isle_of_Man',1012),('posix/Europe/Istanbul',1013),('posix/Europe/Jersey',1014),('posix/Europe/Kaliningrad',1015),('posix/Europe/Kiev',1016),('posix/Europe/Lisbon',1017),('posix/Europe/Ljubljana',1018),('posix/Europe/London',1019),('posix/Europe/Luxembourg',1020),('posix/Europe/Madrid',1021),('posix/Europe/Malta',1022),('posix/Europe/Mariehamn',1023),('posix/Europe/Minsk',1024),('posix/Europe/Monaco',1025),('posix/Europe/Moscow',1026),('posix/Europe/Nicosia',1027),('posix/Europe/Oslo',1028),('posix/Europe/Paris',1029),('posix/Europe/Podgorica',1030),('posix/Europe/Prague',1031),('posix/Europe/Riga',1032),('posix/Europe/Rome',1033),('posix/Europe/Samara',1034),('posix/Europe/San_Marino',1035),('posix/Europe/Sarajevo',1036),('posix/Europe/Simferopol',1037),('posix/Europe/Skopje',1038),('posix/Europe/Sofia',1039),('posix/Europe/Stockholm',1040),('posix/Europe/Tallinn',1041),('posix/Europe/Tirane',1042),('posix/Europe/Tiraspol',1043),('posix/Europe/Uzhgorod',1044),('posix/Europe/Vaduz',1045),('posix/Europe/Vatican',1046),('posix/Europe/Vienna',1047),('posix/Europe/Vilnius',1048),('posix/Europe/Volgograd',1049),('posix/Europe/Warsaw',1050),('posix/Europe/Zagreb',1051),('posix/Europe/Zaporozhye',1052),('posix/Europe/Zurich',1053),('posix/GB',1054),('posix/GB-Eire',1055),('posix/GMT',1056),('posix/GMT+0',1057),('posix/GMT-0',1058),('posix/GMT0',1059),('posix/Greenwich',1060),('posix/Hongkong',1062),('posix/HST',1061),('posix/Iceland',1063),('posix/Indian/Antananarivo',1064),('posix/Indian/Chagos',1065),('posix/Indian/Christmas',1066),('posix/Indian/Cocos',1067),('posix/Indian/Comoro',1068),('posix/Indian/Kerguelen',1069),('posix/Indian/Mahe',1070),('posix/Indian/Maldives',1071),('posix/Indian/Mauritius',1072),('posix/Indian/Mayotte',1073),('posix/Indian/Reunion',1074),('posix/Iran',1075),('posix/Israel',1076),('posix/Jamaica',1077),('posix/Japan',1078),('posix/Kwajalein',1079),('posix/Libya',1080),('posix/MET',1081),('posix/Mexico/BajaNorte',1084),('posix/Mexico/BajaSur',1085),('posix/Mexico/General',1086),('posix/MST',1082),('posix/MST7MDT',1083),('posix/Navajo',1089),('posix/NZ',1087),('posix/NZ-CHAT',1088),('posix/Pacific/Apia',1092),('posix/Pacific/Auckland',1093),('posix/Pacific/Chatham',1094),('posix/Pacific/Chuuk',1095),('posix/Pacific/Easter',1096),('posix/Pacific/Efate',1097),('posix/Pacific/Enderbury',1098),('posix/Pacific/Fakaofo',1099),('posix/Pacific/Fiji',1100),('posix/Pacific/Funafuti',1101),('posix/Pacific/Galapagos',1102),('posix/Pacific/Gambier',1103),('posix/Pacific/Guadalcanal',1104),('posix/Pacific/Guam',1105),('posix/Pacific/Honolulu',1106),('posix/Pacific/Johnston',1107),('posix/Pacific/Kiritimati',1108),('posix/Pacific/Kosrae',1109),('posix/Pacific/Kwajalein',1110),('posix/Pacific/Majuro',1111),('posix/Pacific/Marquesas',1112),('posix/Pacific/Midway',1113),('posix/Pacific/Nauru',1114),('posix/Pacific/Niue',1115),('posix/Pacific/Norfolk',1116),('posix/Pacific/Noumea',1117),('posix/Pacific/Pago_Pago',1118),('posix/Pacific/Palau',1119),('posix/Pacific/Pitcairn',1120),('posix/Pacific/Pohnpei',1121),('posix/Pacific/Ponape',1122),('posix/Pacific/Port_Moresby',1123),('posix/Pacific/Rarotonga',1124),('posix/Pacific/Saipan',1125),('posix/Pacific/Samoa',1126),('posix/Pacific/Tahiti',1127),('posix/Pacific/Tarawa',1128),('posix/Pacific/Tongatapu',1129),('posix/Pacific/Truk',1130),('posix/Pacific/Wake',1131),('posix/Pacific/Wallis',1132),('posix/Pacific/Yap',1133),('posix/Poland',1134),('posix/Portugal',1135),('posix/PRC',1090),('posix/PST8PDT',1091),('posix/ROC',1136),('posix/ROK',1137),('posix/Singapore',1138),('posix/Turkey',1139),('posix/UCT',1140),('posix/Universal',1155),('posix/US/Alaska',1141),('posix/US/Aleutian',1142),('posix/US/Arizona',1143),('posix/US/Central',1144),('posix/US/East-Indiana',1145),('posix/US/Eastern',1146),('posix/US/Hawaii',1147),('posix/US/Indiana-Starke',1148),('posix/US/Michigan',1149),('posix/US/Mountain',1150),('posix/US/Pacific',1151),('posix/US/Pacific-New',1152),('posix/US/Samoa',1153),('posix/UTC',1154),('posix/W-SU',1156),('posix/WET',1157),('posix/Zulu',1158),('posixrules',1159),('PRC',511),('PST8PDT',512),('right/Africa/Abidjan',1160),('right/Africa/Accra',1161),('right/Africa/Addis_Ababa',1162),('right/Africa/Algiers',1163),('right/Africa/Asmara',1164),('right/Africa/Asmera',1165),('right/Africa/Bamako',1166),('right/Africa/Bangui',1167),('right/Africa/Banjul',1168),('right/Africa/Bissau',1169),('right/Africa/Blantyre',1170),('right/Africa/Brazzaville',1171),('right/Africa/Bujumbura',1172),('right/Africa/Cairo',1173),('right/Africa/Casablanca',1174),('right/Africa/Ceuta',1175),('right/Africa/Conakry',1176),('right/Africa/Dakar',1177),('right/Africa/Dar_es_Salaam',1178),('right/Africa/Djibouti',1179),('right/Africa/Douala',1180),('right/Africa/El_Aaiun',1181),('right/Africa/Freetown',1182),('right/Africa/Gaborone',1183),('right/Africa/Harare',1184),('right/Africa/Johannesburg',1185),('right/Africa/Juba',1186),('right/Africa/Kampala',1187),('right/Africa/Khartoum',1188),('right/Africa/Kigali',1189),('right/Africa/Kinshasa',1190),('right/Africa/Lagos',1191),('right/Africa/Libreville',1192),('right/Africa/Lome',1193),('right/Africa/Luanda',1194),('right/Africa/Lubumbashi',1195),('right/Africa/Lusaka',1196),('right/Africa/Malabo',1197),('right/Africa/Maputo',1198),('right/Africa/Maseru',1199),('right/Africa/Mbabane',1200),('right/Africa/Mogadishu',1201),('right/Africa/Monrovia',1202),('right/Africa/Nairobi',1203),('right/Africa/Ndjamena',1204),('right/Africa/Niamey',1205),('right/Africa/Nouakchott',1206),('right/Africa/Ouagadougou',1207),('right/Africa/Porto-Novo',1208),('right/Africa/Sao_Tome',1209),('right/Africa/Timbuktu',1210),('right/Africa/Tripoli',1211),('right/Africa/Tunis',1212),('right/Africa/Windhoek',1213),('right/America/Adak',1214),('right/America/Anchorage',1215),('right/America/Anguilla',1216),('right/America/Antigua',1217),('right/America/Araguaina',1218),('right/America/Argentina/Buenos_Aires',1219),('right/America/Argentina/Catamarca',1220),('right/America/Argentina/ComodRivadavia',1221),('right/America/Argentina/Cordoba',1222),('right/America/Argentina/Jujuy',1223),('right/America/Argentina/La_Rioja',1224),('right/America/Argentina/Mendoza',1225),('right/America/Argentina/Rio_Gallegos',1226),('right/America/Argentina/Salta',1227),('right/America/Argentina/San_Juan',1228),('right/America/Argentina/San_Luis',1229),('right/America/Argentina/Tucuman',1230),('right/America/Argentina/Ushuaia',1231),('right/America/Aruba',1232),('right/America/Asuncion',1233),('right/America/Atikokan',1234),('right/America/Atka',1235),('right/America/Bahia',1236),('right/America/Bahia_Banderas',1237),('right/America/Barbados',1238),('right/America/Belem',1239),('right/America/Belize',1240),('right/America/Blanc-Sablon',1241),('right/America/Boa_Vista',1242),('right/America/Bogota',1243),('right/America/Boise',1244),('right/America/Buenos_Aires',1245),('right/America/Cambridge_Bay',1246),('right/America/Campo_Grande',1247),('right/America/Cancun',1248),('right/America/Caracas',1249),('right/America/Catamarca',1250),('right/America/Cayenne',1251),('right/America/Cayman',1252),('right/America/Chicago',1253),('right/America/Chihuahua',1254),('right/America/Coral_Harbour',1255),('right/America/Cordoba',1256),('right/America/Costa_Rica',1257),('right/America/Creston',1258),('right/America/Cuiaba',1259),('right/America/Curacao',1260),('right/America/Danmarkshavn',1261),('right/America/Dawson',1262),('right/America/Dawson_Creek',1263),('right/America/Denver',1264),('right/America/Detroit',1265),('right/America/Dominica',1266),('right/America/Edmonton',1267),('right/America/Eirunepe',1268),('right/America/El_Salvador',1269),('right/America/Ensenada',1270),('right/America/Fortaleza',1272),('right/America/Fort_Wayne',1271),('right/America/Glace_Bay',1273),('right/America/Godthab',1274),('right/America/Goose_Bay',1275),('right/America/Grand_Turk',1276),('right/America/Grenada',1277),('right/America/Guadeloupe',1278),('right/America/Guatemala',1279),('right/America/Guayaquil',1280),('right/America/Guyana',1281),('right/America/Halifax',1282),('right/America/Havana',1283),('right/America/Hermosillo',1284),('right/America/Indiana/Indianapolis',1285),('right/America/Indiana/Knox',1286),('right/America/Indiana/Marengo',1287),('right/America/Indiana/Petersburg',1288),('right/America/Indiana/Tell_City',1289),('right/America/Indiana/Vevay',1290),('right/America/Indiana/Vincennes',1291),('right/America/Indiana/Winamac',1292),('right/America/Indianapolis',1293),('right/America/Inuvik',1294),('right/America/Iqaluit',1295),('right/America/Jamaica',1296),('right/America/Jujuy',1297),('right/America/Juneau',1298),('right/America/Kentucky/Louisville',1299),('right/America/Kentucky/Monticello',1300),('right/America/Knox_IN',1301),('right/America/Kralendijk',1302),('right/America/La_Paz',1303),('right/America/Lima',1304),('right/America/Los_Angeles',1305),('right/America/Louisville',1306),('right/America/Lower_Princes',1307),('right/America/Maceio',1308),('right/America/Managua',1309),('right/America/Manaus',1310),('right/America/Marigot',1311),('right/America/Martinique',1312),('right/America/Matamoros',1313),('right/America/Mazatlan',1314),('right/America/Mendoza',1315),('right/America/Menominee',1316),('right/America/Merida',1317),('right/America/Metlakatla',1318),('right/America/Mexico_City',1319),('right/America/Miquelon',1320),('right/America/Moncton',1321),('right/America/Monterrey',1322),('right/America/Montevideo',1323),('right/America/Montreal',1324),('right/America/Montserrat',1325),('right/America/Nassau',1326),('right/America/New_York',1327),('right/America/Nipigon',1328),('right/America/Nome',1329),('right/America/Noronha',1330),('right/America/North_Dakota/Beulah',1331),('right/America/North_Dakota/Center',1332),('right/America/North_Dakota/New_Salem',1333),('right/America/Ojinaga',1334),('right/America/Panama',1335),('right/America/Pangnirtung',1336),('right/America/Paramaribo',1337),('right/America/Phoenix',1338),('right/America/Port-au-Prince',1339),('right/America/Porto_Acre',1341),('right/America/Porto_Velho',1342),('right/America/Port_of_Spain',1340),('right/America/Puerto_Rico',1343),('right/America/Rainy_River',1344),('right/America/Rankin_Inlet',1345),('right/America/Recife',1346),('right/America/Regina',1347),('right/America/Resolute',1348),('right/America/Rio_Branco',1349),('right/America/Rosario',1350),('right/America/Santarem',1352),('right/America/Santa_Isabel',1351),('right/America/Santiago',1353),('right/America/Santo_Domingo',1354),('right/America/Sao_Paulo',1355),('right/America/Scoresbysund',1356),('right/America/Shiprock',1357),('right/America/Sitka',1358),('right/America/St_Barthelemy',1359),('right/America/St_Johns',1360),('right/America/St_Kitts',1361),('right/America/St_Lucia',1362),('right/America/St_Thomas',1363),('right/America/St_Vincent',1364),('right/America/Swift_Current',1365),('right/America/Tegucigalpa',1366),('right/America/Thule',1367),('right/America/Thunder_Bay',1368),('right/America/Tijuana',1369),('right/America/Toronto',1370),('right/America/Tortola',1371),('right/America/Vancouver',1372),('right/America/Virgin',1373),('right/America/Whitehorse',1374),('right/America/Winnipeg',1375),('right/America/Yakutat',1376),('right/America/Yellowknife',1377),('right/Antarctica/Casey',1378),('right/Antarctica/Davis',1379),('right/Antarctica/DumontDUrville',1380),('right/Antarctica/Macquarie',1381),('right/Antarctica/Mawson',1382),('right/Antarctica/McMurdo',1383),('right/Antarctica/Palmer',1384),('right/Antarctica/Rothera',1385),('right/Antarctica/South_Pole',1386),('right/Antarctica/Syowa',1387),('right/Antarctica/Troll',1388),('right/Antarctica/Vostok',1389),('right/Arctic/Longyearbyen',1390),('right/Asia/Aden',1391),('right/Asia/Almaty',1392),('right/Asia/Amman',1393),('right/Asia/Anadyr',1394),('right/Asia/Aqtau',1395),('right/Asia/Aqtobe',1396),('right/Asia/Ashgabat',1397),('right/Asia/Ashkhabad',1398),('right/Asia/Baghdad',1399),('right/Asia/Bahrain',1400),('right/Asia/Baku',1401),('right/Asia/Bangkok',1402),('right/Asia/Beirut',1403),('right/Asia/Bishkek',1404),('right/Asia/Brunei',1405),('right/Asia/Calcutta',1406),('right/Asia/Choibalsan',1407),('right/Asia/Chongqing',1408),('right/Asia/Chungking',1409),('right/Asia/Colombo',1410),('right/Asia/Dacca',1411),('right/Asia/Damascus',1412),('right/Asia/Dhaka',1413),('right/Asia/Dili',1414),('right/Asia/Dubai',1415),('right/Asia/Dushanbe',1416),('right/Asia/Gaza',1417),('right/Asia/Harbin',1418),('right/Asia/Hebron',1419),('right/Asia/Hong_Kong',1421),('right/Asia/Hovd',1422),('right/Asia/Ho_Chi_Minh',1420),('right/Asia/Irkutsk',1423),('right/Asia/Istanbul',1424),('right/Asia/Jakarta',1425),('right/Asia/Jayapura',1426),('right/Asia/Jerusalem',1427),('right/Asia/Kabul',1428),('right/Asia/Kamchatka',1429),('right/Asia/Karachi',1430),('right/Asia/Kashgar',1431),('right/Asia/Kathmandu',1432),('right/Asia/Katmandu',1433),('right/Asia/Khandyga',1434),('right/Asia/Kolkata',1435),('right/Asia/Krasnoyarsk',1436),('right/Asia/Kuala_Lumpur',1437),('right/Asia/Kuching',1438),('right/Asia/Kuwait',1439),('right/Asia/Macao',1440),('right/Asia/Macau',1441),('right/Asia/Magadan',1442),('right/Asia/Makassar',1443),('right/Asia/Manila',1444),('right/Asia/Muscat',1445),('right/Asia/Nicosia',1446),('right/Asia/Novokuznetsk',1447),('right/Asia/Novosibirsk',1448),('right/Asia/Omsk',1449),('right/Asia/Oral',1450),('right/Asia/Phnom_Penh',1451),('right/Asia/Pontianak',1452),('right/Asia/Pyongyang',1453),('right/Asia/Qatar',1454),('right/Asia/Qyzylorda',1455),('right/Asia/Rangoon',1456),('right/Asia/Riyadh',1457),('right/Asia/Saigon',1458),('right/Asia/Sakhalin',1459),('right/Asia/Samarkand',1460),('right/Asia/Seoul',1461),('right/Asia/Shanghai',1462),('right/Asia/Singapore',1463),('right/Asia/Taipei',1464),('right/Asia/Tashkent',1465),('right/Asia/Tbilisi',1466),('right/Asia/Tehran',1467),('right/Asia/Tel_Aviv',1468),('right/Asia/Thimbu',1469),('right/Asia/Thimphu',1470),('right/Asia/Tokyo',1471),('right/Asia/Ujung_Pandang',1472),('right/Asia/Ulaanbaatar',1473),('right/Asia/Ulan_Bator',1474),('right/Asia/Urumqi',1475),('right/Asia/Ust-Nera',1476),('right/Asia/Vientiane',1477),('right/Asia/Vladivostok',1478),('right/Asia/Yakutsk',1479),('right/Asia/Yekaterinburg',1480),('right/Asia/Yerevan',1481),('right/Atlantic/Azores',1482),('right/Atlantic/Bermuda',1483),('right/Atlantic/Canary',1484),('right/Atlantic/Cape_Verde',1485),('right/Atlantic/Faeroe',1486),('right/Atlantic/Faroe',1487),('right/Atlantic/Jan_Mayen',1488),('right/Atlantic/Madeira',1489),('right/Atlantic/Reykjavik',1490),('right/Atlantic/South_Georgia',1491),('right/Atlantic/Stanley',1493),('right/Atlantic/St_Helena',1492),('right/Australia/ACT',1494),('right/Australia/Adelaide',1495),('right/Australia/Brisbane',1496),('right/Australia/Broken_Hill',1497),('right/Australia/Canberra',1498),('right/Australia/Currie',1499),('right/Australia/Darwin',1500),('right/Australia/Eucla',1501),('right/Australia/Hobart',1502),('right/Australia/LHI',1503),('right/Australia/Lindeman',1504),('right/Australia/Lord_Howe',1505),('right/Australia/Melbourne',1506),('right/Australia/North',1508),('right/Australia/NSW',1507),('right/Australia/Perth',1509),('right/Australia/Queensland',1510),('right/Australia/South',1511),('right/Australia/Sydney',1512),('right/Australia/Tasmania',1513),('right/Australia/Victoria',1514),('right/Australia/West',1515),('right/Australia/Yancowinna',1516),('right/Brazil/Acre',1517),('right/Brazil/DeNoronha',1518),('right/Brazil/East',1519),('right/Brazil/West',1520),('right/Canada/Atlantic',1523),('right/Canada/Central',1524),('right/Canada/East-Saskatchewan',1525),('right/Canada/Eastern',1526),('right/Canada/Mountain',1527),('right/Canada/Newfoundland',1528),('right/Canada/Pacific',1529),('right/Canada/Saskatchewan',1530),('right/Canada/Yukon',1531),('right/CET',1521),('right/Chile/Continental',1532),('right/Chile/EasterIsland',1533),('right/CST6CDT',1522),('right/Cuba',1534),('right/EET',1535),('right/Egypt',1538),('right/Eire',1539),('right/EST',1536),('right/EST5EDT',1537),('right/Etc/GMT',1540),('right/Etc/GMT+0',1541),('right/Etc/GMT+1',1542),('right/Etc/GMT+10',1543),('right/Etc/GMT+11',1544),('right/Etc/GMT+12',1545),('right/Etc/GMT+2',1546),('right/Etc/GMT+3',1547),('right/Etc/GMT+4',1548),('right/Etc/GMT+5',1549),('right/Etc/GMT+6',1550),('right/Etc/GMT+7',1551),('right/Etc/GMT+8',1552),('right/Etc/GMT+9',1553),('right/Etc/GMT-0',1554),('right/Etc/GMT-1',1555),('right/Etc/GMT-10',1556),('right/Etc/GMT-11',1557),('right/Etc/GMT-12',1558),('right/Etc/GMT-13',1559),('right/Etc/GMT-14',1560),('right/Etc/GMT-2',1561),('right/Etc/GMT-3',1562),('right/Etc/GMT-4',1563),('right/Etc/GMT-5',1564),('right/Etc/GMT-6',1565),('right/Etc/GMT-7',1566),('right/Etc/GMT-8',1567),('right/Etc/GMT-9',1568),('right/Etc/GMT0',1569),('right/Etc/Greenwich',1570),('right/Etc/UCT',1571),('right/Etc/Universal',1573),('right/Etc/UTC',1572),('right/Etc/Zulu',1574),('right/Europe/Amsterdam',1575),('right/Europe/Andorra',1576),('right/Europe/Athens',1577),('right/Europe/Belfast',1578),('right/Europe/Belgrade',1579),('right/Europe/Berlin',1580),('right/Europe/Bratislava',1581),('right/Europe/Brussels',1582),('right/Europe/Bucharest',1583),('right/Europe/Budapest',1584),('right/Europe/Busingen',1585),('right/Europe/Chisinau',1586),('right/Europe/Copenhagen',1587),('right/Europe/Dublin',1588),('right/Europe/Gibraltar',1589),('right/Europe/Guernsey',1590),('right/Europe/Helsinki',1591),('right/Europe/Isle_of_Man',1592),('right/Europe/Istanbul',1593),('right/Europe/Jersey',1594),('right/Europe/Kaliningrad',1595),('right/Europe/Kiev',1596),('right/Europe/Lisbon',1597),('right/Europe/Ljubljana',1598),('right/Europe/London',1599),('right/Europe/Luxembourg',1600),('right/Europe/Madrid',1601),('right/Europe/Malta',1602),('right/Europe/Mariehamn',1603),('right/Europe/Minsk',1604),('right/Europe/Monaco',1605),('right/Europe/Moscow',1606),('right/Europe/Nicosia',1607),('right/Europe/Oslo',1608),('right/Europe/Paris',1609),('right/Europe/Podgorica',1610),('right/Europe/Prague',1611),('right/Europe/Riga',1612),('right/Europe/Rome',1613),('right/Europe/Samara',1614),('right/Europe/San_Marino',1615),('right/Europe/Sarajevo',1616),('right/Europe/Simferopol',1617),('right/Europe/Skopje',1618),('right/Europe/Sofia',1619),('right/Europe/Stockholm',1620),('right/Europe/Tallinn',1621),('right/Europe/Tirane',1622),('right/Europe/Tiraspol',1623),('right/Europe/Uzhgorod',1624),('right/Europe/Vaduz',1625),('right/Europe/Vatican',1626),('right/Europe/Vienna',1627),('right/Europe/Vilnius',1628),('right/Europe/Volgograd',1629),('right/Europe/Warsaw',1630),('right/Europe/Zagreb',1631),('right/Europe/Zaporozhye',1632),('right/Europe/Zurich',1633),('right/GB',1634),('right/GB-Eire',1635),('right/GMT',1636),('right/GMT+0',1637),('right/GMT-0',1638),('right/GMT0',1639),('right/Greenwich',1640),('right/Hongkong',1642),('right/HST',1641),('right/Iceland',1643),('right/Indian/Antananarivo',1644),('right/Indian/Chagos',1645),('right/Indian/Christmas',1646),('right/Indian/Cocos',1647),('right/Indian/Comoro',1648),('right/Indian/Kerguelen',1649),('right/Indian/Mahe',1650),('right/Indian/Maldives',1651),('right/Indian/Mauritius',1652),('right/Indian/Mayotte',1653),('right/Indian/Reunion',1654),('right/Iran',1655),('right/Israel',1656),('right/Jamaica',1657),('right/Japan',1658),('right/Kwajalein',1659),('right/Libya',1660),('right/MET',1661),('right/Mexico/BajaNorte',1664),('right/Mexico/BajaSur',1665),('right/Mexico/General',1666),('right/MST',1662),('right/MST7MDT',1663),('right/Navajo',1669),('right/NZ',1667),('right/NZ-CHAT',1668),('right/Pacific/Apia',1672),('right/Pacific/Auckland',1673),('right/Pacific/Chatham',1674),('right/Pacific/Chuuk',1675),('right/Pacific/Easter',1676),('right/Pacific/Efate',1677),('right/Pacific/Enderbury',1678),('right/Pacific/Fakaofo',1679),('right/Pacific/Fiji',1680),('right/Pacific/Funafuti',1681),('right/Pacific/Galapagos',1682),('right/Pacific/Gambier',1683),('right/Pacific/Guadalcanal',1684),('right/Pacific/Guam',1685),('right/Pacific/Honolulu',1686),('right/Pacific/Johnston',1687),('right/Pacific/Kiritimati',1688),('right/Pacific/Kosrae',1689),('right/Pacific/Kwajalein',1690),('right/Pacific/Majuro',1691),('right/Pacific/Marquesas',1692),('right/Pacific/Midway',1693),('right/Pacific/Nauru',1694),('right/Pacific/Niue',1695),('right/Pacific/Norfolk',1696),('right/Pacific/Noumea',1697),('right/Pacific/Pago_Pago',1698),('right/Pacific/Palau',1699),('right/Pacific/Pitcairn',1700),('right/Pacific/Pohnpei',1701),('right/Pacific/Ponape',1702),('right/Pacific/Port_Moresby',1703),('right/Pacific/Rarotonga',1704),('right/Pacific/Saipan',1705),('right/Pacific/Samoa',1706),('right/Pacific/Tahiti',1707),('right/Pacific/Tarawa',1708),('right/Pacific/Tongatapu',1709),('right/Pacific/Truk',1710),('right/Pacific/Wake',1711),('right/Pacific/Wallis',1712),('right/Pacific/Yap',1713),('right/Poland',1714),('right/Portugal',1715),('right/PRC',1670),('right/PST8PDT',1671),('right/ROC',1716),('right/ROK',1717),('right/Singapore',1718),('right/Turkey',1719),('right/UCT',1720),('right/Universal',1735),('right/US/Alaska',1721),('right/US/Aleutian',1722),('right/US/Arizona',1723),('right/US/Central',1724),('right/US/East-Indiana',1725),('right/US/Eastern',1726),('right/US/Hawaii',1727),('right/US/Indiana-Starke',1728),('right/US/Michigan',1729),('right/US/Mountain',1730),('right/US/Pacific',1731),('right/US/Pacific-New',1732),('right/US/Samoa',1733),('right/UTC',1734),('right/W-SU',1736),('right/WET',1737),('right/Zulu',1738),('ROC',557),('ROK',558),('Singapore',559),('Turkey',560),('UCT',561),('Universal',576),('US/Alaska',562),('US/Aleutian',563),('US/Arizona',564),('US/Central',565),('US/East-Indiana',566),('US/Eastern',567),('US/Hawaii',568),('US/Indiana-Starke',569),('US/Michigan',570),('US/Mountain',571),('US/Pacific',572),('US/Pacific-New',573),('US/Samoa',574),('UTC',575),('W-SU',577),('WET',578),('Zulu',579);
/*!40000 ALTER TABLE `time_zone_name` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `reg_code` char(32) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `skype` varchar(255) DEFAULT NULL,
  `gpg_pub_key` text,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `reg_code_UNIQUE` (`reg_code`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (0,'dba@twindb.com',NULL,'Meta','TwinDB',NULL,NULL,NULL,NULL),(1,'aleks@twindb.com','$6$54afbf3a10847800$q1PNj3MQminukMeaDzmkee3n.k01XJ1Z1RakChNEY01OaHgnqkUPEdzReZdYU/PGeCINGengixuLCOpuwo4s.0','a','1','1234567890','','','-----BEGIN PGP PUBLIC KEY BLOCK-----\nVersion: GnuPG v2.0.14 (GNU/Linux)\n\nmQENBFO0wrsBCACbKuiN06uZg21DX0Mi/cbVH3CxcrXEJoDnVFPbW71ZL8k7jlIs\nLkFymCtkjLfVFNccL3SQlqSLdiks5jeloLnZrFw93h7W4e/YbJ5HdfNX7YNgfiTc\nCrtZ5jLPdaSXMIg8OIAQH+/NAMx7e7e4AtyYOxa4aCeq1EowQvsRpCMXfPk2+YGy\nnqsbv2/ei64RFqx/+ffEl9KpTM/ovzpEZdKFqpFIq1O8xv4sdZGUHq/v5tHWXl1i\nfTZBhBOW/1rAPZ96LuqIgg0jz0Cx/DJwjq+MHUuC32neeG5O8cc0IWVZYDV48OED\nguEujOuUOFL390NdVrfVAFm//je3H6c+Y/1bABEBAAG0d0JhY2t1cCBTZXJ2ZXIg\naWQgMWQ0NzIyNDAtOThiNy00NGI4LTg4ZjUtNzk3YzA5OTEyMTE4IChObyBwYXNz\ncGhyYXNlKSA8MWQ0NzIyNDAtOThiNy00NGI4LTg4ZjUtNzk3YzA5OTEyMTE4QHR3\naW5kYi5jb20+iQE4BBMBAgAiBQJTtMK7AhsvBgsJCAcDAgYVCAIJCgsEFgIDAQIe\nAQIXgAAKCRAQP7l2VC3QNJOVB/9WeuxJ1eMbpFKKKraCIT9cKBIuqx7mZGVFpsIE\nBBxzv/dlg6T3YheK6C6wNKypqpNjKsxdevxjEptByhSWQWFbcbO1oNAQRm76A17F\nqMeE1EbYfMQJ69fTT15N7TE0sRVjsTtaUEoiN+1TVKwlWklyvroMTA65Wn02RvGe\n9fF636P/JYDxGq7lgP1VapbFlts/RaE0nvVHmTDApfi4OkqyEwgBJ5M8kbuF2Sv7\nrFHDsEZJHEtoJj2yrLWYLgpV6CUqcVH01OYjp03MYbxF61JvaryJ+IP5Tvq06BwU\nR6Eb0cA2sY7bbi9aYe9qL8Zino+jX7MgBht29/oJmjBwAGePuQENBFO0wrsBCACj\n33J1wMkWg2H7WNRCetA2nLWQecSR9Ut51gjzdHnvxcIg5rNLU9c/nC2ts5jrXgay\nhWJGGnVkjxWz/YS0rhCSho9Q36ZuTmrLTpgOtYbqxsRvTAXrwIWuhX3uBCA6UCj9\no/3u01ePxfsftmqTediadPP6h51AEZzakY4ElPu0DtRTc5wCqcaJCdLfm1HhTFGa\nK6s15g6U03aGOetenG2eMnvaV/bJKfD34OdGOxFFPMRr0zF6Rhm4PWwl4ZiZwphL\n4dtaZEcKisE0noD7VIROoKo2U58I6/3V9f5H2omDC2lZd1Z5I3Zl2AZTnpqJ296/\nLZKWqSgK1OH54r8owat9ABEBAAGJAj4EGAECAAkFAlO0wrsCGy4BKQkQED+5dlQt\n0DTAXSAEGQECAAYFAlO0wrsACgkQgBZQJ74ouTOD8Qf/bFexVPlbKcrkbE4PiH7T\nL3dYaS3o/pQCvd6xHPDU6a8qhDjvVkqRgMMuEzQYGi8xb8OIslwlTOI+suVP6H07\nhys3FPp3/XHxNp3kx0RpJr5imTgHyD47Kp6C6Ie78utyB7RVYfIZtRS7dzUV7HCL\nPd3nOvEzP91ZzBLEH0AP7DuEAlMlWwSGBTibwtsZU1WBvykmRZA4V9cQ6pT0ZMgn\nDc3ojN92JQVi1AXehA238hyFOToP6yXrRVXktLujx+PWaExsk8u5EPDHTqe8Ut3a\nEFmJ3XfLaPzR0oXtLcLsYa6OvJzP6nwTIFNV4621M0qujoBCwP4pQeYP4w3kW2Zh\nSYl0B/0bKwK7LySnJgmUN97r0+3ZbmO/FcM54OImnE1kc7Dx8b1TjhEvAFfhqAdl\na/pt811ixARY/2vNfvel1Kh5VEJQoRlRshr+vgQ2eiDvTqbES1HMD1YbXD9IeAF+\n5e87ST/pLOaHJw4AI7kwrhR30T5kB0flW6NyhFZ0rR+nbSg0mLtj42T5s7RXCOgI\nkEwZ4kbErJaa4BcIqiCZ6Muus7FMP5mx/ZZiTBQFtRPX3nIJ4MLMwm3BF7c+nCNg\nf/DdvWqHHc3+ORcuBpu4Itb4e+dECUDL27Sy4takoUGlHuB8wCHChlpdq6AR6EA/\n33OEeuTDRwq13o3/1zaw5ytFlxdj\n=+wLc\n-----END PGP PUBLIC KEY BLOCK-----');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `volume`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `volume` (
  `volume_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `storage_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT 'Default storage',
  `username` varchar(45) NOT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT '2147483648',
  `used_size` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`volume_id`),
  KEY `fk_volume_storage_idx` (`storage_id`),
  KEY `fk_volume_user_idx` (`user_id`),
  KEY `idx_user_id_priority` (`user_id`,`priority`),
  CONSTRAINT `fk_volume_storage` FOREIGN KEY (`storage_id`) REFERENCES `storage` (`storage_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_volume_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `volume` WRITE;
/*!40000 ALTER TABLE `volume` DISABLE KEYS */;
INSERT INTO `volume` VALUES (5,2,1,0,'Default storage','user_id_1',2147483648,0);
/*!40000 ALTER TABLE `volume` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

