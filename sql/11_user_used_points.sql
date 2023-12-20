ALTER TABLE users
ADD COLUMN used_points INT DEFAULT 0;
ALTER TABLE `users`
	CHANGE COLUMN `used_points` `used_points` INT(11) NOT NULL DEFAULT '0' AFTER `referer`;
ALTER TABLE `users`
	ADD COLUMN `click_recharge_dialog` TINYINT NOT NULL DEFAULT 0 AFTER `used_points`;