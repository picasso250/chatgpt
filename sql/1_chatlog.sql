-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2023-07-04 10:24:35
-- 服务器版本： 10.4.28-MariaDB
-- PHP 版本： 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- 数据库： `chatgpt`
--

-- --------------------------------------------------------

--
-- 表的结构 `chatlog`
--

CREATE TABLE `chatlog` (
  `id` bigint(11) UNSIGNED NOT NULL,
  `uid` int(11) NOT NULL,
  `ip` varchar(99) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转储表的索引
--

--
-- 表的索引 `chatlog`
--
ALTER TABLE `chatlog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `chatlog`
--
ALTER TABLE `chatlog`
  MODIFY `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;
