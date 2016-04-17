SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `server` 
ADD COLUMN `mysql_server_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'MySQL server_id variable' AFTER `enc_public_key`,
ADD COLUMN `mysql_master_server_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `mysql_server_id`,
ADD COLUMN `mysql_master_host` VARCHAR(255) NULL DEFAULT NULL AFTER `mysql_master_server_id`,
ADD COLUMN `mysql_seconds_behind_master` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `mysql_master_host`,
ADD COLUMN `mysql_slave_io_running` ENUM('Yes', 'No') NULL DEFAULT NULL AFTER `mysql_seconds_behind_master`,
ADD COLUMN `mysql_slave_sql_running` ENUM('Yes', 'No') NULL DEFAULT NULL AFTER `mysql_slave_io_running`;

CREATE TABLE IF NOT EXISTS `server_attribute` (
  `server_attribute_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `server_id` CHAR(36) NOT NULL,
  `attribute` VARCHAR(255) NOT NULL,
  `value` VARCHAR(255) NULL DEFAULT NULL,
  `default_value` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`server_attribute_id`),
  UNIQUE INDEX `server_attribute_idx` (`server_id` ASC, `attribute` ASC),
  CONSTRAINT `fk_server_attribute_server`
    FOREIGN KEY (`server_id`)
    REFERENCES `server` (`server_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `server_tag` (
  `server_tag_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `server_id` CHAR(36) NOT NULL,
  `tag` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`server_tag_id`),
  INDEX `server_id_idx` (`server_id` ASC),
  CONSTRAINT `fk_server_tag_server`
    FOREIGN KEY (`server_id`)
    REFERENCES `server` (`server_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `server_filter` (
  `server_filter_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL DEFAULT 'Default',
  `config_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`server_filter_id`),
  INDEX `fk_server_filter_config_idx` (`config_id` ASC),
  CONSTRAINT `fk_server_filter_config`
    FOREIGN KEY (`config_id`)
    REFERENCES `config` (`config_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `server_filter_attribute_entry` (
  `server_filter_attribute_entry_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `server_filter_id` INT(10) UNSIGNED NOT NULL,
  `attribute` VARCHAR(255) NOT NULL,
  `value` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`server_filter_attribute_entry_id`),
  INDEX `fk_server_filter_attribute_entry_server_filter_idx` (`server_filter_id` ASC),
  CONSTRAINT `fk_server_filter_attribute_entry_server_filter`
    FOREIGN KEY (`server_filter_id`)
    REFERENCES `server_filter` (`server_filter_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `server_filter_tag_entry` (
  `server_filter_tag_entry_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `server_filter_id` INT(10) UNSIGNED NOT NULL,
  `tag` VARCHAR(255) NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`server_filter_tag_entry_id`),
  INDEX `fk_server_filter_tag_entry_server_filter_idx` (`server_filter_id` ASC),
  CONSTRAINT `fk_server_filter_tag_entry_server_filter`
    FOREIGN KEY (`server_filter_id`)
    REFERENCES `server_filter` (`server_filter_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;

ALTER TABLE `server` 
CHANGE COLUMN `cluster_id` `user_id` INT(10) UNSIGNED NOT NULL ,
ADD INDEX `fk_server_user_idx` (`user_id` ASC);

ALTER TABLE `server` 
ADD CONSTRAINT `fk_server_user`
  FOREIGN KEY (`user_id`)
  REFERENCES `user` (`user_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;




ALTER TABLE `server` 
DROP FOREIGN KEY `fk_server_user`;

ALTER TABLE `server` 
ADD CONSTRAINT `fk_server_user`
  FOREIGN KEY (`user_id`)
  REFERENCES `user` (`user_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `server_attribute` 
DROP COLUMN `default_value`,
CHANGE COLUMN `attribute` `attribute_id` INT(10) UNSIGNED NOT NULL ,
ADD INDEX `fk_server_attribute_attribute_idx` (`attribute_id` ASC);

CREATE TABLE IF NOT EXISTS `attribute` (
	  `attribute_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	  `user_id` INT(10) UNSIGNED NOT NULL,
	  `attribute` VARCHAR(255) NOT NULL,
	  `default_value` VARCHAR(255) NULL DEFAULT NULL,
	  PRIMARY KEY (`attribute_id`),
	  INDEX `fk_attribute_user_idx` (`user_id` ASC),
	  CONSTRAINT `fk_attribute_user`
	    FOREIGN KEY (`user_id`)
	    REFERENCES `user` (`user_id`)
	    ON DELETE CASCADE
	    ON UPDATE CASCADE)
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = latin1
	COLLATE = latin1_swedish_ci;

ALTER TABLE `server_attribute` 
ADD CONSTRAINT `fk_server_attribute_attribute`
  FOREIGN KEY (`attribute_id`)
  REFERENCES `attribute` (`attribute_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `server_attribute` 
DROP COLUMN `server_attribute_id`,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`server_id`, `attribute_id`);


INSERT INTO db_version (version) VALUES(4);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
