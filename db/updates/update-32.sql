-- MySQL Workbench Synchronization
-- Generated: 2015-01-12 15:19
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

UPDATE `backup_copy` SET `backup_type` = 'Hourly' WHERE `backup_type` IS NULL;
ALTER TABLE `backup_copy` MODIFY `backup_type` SET('Hourly','Daily','Weekly','Monthly','Quarterly','Yearly') NOT NULL DEFAULT 'Hourly';

INSERT INTO db_version (version) VALUES(32);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
