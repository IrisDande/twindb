SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

UPDATE `schedule` SET `ntimes` = 1 WHERE `ntimes` IS NULL;
ALTER TABLE `schedule` 
DROP COLUMN `period`,
DROP COLUMN `run_once_day`,
DROP COLUMN `day`,
CHANGE COLUMN `frequency_unit` `frequency_unit` ENUM('Hour','Day','Week') NOT NULL DEFAULT 'Hour' AFTER `ntimes`,
CHANGE COLUMN `ntimes` `ntimes` TINYINT(4) NOT NULL DEFAULT 1 COMMENT 'How many times per frequency unit backups' ;

INSERT INTO db_version (version) VALUES(7);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
