SET FOREIGN_KEY_CHECKS=0;

SET @OLD_SQL_MODE=@@SQL_MODE;
SET SESSION SQL_MODE=CONCAT(@OLD_SQL_MODE, ',', 'NO_AUTO_VALUE_ON_ZERO');

BEGIN;

INSERT INTO `db_version` (version, deployed) VALUES (0,NOW());

INSERT INTO `config` (`config_id`, `user_id`, `priority`, `name`, `schedule_id`, `retention_policy_id`, `volume_id`, `mysql_user`, `mysql_password`) 
    VALUES (1,1,0,'Default',1,1,NULL,'twindb_agent', md5(rand()));

INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (0,'Root',NULL,NULL,'Y');
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (1,'Status','report',NULL,'Y');
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (2,'Dashboard','map','panel','Y');
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (3,'Profile','user_suit',NULL,'Y');
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (4,'General','book_addresses','generalprofile','Y');
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (5,'Security','shield','securityprofile','Y');
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (6,'Notifications','bell','panel','N');
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (7,'Server farm','server_database',NULL,'Y');
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (8,'Schedule','calendar',NULL,'Y');
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (9,'Retention policy','page_white_stack',NULL,'Y');
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (10,'Storage','drive',NULL,'Y');
INSERT INTO `menu_item` (`menuitem_id`, `text`, `iconCls`, `className`, `show`) VALUES (11,'My subscriptions','coins','ordergrid','Y');

INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,0,0);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,1,1);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,2,2);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,3,1);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,4,2);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,5,2);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,6,2);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,7,1);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,8,1);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,9,1);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,10,1);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (0,11,2);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (1,1,0);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (1,2,1);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (2,2,0);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (3,3,0);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (3,4,1);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (3,5,1);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (3,6,1);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (3,11,1);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (4,4,0);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (5,5,0);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (6,6,0);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (7,7,0);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (8,8,0);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (9,9,0);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (10,10,0);
INSERT INTO `menu_item_tree` (`ancestor`, `descendant`, `depth`) VALUES (11,11,0);

INSERT INTO `package` (`package_id`, `name`, `storage`, `price`) VALUES (2,'50G',53687091200,10.00);
INSERT INTO `package` (`package_id`, `name`, `storage`, `price`) VALUES (3,'100G',107374182400,10.00);
INSERT INTO `package` (`package_id`, `name`, `storage`, `price`) VALUES (4,'500G',536870912000,10.00);
INSERT INTO `package` (`package_id`, `name`, `storage`, `price`) VALUES (5,'1T',1073741824000,10.00);

INSERT INTO `retention_policy` (`retention_policy_id`, `user_id`, `priority`, `name`, `Hourly`, `Daily`, `Weekly`, `Monthly`, `Quarterly`, `Yearly`) 
    VALUES (1,1,0,'Default retention policy',0,7,0,12,0,3);

INSERT INTO `schedule` (`schedule_id`, `user_id`, `priority`, `name`, `start_time`, `day`, `run_once_day`, `period`, `ntimes`, `full_copy`, `time_zone`) 
    VALUES (1,1,0,'Default schedule','00:00:00','Mon,Tue,Wed,Thu,Fri,Sat,Sun','Y',NULL,NULL,'Weekly','UTC');

INSERT INTO `storage` (`storage_id`, `user_id`, `name`, `params`, `type`, `size`, `used_size`) 
    VALUES (1,NULL,'Default storage',NULL,'ssh',NULL,NULL);

INSERT INTO `user` (`user_id`, `email`, `password`, `first_name`, `last_name`, `reg_code`, `phone`, `skype`, `gpg_pub_key`) 
VALUES (1,'dba@twindb.com', NULL, NULL, NULL, MD5(RAND()), NULL, NULL, NULL);

COMMIT;
