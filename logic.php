<?php

require_once 'db.php';

// 生成一个随机的代号
function generateRandomCode($length = 9)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
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

        $sql = "INSERT INTO users (username, balance) VALUES (:username, 100)
                ON DUPLICATE KEY UPDATE last_updated = CURRENT_TIMESTAMP";
        $params = [':username' => $username];

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
