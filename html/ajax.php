<?php

require_once 'lib.php';
require_once 'logic.php';
require_once 'actions.php';

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    $action_func = 'action_' . $action;

    if (function_exists($action_func)) {
        $action_func(); // Call the function if it exists
    } else {
        // Handle the case when the function doesn't exist
        echo "The action you requested is not available.";
    }
} else {
    // Handle the case when 'action' parameter is missing
    echo "The 'action' parameter is missing or empty. Please provide a valid action.";
}
