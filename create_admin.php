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
    
    registerUser($username, $password);

    echo "$password\n";

    echo "新用户已成功添加！";
} catch (PDOException $e) {
    echo "错误信息：" . $e->getMessage();
}
