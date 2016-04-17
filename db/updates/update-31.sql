-- MySQL Workbench Synchronization
-- Generated: 2015-01-12 15:19
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Aleksandr Kuzminsky

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP PROCEDURE IF EXISTS tree_naive_to_closure;

DELIMITER |

CREATE PROCEDURE tree_naive_to_closure()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE id BIGINT UNSIGNED DEFAULT 0;
    DECLARE anc BIGINT UNSIGNED DEFAULT 0;

    DECLARE cur CURSOR FOR SELECT `backup_copy_id`, `ancestor` FROM `backup_copy` ORDER BY `backup_copy_id`;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    WHILE NOT done DO
        FETCH cur INTO id, anc;
        INSERT IGNORE INTO `backup_copy_tree`
            SELECT `ancestor`, id, `length` + 1
            FROM `backup_copy_tree`
            WHERE `descendant` = anc
            UNION ALL
            SELECT id, id, 0;
    END WHILE;
    
    CLOSE cur;
END
|
DELIMITER ;

CALL tree_naive_to_closure();
DROP PROCEDURE IF EXISTS tree_naive_to_closure;
DELETE FROM backup_copy_tree WHERE ancestor = 0 OR descendant = 0;

INSERT INTO db_version (version) VALUES(31);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
