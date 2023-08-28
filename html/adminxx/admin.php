<?php

require_once '../lib.php';
require_once '../logic.php';
require_once 'admin_page.php';

define('IN_ADMIN', 1);

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

$params = [];
$where = [];

// 获取用户列表（包括分页）
// 获取GET参数
$page_num = isset($_GET['page_num']) ? intval($_GET['page_num']) : 1;
$per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;

// 设置默认值
if ($page_num <= 0) {
    $page_num = 1;
}

if ($per_page <= 0) {
    $per_page = 10;
}

$username = isset($_GET['username']) ? trim($_GET['username']) : '';

if ($username) {
    $where[] = "username like ?";
    $params[] = "%$username%";
}

if ($where) {
    $where = "WHERE " . implode(' AND ', $where);
} else {
    $where = '';
}
$sql = "SELECT * FROM users $where order by id asc LIMIT $per_page OFFSET " . (($page_num - 1) * $per_page);
$stmt = executePreparedStmt($sql, $params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT COUNT(*) as count FROM users $where";
$stmt = executePreparedStmt($sql, $params);
$countResult = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRowCount = $countResult['count'];

$pagination = '';

if ($totalRowCount > 0) {
    $pagination = generatePagination($totalRowCount, $page_num, $per_page);
}

$configPages = require('pages.php');
$config = $configPages['users'];

include 'admin.html';
