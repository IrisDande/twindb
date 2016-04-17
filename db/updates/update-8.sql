SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

BEGIN;
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (12,'Backup configuration','cog',NULL,'Y');

INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,12,1);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (12,12,0);

INSERT INTO db_version (version) VALUES(8);
COMMIT;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
