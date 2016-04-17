-- MySQL Workbench Synchronization
-- Generated: 2015-03-16 12:22
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `notification` (
  `notification_id` INT(11) NOT NULL AUTO_INCREMENT,
  `check_id` VARCHAR(255) NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `message` VARCHAR(255) NULL DEFAULT NULL,
  `acknowledged` ENUM('Yes','No') NOT NULL DEFAULT 'No',
  `resolved` ENUM('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`notification_id`, `check_id`),
  INDEX `fk_notification_check_idx` (`check_id` ASC),
  INDEX `fk_notification_user_idx` (`user_id` ASC),
  CONSTRAINT `fk_notification_check`
    FOREIGN KEY (`check_id`)
    REFERENCES `twindb`.`check` (`check_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_notification_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `twindb`.`user` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `check` (
  `check_id` VARCHAR(255) NOT NULL,
  `problem_description` TEXT NULL DEFAULT NULL,
  `remedy_description` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`check_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;

INSERT INTO `twindb`.`check` (`check_id`, `problem_description`, `remedy_description`) VALUES ('public_gpg_key_not_avaialable', 'You haven\'t uploaded your public GPG key', 'Please go to Profile -> Security and enter your public GPG key');
INSERT INTO `twindb`.`check` (`check_id`, `problem_description`, `remedy_description`) VALUES ('backup_job_failed', 'Backup job failed', 'Please check error log of the failed job to find out why the job failed. File a bug if necessary on https://bugs.launchpad.net/twindb/+filebug');
INSERT INTO `twindb`.`check` (`check_id`, `problem_description`, `remedy_description`) VALUES ('restore_job_failed', 'Restore job failed', 'Please check error log of the failed job to find out why the job failed. File a bug if necessary on https://bugs.launchpad.net/twindb/+filebug');
INSERT INTO `twindb`.`check` (`check_id`, `problem_description`, `remedy_description`) VALUES ('storage_ran_out_of_space', 'Storage space is almost all used', 'Please buy additional storage or change retention policy, so backup copies need less space');

INSERT INTO db_version (version) VALUES(35);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
