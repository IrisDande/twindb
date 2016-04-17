-- MySQL Workbench Synchronization
-- Generated: 2014-11-09 18:13
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `job` 
DROP COLUMN `restore_backup_copy`,
DROP COLUMN `restore_dir`,
DROP COLUMN `full_backup`,
DROP COLUMN `return_code`,
DROP INDEX `idx_server_full_date` ,
ADD INDEX `idx_server_full_date` (`server_id` ASC, `start_scheduled` ASC);

INSERT INTO db_version (version) VALUES(11);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
