-- MySQL Workbench Synchronization
-- Generated: 2014-11-20 08:31
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES,NO_AUTO_VALUE_ON_ZERO';

BEGIN;

DELETE FROM `storage` WHERE `storage_id` = 1;

INSERT INTO `user` (`user_id`, `email`, `password`, `first_name`, `last_name`, `reg_code`, `phone`, `skype`, `gpg_pub_key`)
VALUES (0,'meta@twindb.com', NULL, NULL, NULL, MD5(RAND()), NULL, NULL, NULL);

INSERT INTO db_version (version) VALUES(18);

COMMIT;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
