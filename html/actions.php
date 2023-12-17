<?php

require_once 'lib.php';
require_once 'logic.php';

function action_Index()
{
    error_reporting(E_ALL);
    session_start();

    // Check if the 'username' parameter exists in the request
    $username = _get('username');

    if (!$username) {
        // If 'username' is not provided, generate a random username
        $username = generateRandomUsername();
        // Get the client's IP address
        $ip = getClientIP();
        // Insert or update the user with the random username and IP
        $referer = _get('referer');
        $user = insertOrUpdateUser($username, $ip, $referer);
        if ($user === false) {
            echo json_encode(['error' => '您已经创建过免费用户']);
            return;
        }
    } else {
        // If 'username' is provided, fetch the user and conversations from the database
        $user = getUserByUsername($username);
        if (!$user) {
            echo json_encode(['error' => '没有这个用户']);
            return;
        }
        updateLastIP($user['id'], getClientIP());
    }

    // Store the user information in the session
    $_SESSION['user_ses'] = $user;

    // Calculate the expiration timestamp for 3 years from now
    $expirationTimestamp = time() + (3 * 365 * 24 * 60 * 60); // 3 years in seconds

    // Set the cookie with the expiration date
    setcookie('username', $user['username'], $expirationTimestamp);

    // Fetch conversations for the user
    $conversations = getUserConversations($user['id']);

    $amounts = [10, 21, 55, 120];
    $characters = calculateCharactersForAmounts($amounts);

    // Output the JSON-encoded data
    echo json_encode(compact('conversations', 'user', 'characters'));
}

function action_GetConversation()
{
    $conversation_id = _get('conversation_id');
    $conversationRecords = getConversationRecords($conversation_id);
    echo json_encode($conversationRecords);
}

function action_GetInvitedUsersCount()
{
    session_start();
    $invitedUsersCount = getInvitedUsersCount($_SESSION['user_ses']['id']);
    echo json_encode(compact('invitedUsersCount'));
}

function action_Recharge()
{
    // 鉴权，验证secret
    $secret = _post('secret');
    if ($secret !== get_config_data()['SECRET']) {
        echo json_encode(['error' => 'Authentication failed. Invalid secret.']);
        return;
    }

    // 获取user_id或username和point
    $user_id = _post('user_id');
    $username = _post('username');
    $point = _post('point');

    // Check if either user_id or username is provided
    if ((!empty($user_id) || !empty($username)) && is_numeric($point)) {

        // If username is provided, get user_id using getUsernameById function
        if (!empty($username)) {
            $user = getUserByUsername($username);
            if ($user)
                $user_id = $user['id'];
        }

        // If user_id is available, proceed with the recharge logic
        if (!empty($user_id)) {

            // Use getUserById function to get user information
            $user = getUserById($user_id);

            // Check if user exists
            if ($user) {
                // Perform the recharge operation, you can add recharge records to the database or any other persistence operation
                $result = rechargeUser($user_id, $point);

                if ($result) {
                    echo json_encode(['data' => "用户 $user[username] 充值成功。已添加积分：$point"]);
                } else {
                    echo json_encode(['error' => "Failed to recharge user $user_id."]);
                }
            } else {
                // If the user does not exist, return an error message
                echo json_encode(['error' => "User $user_id does not exist."]);
            }
        } else {
            // If neither user_id nor username is valid, return an error message
            echo json_encode(['error' => 'Invalid user_id or username.']);
        }
    } else {
        // If the user_id or point is missing or not numeric, return an error message
        echo json_encode(['error' => 'Invalid user_id or point.']);
    }
}

function action_SetOpenID()
{
    // 鉴权，验证secret
    $secret = _post('secret');
    if ($secret !== get_config_data()['SECRET']) {
        echo json_encode(['error' => 'Authentication failed. Invalid secret.']);
        return;
    }

    // Assuming you have already authenticated the request and obtained user_id and open_id from the request data.
    $user_id = _post('user_id');
    $open_id = _post('open_id');

    try {
        // Prepare the SQL statement with placeholders
        $sql = "INSERT INTO wechat_users (user_id, open_id) VALUES (:user_id, :open_id) 
                ON DUPLICATE KEY UPDATE open_id = :open_id1";

        // Parameters to bind in the prepared statement
        $params = array(':user_id' => $user_id, ':open_id' => $open_id, ':open_id1' => $open_id);

        // Execute the prepared statement using the executePreparedStmt function
        $stmt = executePreparedStmt($sql, $params);

        // Check if an insert or update occurred
        if ($stmt->rowCount() > 0) {
            echo json_encode(['data' => "OpenID successfully set for user $user_id."]);
        } else {
            echo json_encode(['data' => "OpenID for user $user_id updated."]);
        }
    } catch (PDOException $e) {
        // Handle the error if something goes wrong
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
}

function action_GetUserInfo()
{
    // 鉴权，验证secret
    $secret = _post('secret');
    if ($secret !== get_config_data()['SECRET']) {
        echo json_encode(['error' => 'Authentication failed. Invalid secret.']);
        return;
    }

    // Assuming you have already authenticated the request and obtained open_id from the request data.
    $open_id = _post('open_id');

    try {
        // Prepare the SQL statement with placeholders
        $sql = "SELECT user_id,username,balance FROM wechat_users WHERE open_id = :open_id";

        // Parameters to bind in the prepared statement
        $params = array(':open_id' => $open_id);

        // Execute the prepared statement using the executePreparedStmt function
        $stmt = executePreparedStmt($sql, $params);

        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode(['data' => $result]);
        } else {
            echo json_encode(['error' => 'User ID not found for the provided OpenID.']);
        }
    } catch (PDOException $e) {
        // Handle the error if something goes wrong
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
}
function action_PayEvent()
{
    $num = $_GET['order_num'] ?? null;

    $orderDetails = getOrderDetails($num);

    if (!$orderDetails) {
        echo 'no';
        return;
    }

    if ($orderDetails['is_paid'] == 1) {
        echo 'ok';
    } else {
        echo 'ing';
    }
}
function action_DeleteConversation()
{
    session_start();

    if (isset($_POST['id'])) {
        $userId = $_SESSION['user_ses']['id'];

        $conversationId = $_POST['id'];

        $sql = "DELETE FROM user_conversations WHERE conversation_id = :conversationId AND user_id = :userId";
        $params = array(':conversationId' => $conversationId, ':userId' => $userId);

        $stmt = executePreparedStmt($sql, $params);

        if ($stmt) {
            // Success message or any additional logic after deletion
            echo "Conversation deleted successfully";
        } else {
            // Error message or handling for unsuccessful deletion
            echo "Error deleting conversation";
        }
    } else {
        echo "Invalid input";
    }
}

function action_UserBalance()
{
    session_start();

    // Store the user information in the session
    $user = $_SESSION['user_ses'];

    // Fetch conversations for the user
    $user = getUserById($user['id']);

    // Output the JSON-encoded data
    outputJson(compact('user'));
}
