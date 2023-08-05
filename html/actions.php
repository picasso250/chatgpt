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
        $user = insertOrUpdateUser($username, $ip);
        if ($user === false) {
            echo json_encode(['error' => '您已经创建过免费用户']);
            return;
        }
    } else {
        // If 'username' is provided, fetch the user and conversations from the database
        $user = getUserByUsername($username);
    }

    // Store the user information in the session
    $_SESSION['user_ses'] = $user;

    // Calculate the expiration timestamp for 3 years from now
    $expirationTimestamp = time() + (3 * 365 * 24 * 60 * 60); // 3 years in seconds

    // Set the cookie with the expiration date
    setcookie('username', $user['username'], $expirationTimestamp);

    // Fetch conversations for the user
    $conversations = getUserConversations($user['id']);

    // Output the JSON-encoded data
    echo json_encode(compact('conversations', 'user'));
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
