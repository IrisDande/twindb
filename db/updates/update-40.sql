-- MySQL Workbench Synchronization
-- Generated: 2015-07-10 18:16
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `server` 
ADD COLUMN `registration_confirmed` ENUM('Yes', 'No') NOT NULL DEFAULT 'No' COMMENT 'Correct registration is confirmed by agent' AFTER `role`;

UPDATE `server` SET `registration_confirmed` = 'Yes';

INSERT INTO db_version (version) VALUES(40);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
