<?php

function get_config_data($filename = 'config.ini') {
    static $configData = [];

    if (empty($configData[$filename])) {
        $configData[$filename] = parse_ini_file(dirname(__DIR__) . '/config/' . $filename);
    }

    return $configData[$filename];
}

function log_debug($msg)
{
    error_log("DEBUG\t$msg");
}

function log_info($msg)
{
    $logMessage = date('c') . " INFO: $msg\n";

    if (php_sapi_name() === 'fpm-fcgi') {
        // Logic for PHP-FPM environment
        error_log($logMessage, 3, '/tmp/fpm_info.log');
    } else {
        // Default logic for other environments
        error_log($logMessage);
    }
}

function _get($key, $default = '')
{
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

function _post($key, $default = '')
{
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}
