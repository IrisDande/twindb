CREATE TABLE IF NOT EXISTS `password_reset_code` (
    `password_reset_code_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(10) UNSIGNED NOT NULL,
    `code` CHAR(32) NULL DEFAULT NULL,
    `expires` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`password_reset_code_id`),
    INDEX `fk_prc_user_user_id_idx` (`user_id` ASC),
    CONSTRAINT `fk_prc_user_user_id`
        FOREIGN KEY (`user_id`)
        REFERENCES `user` (`user_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;
INSERT INTO db_version (version) VALUES(1);
