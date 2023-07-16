<?php

require_once 'lib.php';
require_once 'logic.php';

define('IN_ADMIN', 1);

session_start();

if (empty($_SESSION['user'])) {
    die("not login");
}
$username = $_SESSION['user']["username"];

if (isset($_GET['action']) && $_GET['action'] === 'gen_user') {
}
if (isset($_GET['cid'])) {
    echo '<pre>' . json_encode(getConversationRecords($_GET['cid'])) . '</pre>';
    echo "<script>let data = JSON.parse(document.querySelector('pre').textContent);
    let formattedData = JSON.stringify(data, undefined, 2);
    document.querySelector('pre').textContent = formattedData;</script>";
    exit;
}

$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
$gt = isset($_GET['gt']) ? intval($_GET['gt']) : 0;
$limit = 10; // 每页显示的记录数

$rows = getUserConversations($uid);

$sql = "SELECT COUNT(*) as count FROM user_conversations where user_id=?  ";
$stmt = executePreparedStmt($sql, [$uid]);
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

include 'adminchatlog.html';
