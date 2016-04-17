-- MySQL Workbench Synchronization
-- Generated: 2014-11-30 12:32
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `volume_usage_history` (
  `volume_usage_history_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `volume_id` INT(10) UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `used` BIGINT(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`volume_usage_history_id`),
  INDEX `fk_vol_usage_history_volume_idx` (`volume_id` ASC),
  CONSTRAINT `fk_vol_usage_history_volume`
    FOREIGN KEY (`volume_id`)
    REFERENCES `volume` (`volume_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;

INSERT INTO db_version (version) VALUES(21);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
