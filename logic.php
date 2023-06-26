<?php

require_once 'db.php';

// 生成一个随机的代号
function generateRandomCode($length = 9, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $randomIndex = rand(0, strlen($characters) - 1);
        $code .= $characters[$randomIndex];
    }
    return $code;
}

// 检查用户是否已访问过，并返回用户名
function getUsernameFromCookie()
{
    if (isset($_COOKIE['user_cookie'])) {
        // 如果已经存在用户的 Cookie，直接返回用户名
        return $_COOKIE['user_cookie'];
    } else {
        // 生成一个新的随机代号并存储在 Cookie 中
        $code = generateRandomCode();
        setcookie('user_cookie', $code, time() + 86400 * 365); // 有效期为一年
        return $code;
    }
}

function insertOrUpdateUser($username)
{
    $pdo = getInitializedPDO();
    try {
        // 开启事务
        $pdo->beginTransaction();

        $last_ip = $_SERVER['REMOTE_ADDR'];

        $sql = "INSERT INTO users (username, balance, last_ip) VALUES (:username, 100, :last_ip)
        ON DUPLICATE KEY UPDATE last_updated = CURRENT_TIMESTAMP, last_ip = :last_ip2";
        $params = [
            ':username' => $username,
            ':last_ip' => $last_ip,
            ':last_ip2' => $last_ip,
        ];

        // 执行SQL语句
        $stmt = executePreparedStmt($sql, $params);

        // 查询并返回关联数组
        $selectSql = "SELECT * FROM users WHERE username = :username";
        $selectParams = [':username' => $username];
        $selectStmt = executePreparedStmt($selectSql, $selectParams);
        $result = $selectStmt->fetch(PDO::FETCH_ASSOC);

        // 提交事务
        $pdo->commit();

        return $result;
    } catch (PDOException $e) {
        // 回滚事务
        $pdo->rollBack();
        throw $e;
    }
}

function deductUserBalance($userId, $cost)
{
    // Prepare the SQL statement
    $sql = "UPDATE users SET balance = balance - :cost WHERE id = :userId";

    // Define the parameters for the prepared statement
    $params = array(
        'cost' => $cost,
        'userId' => $userId,
    );

    // Execute the prepared statement
    executePreparedStmt($sql, $params);

    // Fetch and return the updated balance
    $sql = "SELECT balance FROM users WHERE id = :userId";
    $params = array('userId' => $userId);
    $result = executePreparedStmt($sql, $params);

    if ($result && $row = $result->fetch(PDO::FETCH_ASSOC)) {
        return $row['balance'];
    }

    return null; // Return null if balance fetching failed
}
function getMaxIdFromRows($rows)
{
    $maxId = 0;
    foreach ($rows as $row) {
        if ($row['id'] > $maxId) {
            $maxId = $row['id'];
        }
    }
    return $maxId;
}
function batchInsertUser($userList, $balance)
{
    // Prepare the SQL statement for insertion
    $sql = "INSERT INTO users (username, balance) VALUES (?, ?)";

    // Iterate over each user
    foreach ($userList as $user) {
        // Execute the prepared statement for each user
        executePreparedStmt($sql, [$user, $balance]);
    }
}
function validateUser($username, $password)
{
    $sql = "SELECT * FROM admin_users WHERE username = :username";
    $params = array(':username' => $username);

    $stmt = executePreparedStmt($sql, $params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($password, $row['password'])) {
        return true;
    }

    return false;
}
function registerUser($username, $password)
{
    // 加密密码
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 构建SQL语句
    $sql = "INSERT INTO admin_users (username, password) VALUES (:username, :password)";

    // 准备参数
    $params = array(
        ':username' => $username,
        ':password' => $hashedPassword,
    );

    // 执行预处理语句
    executePreparedStmt($sql, $params);
}
function changePassword($username, $newPassword)
{
    $sql = "UPDATE admin_users SET password = :newPassword WHERE username = :username";
    $params = array(
        ':newPassword' => password_hash($newPassword, PASSWORD_DEFAULT),
        ':username' => $username
    );

    try {
        $stmt = executePreparedStmt($sql, $params);
        if ($stmt->rowCount() > 0) {
            return true;
        }
    } catch (PDOException $e) {
        // 错误处理
    }

    return false;
}
