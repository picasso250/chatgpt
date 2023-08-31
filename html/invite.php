<?php
// Include the required files
require_once 'db.php'; // Assuming insertIntoTable() function is defined here
require_once 'lib.php'; // Assuming _get() function is defined here
require_once 'logic.php'; // Assuming getUserByUsername() function is defined here

// 1. Check for the "from" parameter in the URL
$inviterUsername = _get('from');

$inviteeUsername = '';

// 2. Find the inviter user information from the database
$inviter = getUserByUsername($inviterUsername);

if ($inviter) {
    // 3. Find out if the IP is eligible to add points (100 points)
    $ipAddress = getClientIP();
    $eligible = isEligibleForPoints($ipAddress, $inviterUsername);

    if ($eligible) {
        // Add points (1*100 points) to the inviter's balance
        $pointsToAdd = 100;
        rechargeUser($inviter['id'], $pointsToAdd);

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

// 4. Redirect to index.html
$queryParams = array('username' => $inviteeUsername);
$queryString = http_build_query($queryParams);
header('Location: index.html?' . $queryString);
exit();
