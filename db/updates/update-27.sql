-- MySQL Workbench Synchronization
-- Generated: 2014-12-11 16:57
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `backup_copy` 
DROP FOREIGN KEY `fk_backup_copy_job_id`;

ALTER TABLE `storage` 
DROP FOREIGN KEY `fk_storage_user`;

ALTER TABLE `storage` 
CHANGE COLUMN `approved` `registered` ENUM('yes', 'no') NOT NULL DEFAULT 'no' ;

ALTER TABLE `backup_copy` 
ADD CONSTRAINT `fk_backup_copy_job_id`
  FOREIGN KEY (`job_id`)
  REFERENCES `job` (`job_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `storage` 
ADD CONSTRAINT `fk_storage_user`
  FOREIGN KEY (`user_id`)
  REFERENCES `user` (`user_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

INSERT INTO db_version (version) VALUES(27);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
