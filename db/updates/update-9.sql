SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `menu_item` 
ADD COLUMN `order` TINYINT(4) NOT NULL DEFAULT 0 AFTER `show`;
BEGIN;
UPDATE `menu_item` SET `order` = 1 WHERE `text` = 'Status';
UPDATE `menu_item` SET `order` = 2 WHERE `text` = 'Profile';
UPDATE `menu_item` SET `order` = 3 WHERE `text` = 'Server farm';
UPDATE `menu_item` SET `order` = 4 WHERE `text` = 'Backup configuration';
UPDATE `menu_item` SET `order` = 5 WHERE `text` = 'Schedule';
UPDATE `menu_item` SET `order` = 6 WHERE `text` = 'Retention policy';
UPDATE `menu_item` SET `order` = 7 WHERE `text` = 'Storage';

INSERT INTO db_version (version) VALUES(9);
COMMIT;
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
