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
$resultUser = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取当前日期
$currentDate = date('Y-m-d');

// 计算开始日期
$startDate = date('Y-m-d', strtotime("-$days day", strtotime($currentDate)));

// 查询 statistics 表中指定日期范围内的数据
$sql = "SELECT * FROM statistics WHERE date BETWEEN :start_date AND :end_date ORDER BY date ASC";
$params = [
    ':start_date' => $startDate,
    ':end_date' => $currentDate,
];
$stmt = executePreparedStmt($sql, $params);
$resultStatistics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 遍历查询结果
foreach ($resultStatistics as $key => &$row) {
    // 如果不是第一行，计算差值并存入数组
    if ($key > 0) {
        $currentValue = $row['total_points']; 
        $previousValue = $resultStatistics[$key - 1]['total_points']; 
        
        // 计算差值并存入数组
        $dailyDifference = $currentValue - $previousValue;
        $row['day_points'] = $dailyDifference;
    }else{
        $row['day_points'] = 0;
    }
}

// 格式化结果为JavaScript数组
$jsResult = [
    'users' => $resultUser,
    'statistics' => $resultStatistics
];

if (isset($_GET['days'])) {
    outputJson($jsResult);
} else {
    include 'statistics.html';
}
