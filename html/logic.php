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
function generateRandomUsername()
{
    return generateRandomCode();
}

function getClientIP()
{
    // Check for the most common headers containing the client's IP address
    $headers = array(
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    );

    $ipAddresses = array();

    foreach ($headers as $header) {
        if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            $ipAddresses[$header] = $ip;
        }
    }

    log_info("Collected IP addresses: " . json_encode($ipAddresses));

    foreach ($headers as $header) {
        if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            // Extract the first IP address in the list if multiple IP addresses are provided
            $ip = explode(',', $ip)[0];
            return $ip;
        }
    }

    // Return a default IP address (e.g., localhost) if none of the headers are found
    return '127.0.0.1';
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

function insertOrUpdateUser($username, $ip)
{
    // Step 1: 检查是否有ip和username都匹配的用户
    $selectSql = "SELECT id, username, balance FROM users WHERE username = :username AND last_ip = :last_ip";
    $selectParams = [
        ':username' => $username,
        ':last_ip' => $ip
    ];
    $selectStmt = executePreparedStmt($selectSql, $selectParams);
    $matchingUser = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if ($matchingUser) {
        // 如果有ip和username都匹配的用户，直接返回该用户信息
        return $matchingUser;
    }

    // Step 2: 检查是否有相同ip的用户
    $selectSql = "SELECT id, username, balance FROM users WHERE last_ip = :last_ip";
    $selectParams = [
        ':last_ip' => $ip
    ];
    $selectStmt = executePreparedStmt($selectSql, $selectParams);
    $existingUser = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // 如果有相同ip的用户，返回false
        return false;
    }

    // Step 3: 插入新用户并返回插入后的用户信息
    $sql = "INSERT INTO users (username, balance, last_ip, email, password) VALUES (:username, 100, :last_ip, '', '')";
    $params = [
        ':username' => $username,
        ':last_ip' => $ip,
    ];

    // 执行SQL语句
    $stmt = executePreparedStmt($sql, $params);

    // 查询并返回插入后的用户信息
    $selectSql = "SELECT id, username, balance FROM users WHERE username = :username";
    $selectParams = [':username' => $username];
    $selectStmt = executePreparedStmt($selectSql, $selectParams);
    $result = $selectStmt->fetch(PDO::FETCH_ASSOC);

    return $result;
}

