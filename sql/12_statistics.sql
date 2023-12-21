CREATE TABLE `statistics` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`total_points` INT(11) NOT NULL,
	`date` DATE NOT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `idx_date` (`date`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;
