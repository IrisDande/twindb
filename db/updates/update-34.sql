ALTER TABLE `job` MODIFY `type` enum('backup','restore','send_key') NOT NULL DEFAULT 'backup';
ALTER TABLE `server` ADD `enc_private_key` text AFTER `enc_public_key`;
INSERT INTO db_version (version) VALUES(34);
