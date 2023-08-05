ALTER TABLE `users` ADD INDEX `idx_ip` (`last_ip`(12));
ALTER TABLE `conversation_records` ADD `prev` INT UNSIGNED NOT NULL AFTER `created_at`;
ALTER TABLE `conversation_records` CHANGE `prev` `prev` INT(10) UNSIGNED NULL DEFAULT NULL;

CREATE TABLE invite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inviter_id INT NOT NULL,
    invitee_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    points INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE `invite` ADD UNIQUE `idx_uniq_ip` (`ip_address`(15));