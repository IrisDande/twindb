-- MySQL Workbench Synchronization
-- Generated: 2015-04-25 15:58
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `twindb`.`config` 
DROP FOREIGN KEY `fk_config_volume`;

ALTER TABLE `twindb`.`config` 
ADD CONSTRAINT `fk_config_volume`
  FOREIGN KEY (`volume_id`)
  REFERENCES `twindb`.`volume` (`volume_id`)
  ON DELETE SET NULL
  ON UPDATE SET NULL;

INSERT INTO db_version (version) VALUES(37);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;


