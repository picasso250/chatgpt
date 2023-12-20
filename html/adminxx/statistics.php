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

$days = 30;
if (isset($_GET['days'])) {
    $days = $_GET['days'];
}

$sql = "SELECT DATE(last_updated) AS date, COUNT(*) AS count, SUM(click_recharge_dialog = 1) AS click_count
        FROM users
        WHERE last_updated >= CURDATE() - INTERVAL :days DAY
        GROUP BY DATE(last_updated)
        ORDER BY DATE(last_updated)";
$stmt = executePreparedStmt($sql, [':days' => $days]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 格式化结果为JavaScript数组
$jsResult = [];
foreach ($result as $row) {
    $jsResult[] = [
        'date' => $row['date'],
        'count' => $row['count'],
        'click_recharge_dialog' => $row['click_count']
    ];
}

if (isset($_GET['days'])) {
    outputJson($jsResult);
} else {
    include 'statistics.html';
}
