<?php
// Include the required files
require_once 'db.php'; // Assuming insertIntoTable() function is defined here
require_once 'lib.php'; // Assuming _get() function is defined here
require_once 'logic.php'; // Assuming getUserByUsername() function is defined here

// 1. Check for the "from" parameter in the URL
$inviterUsername = _get('from');

// 2. Find the inviter user information from the database
$inviter = getUserByUsername($inviterUsername);

if ($inviter) {
    // 3. Find out if the IP is eligible to add points (10*100 points)
    $ipAddress = getClientIP();
    $eligible = true;

    // Check if the IP address already exists in the invite table
    $sql = "SELECT id FROM invite WHERE ip_address = :ip";
    $params = [':ip' => $ipAddress];
    $stmt = executePreparedStmt($sql, $params);

    if ($stmt->rowCount() > 0) {
        $eligible = false; // IP already used for an invite
    }

    if ($eligible) {
        // Add points (10*100 points) to the inviter's balance
        $pointsToAdd = 1000;
        $inviterBalance = $inviter['balance'] + $pointsToAdd;

        // Update the inviter's balance in the users table
        $sql = "UPDATE users SET balance = balance + :pointsToAdd WHERE id = :id";
        $params = [':pointsToAdd' => $pointsToAdd, ':id' => $inviter['id']];
        executePreparedStmt($sql, $params);

        // Create a new user with initial points of 100
        $inviteeUsername = generateRandomUsername();
        $invitee_id = createNewUser($inviteeUsername, '', '', $ipAddress);

        // Insert the invite record into the invite table
        $inviteData = [
            'inviter_id' => $inviter['id'],
            'invitee_id' => $invitee_id,
            'ip_address' => $ipAddress,
            'points' => $pointsToAdd
        ];
        insertIntoTable('invite', $inviteData);
    }
}

// // 4. Redirect to index.html
$queryParams = array('username' => $inviteeUsername);
$queryString = http_build_query($queryParams);
header('Location: index.html?' . $queryString);
exit();
