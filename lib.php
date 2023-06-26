<?php
function log_data($msg)
{
    error_log("$msg\n", 3, __DIR__ . '/data.log');
}

function log_info($msg)
{
    $logMessage = "INFO: $msg\n";
    error_log($logMessage, 3, __DIR__ . '/info.log');
}
