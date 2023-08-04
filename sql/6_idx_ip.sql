ALTER TABLE `users` ADD INDEX `idx_ip` (`last_ip`(12));
ALTER TABLE `conversation_records` ADD `prev` INT UNSIGNED NOT NULL AFTER `created_at`;
ALTER TABLE `conversation_records` CHANGE `prev` `prev` INT(10) UNSIGNED NULL DEFAULT NULL;