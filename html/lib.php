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
    $logMessage = date('c') . "\tDEBUG: $msg\n";

    if (php_sapi_name() === 'fpm-fcgi') {
        // Logic for PHP-FPM environment
        error_log($logMessage, 3, '/tmp/fpm_debug.log');
    } else {
        // Default logic for other environments
        error_log($logMessage);
    }
}

function log_info($msg)
{
    $logMessage = date('c') . "\tINFO: $msg\n";

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

function outputJson($data) {
    header('Content-Type: application/json; charset=utf-8'); // Set charset to UTF-8
    echo json_encode($data, JSON_UNESCAPED_UNICODE); // Use JSON_UNESCAPED_UNICODE to handle Unicode characters
}

function buildQueryString($params)
{
    $filteredParams = array_merge($_GET, $params);

    // 构建查询字符串
    $query = http_build_query($filteredParams);
    return $query;
}