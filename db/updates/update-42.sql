-- MySQL Workbench Synchronization
-- Generated: 2015-11-08 19:59
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `twindb`.`volume` 
CHANGE COLUMN `username` `username` VARCHAR(45) NULL DEFAULT NULL ,
CHANGE COLUMN `size` `size` BIGINT(20) UNSIGNED NULL DEFAULT '2147483648' ;

ALTER TABLE `twindb`.`storage` 
CHANGE COLUMN `size` `size` BIGINT(19) UNSIGNED NULL DEFAULT NULL ,
CHANGE COLUMN `ip` `ip` VARCHAR(16) NULL DEFAULT NULL ;

INSERT INTO db_version (version) VALUES(42);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
