<?php

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.");
}

define('DATA_ROOT', dirname(__DIR__) . '/data');

require_once dirname(__DIR__) . '/html/logic.php';

// 设置时区为+8:00
$setTimezoneSql = "SET time_zone = '+08:00'";
$setTimezoneParams = [];
executePreparedStmt($setTimezoneSql, $setTimezoneParams);

// 获取前一天的日期
$yesterdaySql = "SELECT CURDATE() - INTERVAL 1 DAY AS yesterday";
$yesterdayParams = [];
$yesterdayStmt = executePreparedStmt($yesterdaySql, $yesterdayParams);
$yesterdayResult = $yesterdayStmt->fetchAll();
$yesterday = $yesterdayResult[0]['yesterday'];

$sql = "SELECT SUM(used_points) AS total_points FROM users ";
$params = [];
$stmt = executePreparedStmt($sql, $params);
$result = $stmt->fetchAll();

// 获取查询结果
$totalPointsFromDB = $result[0]['total_points'];

// 将结果插入到 statistics 表中
$insertSql = "INSERT INTO statistics (total_points, date) VALUES (:total_points, :date)";
$insertParams = [
    ':total_points' => $totalPointsFromDB,
    ':date' => $yesterday,
];

executePreparedStmt($insertSql, $insertParams);

echo "$yesterday 数据已成功插入到 statistics 表中。";
