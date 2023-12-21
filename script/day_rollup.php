<?php

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.");
}

define('DATA_ROOT', dirname(__DIR__) . '/data');

require_once dirname(__DIR__) . '/html/logic.php';

// 获取前一天的日期
$yesterday = date('Y-m-d', strtotime('-1 day'));

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

echo "数据已成功插入到 statistics 表中。";
