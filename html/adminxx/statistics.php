<?php

require_once '../lib.php';
require_once '../logic.php';
require_once 'admin_page.php';

define('IN_ADMIN', 1);

$configPages = require('pages.php');
$config = $configPages['users'];

session_start();

if (empty($_SESSION['user'])) {
    die("not login");
}
$username = $_SESSION['user']["username"];

if (isset($_GET['action']) && $_GET['action'] === 'gen_user') {
    $balance = intval($_POST['balance']);
    $userCount = intval($_POST['userCount']);

    $userList = [];

    // Generate users based on the given count
    for ($i = 0; $i < $userCount; $i++) {
        // Generate a user and add it to the user list
        $userList[] = generateRandomCode();
    }

    batchInsertUser($userList, $balance);
    addAdminLog(
        $_SESSION['user']['id'],
        'batchInsertUser',
        ['userCount' => $userCount, 'balance' => $balance, "userList" => $userList]
    );
    log_info("$username gen_user userCount=$userCount balance=$balance " . json_encode($userList));

    // Format the user list as HTML
    $userListHTML = '<ul>';
    foreach ($userList as $user) {
        $userListHTML .= '<li>' . $user . '</li>';
    }
    $userListHTML .= '</ul>';

    // Return the generated user list as a response
    echo $userListHTML;
    exit();
}


$sql = "SELECT DATE(last_updated) AS date, COUNT(*) AS count FROM users WHERE last_updated >= CURDATE() - INTERVAL 30 DAY GROUP BY DATE(last_updated) ORDER BY DATE(last_updated)";
$stmt = executePreparedStmt($sql, []);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 格式化结果为JavaScript数组
$jsResult = [];
foreach ($result as $row) {
    $jsResult[] = [
        'date' => $row['date'],
        'count' => $row['count']
    ];
}

include 'statistics.html';
