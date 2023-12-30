<?php

require_once '../lib.php';
require_once 'GeminiAPI.php';

function getGeminiAPI()
{
    $api_key = 'AIzaSyA5V7OQZfFUkZlSt6TBR1vVLnco8PFS6wc';
    static $geminiAPI;
    if (!$geminiAPI) {
        $geminiAPI = new GeminiAPI($api_key);
    }
    return $geminiAPI;
}

function action_send_message()
{
    // 从POST请求中获取JSON格式的对话数据
    $json_body = file_get_contents('php://input');
    $conversation = json_decode($json_body, true);

    // 检查是否成功解析 JSON 数据
    if ($conversation === null) {
        // JSON 解析失败
        outputJson(['error' => 'Invalid JSON data']);
        return;
    }

    $geminiAPI = getGeminiAPI();

    // 调用 GeminiAPI 实例的 chat 方法，传递对话数据
    outputJson($geminiAPI->chat($conversation));
}
