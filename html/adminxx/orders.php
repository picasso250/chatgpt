<?php

require_once '../lib.php';
require_once '../logic.php';
require_once 'admin_page.php';

define('IN_ADMIN', 1);

$configPages = require('pages.php');
$config = $configPages['orders'];

session_start();

if (empty($_SESSION['user'])) {
    die("not login");
}
$username = $_SESSION['user']["username"];

$params = [];
$where = [];

$page_num = getPageNum();
$per_page = getPerPage();

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
$order_by = getOrderString($config);
$sql = "SELECT * FROM orders $where $order_by LIMIT $per_page OFFSET " . (($page_num - 1) * $per_page);
$stmt = executePreparedStmt($sql, $params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT COUNT(*) as count FROM orders $where";
$stmt = executePreparedStmt($sql, $params);
$countResult = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRowCount = $countResult['count'];

$pagination = '';

if ($totalRowCount > 0) {
    $pagination = generatePagination($totalRowCount, $page_num, $per_page);
}

include 'orders.html';
