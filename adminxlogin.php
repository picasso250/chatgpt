<?php

require_once 'logic.php';

define('IN_ADMIN', 1);

session_start();

if (isset($_GET['action']) && $_GET['action'] == 'login') {

    // 检查用户名和密码
    $username = $_POST['username'];
    $password = $_POST['password'];

    $validUser = validateUser($username, $password);

    if ($validUser) {
        // 设置用户会话
        $_SESSION['user'] = array(
            "username" => $username,
        );

        echo 'ok';
    } else {
        echo 'fail';
    }
    exit();
} else
// 处理登出操作
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // 清空用户会话
    $_SESSION['user'] = null;

    // 重定向到其他页面或登录页
    header('Location: /');
    exit();
} else if (isset($_GET['action']) && $_GET['action'] === 'changepassword') {

    // 检查用户名和密码
    $username = $_POST['username'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];

    $validUser = validateUser($username, $currentPassword);

    if ($validUser) {
        // 执行密码更改逻辑
        if (changePassword($username, $newPassword)) {
            echo '修改成功';
        } else {
            echo 'fail';
        }
    } else {
        echo '你的密码错误';
    }
    exit();
}

include 'adminlogin.html';