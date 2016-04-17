SET FOREIGN_KEY_CHECKS=0;

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

CREATE TABLE `cg_treepath` (
  `ancestor` int(10) unsigned NOT NULL,
  `descendant` int(10) unsigned NOT NULL,
  `length` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `fk_treepath_server_id_1_idx` (`ancestor`),
  KEY `fk_treepath_server_id_2_idx` (`descendant`),
  CONSTRAINT `fk_treepath_server_id_1` FOREIGN KEY (`ancestor`) REFERENCES `cgnode` (`cluster_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_treepath_server_id_2` FOREIGN KEY (`descendant`) REFERENCES `cgnode` (`cluster_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `config` (
  `config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `name` varchar(45) NOT NULL DEFAULT 'Default',
  `schedule_id` int(10) unsigned NOT NULL,
  `retention_policy_id` int(10) unsigned NOT NULL,
  `volume_id` int(10) unsigned,
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `db_version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `version` int(10) unsigned DEFAULT NULL,
  `deployed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

CREATE TABLE `menu_item` (
  `menuitem_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `text` text CHARACTER SET utf8,
  `iconCls` varchar(255) DEFAULT NULL,
  `className` varchar(255) DEFAULT NULL,
  `show` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`menuitem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `menu_item_tree` (
  `ancestor` int(10) unsigned NOT NULL,
  `descendant` int(10) unsigned NOT NULL,
  `depth` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ancestor`,`descendant`),
  KEY `fk_menu_item_tree_menu_item_2_idx` (`descendant`),
  CONSTRAINT `fk_menu_item_tree_menu_item_1` FOREIGN KEY (`ancestor`) REFERENCES `menu_item` (`menuitem_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_menu_item_tree_menu_item_2` FOREIGN KEY (`descendant`) REFERENCES `menu_item` (`menuitem_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `package` (
  `package_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `storage` bigint(19) unsigned NOT NULL DEFAULT '53687091200',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '10.00',
  PRIMARY KEY (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

CREATE TABLE `server_ip` (
  `server_ip_id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` char(36) NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  PRIMARY KEY (`server_ip_id`),
  KEY `fk_server_idx` (`server_id`),
  CONSTRAINT `fk_server` FOREIGN KEY (`server_id`) REFERENCES `server` (`server_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `storage` (
  `storage_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT 'Default storage',
  `params` text,
  `type` enum('ssh','s3') NOT NULL DEFAULT 'ssh',
  `size` bigint(19) unsigned DEFAULT NULL,
  `used_size` bigint(19) unsigned DEFAULT NULL,
  PRIMARY KEY (`storage_id`),
  KEY `fk_storage_user_idx` (`user_id`),
  CONSTRAINT `fk_storage_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `time_zone_name` (
  `Name` char(64) NOT NULL,
  `Time_zone_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Time zone names';

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
