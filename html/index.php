<?php

define('IN_PHP', 1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/logic.php';

// 获取用户名
$username = getUsernameFromCookie();

// 在数据库中插入或更新用户
$user = insertOrUpdateUser($username);

$type = "个人";

include 'i.html';
