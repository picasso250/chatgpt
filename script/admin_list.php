<?php

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.");
}

require_once dirname(__DIR__) . '/html/logic.php';

$user_list = adminUserList();
print_r($user_list);
