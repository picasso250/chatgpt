<?php

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.");
}

require_once 'logic.php';

// 检查是否提供了用户名作为命令行参数
if (isset($argv[1])) {
    $username = $argv[1];
} else {
    die("Error: Username is not provided.");
}

// 生成随机密码
$password = generateRandomCode(12, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[]{}|;:,.<>?');

try {
    if (isset($argv[2]) && $argv[2] === 'reset') {
        changePassword($username, $password);
    } else {
        registerUser($username, $password);
    }

    echo "$password\n";

} catch (PDOException $e) {
    echo "错误信息：" . $e->getMessage();
}
