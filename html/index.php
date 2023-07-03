<?php

define('IN_PHP', 1);

require_once 'db.php';
require_once 'logic.php';

session_start();

// 获取用户名
$username = getUsernameFromCookie();

// 在数据库中插入或更新用户
$user = insertOrUpdateUser($username);
$_SESSION['user_ses'] = $user;

$type = "个人";

include 'i.html';
