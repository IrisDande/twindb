-- MySQL Workbench Synchronization
-- Generated: 2014-11-20 08:29
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `server` 
DROP FOREIGN KEY `fk_server_user`;

ALTER TABLE `server` 
ADD COLUMN `cluster_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `config_id`,
ADD INDEX `fk_server_cluster_idx` (`cluster_id` ASC);

CREATE TABLE IF NOT EXISTS `cluster` (
  `cluster_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`cluster_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;

ALTER TABLE `server` 
ADD CONSTRAINT `fk_server_config`
  FOREIGN KEY (`config_id`)
  REFERENCES `config` (`config_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_server_cluster`
  FOREIGN KEY (`cluster_id`)
  REFERENCES `cluster` (`cluster_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

INSERT INTO db_version (version) VALUES(16);
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
