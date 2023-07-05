<?php
function log_data($msg)
{
    $tmp = sys_get_temp_dir();
    error_log("$msg\n", 3, $tmp . '/php_data.log');
}

function log_info($msg)
{
    $logMessage = date('c') . " INFO: $msg\n";
    error_log($logMessage, 3, __DIR__ . '/../log/info.log');
}