function getUserByUsername($username)
{
    $sql = "SELECT id, username, balance, free_package_end FROM users WHERE username = :username";
    $params = [':username' => $username];

    // Execute the SQL query
    $stmt = executePreparedStmt($sql, $params);

    // Fetch and return the associative array
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function getUserById($id)
{
    $sql = "SELECT id, username, balance, free_package_end FROM users WHERE id = :id";
    $params = [':id' => $id];

    // Execute the SQL query
    $stmt = executePreparedStmt($sql, $params);

    // Fetch and return the associative array
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
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

function rechargeUserBalance($username, $amount)
{
    // Prepare the SQL statement
    $sql = "UPDATE users SET balance = balance + :amount WHERE username = :username";

    // Define the parameters for the prepared statement
    $params = array(
        'amount' => $amount,
        'username' => $username,
    );

    // Execute the prepared statement
    executePreparedStmt($sql, $params);
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
function getMinIdFromRows($rows)
{
    $minId = PHP_INT_MAX;
    foreach ($rows as $row) {
        if ($row['id'] < $minId) {
            $minId = $row['id'];
        }
    }
    return $minId;
}
function buildQueryString($params)
{
    // 排除 'lt' 和 'gt' 参数
    $filteredParams = array_diff_key($_GET, ['lt' => '', 'gt' => '']);
    $filteredParams = array_merge($filteredParams, $params);

    // 构建查询字符串
    $query = http_build_query($filteredParams);
    return $query;
}

function batchInsertUser($userList, $balance)
{
    // Prepare the SQL statement for insertion
    $sql = "INSERT INTO users (username, balance,email,password) VALUES (?, ?,'','')";

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
        return $row;
    }

    return false;
}
function registerUser($username, $password)
{
    // 加密密码
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 构建SQL语句
    $sql = "INSERT INTO admin_users (username, password,email) VALUES (:username, :password,'')";

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


function addChatLog($uid, $question, $answer, $price)
{
    $last_ip = $_SERVER['REMOTE_ADDR'];

    $sql = "INSERT INTO chatlog (uid,question,answer,ip,money) VALUES (?,?,?,?,?)";
    $params = [
        $uid, json_encode($question), $answer, $last_ip, $price,
    ];

    // 执行SQL语句
    $stmt = executePreparedStmt($sql, $params);
}


function adminUserList()
{
    $sql = "SELECT * from admin_users ";

    $stmt = executePreparedStmt($sql, []);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 向 admin_log 表中插入管理员日志。
 *
 * @param int $adminId 管理员的 ID。
 * @param string $action 执行的操作。
 * @param array $detail 详细信息数组。
 * @return boolean 插入操作是否成功。
 * @throws PDOException 如果查询执行失败。
 */
function addAdminLog($adminId, $action, $detail = null)
{
    $data = [
        'aid' => $adminId,
        'action' => $action,
    ];
    if ($detail) {
        $data['detail'] = json_encode($detail);
    }

    return insertIntoTable('admin_log', $data);
}

// 获取指定conversation_id的所有conversation_records
function getConversationRecords($conversationId)
{

    // 准备查询语句
    $sql = "SELECT * FROM conversation_records WHERE conversation_id = :conversationId order by id asc";

    // 执行预处理语句
    $stmt = executePreparedStmt($sql, ['conversationId' => $conversationId]);

    // 获取查询结果的数组
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}
function getConversationForest($conversationId)
{
    $records = getConversationRecords($conversationId);

    // Build the forest by finding the roots (records with 'prev' as null)
    $forest = array();
    foreach ($records as $record) {
        if ($record['prev'] === null) {
            $tree = buildTreeFromRoot($record, $records);
            $forest[] = $tree;
        }
    }

    return $forest;
}
function addConversationRecord($conversationId, $message, $answer, $price)
{
    $data = [
        'conversation_id' => $conversationId,
        'user_message' => $message,
        'assistant_message' => $answer,
        'price' => $price
    ];

    insertIntoTable('conversation_records', $data);
}

function build_answer($responsedata)
{
    $answer = "";
    if (substr(trim($responsedata), -6) == "[DONE]") {
        $responsedata = substr(trim($responsedata), 0, -6) . "{";
    }
    $responsearr = explode("}\n\ndata: {", $responsedata);

    foreach ($responsearr as $msg) {
        $contentarr = json_decode("{" . trim($msg) . "}", true);
        if (isset($contentarr['choices'][0]['delta']['content'])) {
            $answer .= $contentarr['choices'][0]['delta']['content'];
        }
    }
    return $answer;
}

// 根据uid获取所有的conversation，并连接conversations表
function getUserConversations($userId)
{
    // 假设已经建立了数据库连接，$pdo 是你的 PDO 对象

    // 准备查询语句，使用INNER JOIN连接user_conversations和conversations表
    $sql = "SELECT uc.*,c.* 
            FROM user_conversations uc
            INNER JOIN conversations c ON uc.conversation_id = c.id
            WHERE uc.user_id = :userId order by c.id desc limit 100";

    // 执行预处理语句
    $params = array(':userId' => $userId);
    $stmt = executePreparedStmt($sql, $params);

    // 获取查询结果的数组
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}

// 创建新的对话
function createConversation($user_id, $type, $message)
{

    // Prepare the SQL statement
    $sql = "INSERT INTO conversations (type, title, created_at) 
            VALUES (:type, :title, now())";

    // Define the parameters
    $params = [
        'type' => $type,
        'title' => mb_substr($message, 0, 30)
    ];

    // Execute the prepared statement
    executePreparedStmt($sql, $params);

    $conversationId = getInitializedPDO()->lastInsertId();

    insertUserConversation($user_id, $conversationId);

    return $conversationId;
}

function insertUserConversation($userId, $conversationId)
{
    // Prepare the SQL statement
    $sql = "INSERT INTO user_conversations (user_id, conversation_id, created_at) 
            VALUES (:user_id, :conversation_id, now())";

    // Define the parameters
    $params = [
        'user_id' => $userId,
        'conversation_id' => $conversationId
    ];

    // Execute the prepared statement
    executePreparedStmt($sql, $params);
}

function createNewUser($username, $email, $password, $ipAddress)
{
    // Create a new user with initial points of 100
    $newUser = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'balance' => 100,
        'last_ip' => $ipAddress
    ];
    return insertIntoTable('users', $newUser);
}
function getInvitedUsersCount($inviterId)
{
    $sql = "SELECT COUNT(DISTINCT invitee_id) AS invitedUsersCount FROM invite WHERE inviter_id = ?";
    $params = array($inviterId);
    $stmt = executePreparedStmt($sql, $params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['invitedUsersCount'];
}
// 辅助函数：实际的充值逻辑
function rechargeUser($user_id, $point)
{
    $sql = "UPDATE users SET balance = balance + :pointsToAdd WHERE id = :id";
    $params = [':pointsToAdd' => $point, ':id' => $user_id];
    executePreparedStmt($sql, $params);

    return true; // 充值成功
}

function isIPAddressEligible($ipAddress)
{
    $sql = "SELECT id FROM invite WHERE ip_address = :ip";
    $params = [':ip' => $ipAddress];
    $stmt = executePreparedStmt($sql, $params);

    return $stmt->rowCount() === 0;
}

function isEligibleForPoints($ipAddress, $inviterUsername)
{
    // Check if the IP address is different from the inviter's IP
    $inviterIP = getInviterIPAddressByUsername($inviterUsername);

    if ($ipAddress === $inviterIP) {
        return false; // IP addresses are the same
    }

    // Check if the IP address is eligible based on invite table
    $isIPAddressEligible = isIPAddressEligible($ipAddress);

    return $isIPAddressEligible;
}

function getInviterIPAddressByUsername($inviterUsername)
{
    $sql = "SELECT last_ip FROM users WHERE username = :username";
    $params = [':username' => $inviterUsername];
    $stmt = executePreparedStmt($sql, $params);

    $row = $stmt->fetch();
    return $row ? $row['last_ip'] : null;
}
function sign(array $data, $key)
{
    ksort($data);
    $sign = strtoupper(md5(urldecode(http_build_query($data)) . '&key=' . $key));
    return $sign;
}
function updatePaymentStatus($orderNumber)
{
    $sql = "UPDATE orders SET is_paid = 1, payment_success_time = NOW() WHERE order_number = ?";
    $params = array($orderNumber);

    $stmt = executePreparedStmt($sql, $params);

    // Get the number of rows affected
    $rowCount = $stmt->rowCount();

    return $rowCount;
}

function getOrderDetails($orderNumber)
{
    $sql = "SELECT * FROM orders WHERE order_number = :orderNumber";
    $params = array(':orderNumber' => $orderNumber);

    $result = executePreparedStmt($sql, $params);

    return $result->fetch(PDO::FETCH_ASSOC); // Fetch the order details as an associative array
}

function insertOrder($order_number, $total_fee, $username, $user_id, $request_id, $subscription_name = null)
{
    // Prepare the SQL statement
    $sql = "INSERT INTO orders (order_time, order_number, payment_amount, username, user_id, request_id, subscription_name, is_paid) 
            VALUES (now(), :order_number, :payment_amount, :username, :user_id, :request_id, :subscription_name, :is_paid)";

    // Define the parameters
    $params = [
        'order_number' => $order_number,
        'payment_amount' => $total_fee,
        'username' => $username,
        'user_id' => $user_id,
        'request_id' => $request_id,
        'subscription_name' => $subscription_name,
        'is_paid' => 0 // Order is not paid yet
    ];

    // Execute the prepared statement
    $stmt = executePreparedStmt($sql, $params);
}

function isFreePackageExpired($freePackageEnd) {
    // Convert UTC free_package_end to a DateTime object
    $freePackageEndUTC = new DateTime($freePackageEnd, new DateTimeZone('UTC'));

    // Get the current UTC date and time
    $currentUTC = new DateTime('now', new DateTimeZone('UTC'));

    return $freePackageEndUTC < $currentUTC;
}

function subscribeUserToPlan($username, $days) {
    
    // Calculate the interval in days for the subscription
    $interval = "INTERVAL {$days} DAY";
    
    // Update the user's subscription information with the new expiration date
    $updateSql = "UPDATE users SET free_package_end = NOW() + {$interval} WHERE username = :username";
    $updateParams = array(":username" => $username);
    return executePreparedStmt($updateSql, $updateParams);
}
