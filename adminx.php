<?php

require_once 'lib.php';
require_once 'logic.php';

define('IN_ADMIN', 1);

session_start();

if(empty($_SESSION['user'])){
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
    log_info("$username gen_user userCount=$userCount balance=$balance ".json_encode($userList));

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

// 获取用户列表（包括分页）
$gt = isset($_GET['gt']) ? intval($_GET['gt']) : 0;
$limit = 10; // 每页显示的记录数

$sql = "SELECT * FROM users where id>? LIMIT $limit";
$stmt = executePreparedStmt($sql, [$gt]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT COUNT(*) as count FROM users ";
$stmt = executePreparedStmt($sql, []);
$countResult = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRowCount = $countResult['count'];

$pagination = '';

if ($totalRowCount > 0) {

    $totalPages = ceil($totalRowCount / $limit);

    $pagination .= '<div class="pagination">';

    // Next page link
    if ($totalRowCount > $limit) {
        $nextPageId = getMaxIdFromRows($rows);
        $pagination .= '<a href="?gt=' . $nextPageId . '">下一页</a>';
    }

    $pagination .= '</div>';
}

include 'admin.html';
