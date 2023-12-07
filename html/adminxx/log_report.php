<?php

require_once '../lib.php';
require_once '../logic.php';
require_once 'admin_page.php';

define('IN_ADMIN', 1);
define('ADMIN_ROOT', __DIR__);
define('HTML_ROOT', dirname(__DIR__));

$configPages = require('pages.php');
$config = $configPages['users'];

session_start();

if (empty($_SESSION['user'])) {
    die("not login");
}
include HTML_ROOT.'/../log/report.html';
