ALTER TABLE `users` ADD `free_package_end` DATETIME NULL DEFAULT NULL AFTER `last_ip`;
ALTER TABLE orders
ADD subscription_name varchar(50) COLLATE utf8mb4_general_ci;
ALTER TABLE `users` CHANGE `last_updated` `last_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;