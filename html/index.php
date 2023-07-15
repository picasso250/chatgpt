<?php

define('IN_PHP', 1);
// phpinfo();exit;

require_once 'lib.php';
require_once 'db.php';
require_once 'logic.php';

error_reporting(E_ALL);

session_start();

// 获取用户名
$username = getUsernameFromCookie();

// 在数据库中插入或更新用户
$user = insertOrUpdateUser($username);
$_SESSION['user_ses'] = $user;

if (_get('action') === 'UserInfo') {
    echo json_encode($user);
    exit();
} else if (_get('action') === 'GetConversation') {
    echo json_encode(getConversationRecords(_get('conversation_id')));
    exit();
}

$conversations = getUserConversations($user['id']);

$type = "个人";

include 'i.html';
