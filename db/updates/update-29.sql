-- MySQL Workbench Synchronization
-- Generated: 2014-12-31 17:53
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `server` 
ADD COLUMN `Reload_priv` ENUM('N','Y') NULL DEFAULT NULL COMMENT 'Agent Privilege granted' AFTER `cluster_id`,
ADD COLUMN `Lock_tables_priv` ENUM('N','Y') NULL DEFAULT NULL COMMENT 'Agent Privilege granted' AFTER `Reload_priv`,
ADD COLUMN `Repl_client_priv` ENUM('N','Y') NULL DEFAULT NULL COMMENT 'Agent Privilege granted' AFTER `Lock_tables_priv`,
ADD COLUMN `Super_priv` ENUM('N','Y') NULL DEFAULT NULL COMMENT 'Agent Privilege granted' AFTER `Repl_client_priv`,
ADD COLUMN `Create_tablespace_priv` ENUM('N','Y') NULL DEFAULT NULL COMMENT 'Agent Privilege granted' AFTER `Super_priv`;

INSERT INTO db_version (version) VALUES(29);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
