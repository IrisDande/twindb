-- MySQL Workbench Synchronization
-- Generated: 2014-11-20 08:31
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES,NO_AUTO_VALUE_ON_ZERO';

UPDATE `storage` SET `used_size` = 0 WHERE `used_size` IS NULL;

ALTER TABLE `storage` MODIFY `size` bigint(19) unsigned NOT NULL, 
    MODIFY `used_size` bigint(19) unsigned NOT NULL DEFAULT '0';

INSERT INTO db_version (version) VALUES(19);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
