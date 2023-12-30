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

function action_send_message_stream()
{
    // Set the appropriate headers for EventSource
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    $base64Message = $_GET['message'];

    // Decode the base64 encoded JSON
    $jsonMessage = base64_decode($base64Message);
    $conversation = json_decode($jsonMessage, true);

    // 检查是否成功解析 JSON 数据
    if ($conversation === null) {
        // JSON 解析失败
        outputEventSource('error', 'Invalid JSON data');
        return;
    }

    $geminiAPI = getGeminiAPI();

    // 调用 GeminiAPI 实例的 chatWithStreaming 方法，传递对话数据和回调函数
    $geminiAPI->chatWithStreaming($conversation, 'customStreamingCallback');

    // Output a message indicating the end of the streaming
    outputEventSource('end', '[DONE]');
}

// Define a custom streaming callback function
function customStreamingCallback($ch, $capturedText)
{
    // Output the captured text as an EventSource message
    outputEventSource('message', '{'.$capturedText.'}');
}

// Function to output EventSource messages
function outputEventSource($eventType, $data)
{
    echo "data: $data\n\n";
    ob_flush();
    flush();
}
