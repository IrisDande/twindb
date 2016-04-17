-- MySQL Workbench Synchronization
-- Generated: 2015-10-18 16:02
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `twindb`.`server` 
CHANGE COLUMN `registration_confirmed` `registration_confirmed` ENUM('Yes', 'No') NOT NULL DEFAULT 'Yes' COMMENT 'Correct registration is confirmed by agent' ;

INSERT INTO db_version (version) VALUES(41);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
