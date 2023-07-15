<?php
function log_debug($msg)
{
    error_log("DEBUG\t$msg");
}

function log_info($msg)
{
    $logMessage = date('c') . " INFO: $msg\n";
    error_log($logMessage, 3, __DIR__ . '/../log/info.log');
}
function _get($key, $default = '')
{
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

function _post($key, $default = '')
{
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}
