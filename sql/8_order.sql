
--
-- 表的结构 `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_time` datetime NOT NULL,
  `order_number` varchar(29) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `username` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_id` varchar(50) NOT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `payment_success_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转储表的索引
--

--
-- 表的索引 `orders`
--
ALTER TABLE `orders`
  ADD UNIQUE KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_uid` (`user_id`) USING BTREE;