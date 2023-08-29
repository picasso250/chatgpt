<?php

require_once 'lib.php';
require_once 'logic.php';

$start_time = microtime(true);

header("Access-Control-Allow-Origin: *");
header("Content-Type: text/event-stream");
header("X-Accel-Buffering: no");
set_time_limit(0);

session_start();

$configFile = '../config/config.ini';
$appConfig = parse_ini_file($configFile);

$responsedata = "";
$OPENAI_API_KEY = "";

$user_id = $_SESSION['user_ses']['id'];
$user = getUserById($user_id);

$dontDeductFlag = false;

// Check if free_package_end is null
if ($user['free_package_end'] === null) {
    if ($user['balance'] < 0) {
        $msg = json_encode(['error' => ['code' => 42, 'message' => '余额不足']]);
        setcookie("errcode", "insufficient_balance");
        die("data: $msg\n\n\n\n");
    }
}
// Check if free_package_end is expired
elseif (isFreePackageExpired($user['free_package_end'])) {
    if ($user['balance'] < 0) {
        $msg = json_encode(['error' => ['code' => 42, 'message' => '余额不足']]);
        setcookie("errcode", "insufficient_balance");
        die("data: $msg\n\n\n\n");
    }
} else {
    // Set "不扣款" flag to true
    $dontDeductFlag = true;
}

// Convert UTC free_package_end to a DateTime object
$freePackageEndUTC = new DateTime($user['free_package_end'], new DateTimeZone('UTC'));

// Get the current UTC date and time
$currentUTC = new DateTime('now', new DateTimeZone('UTC'));

if ($freePackageEndUTC <= $currentUTC) {
    $msg = json_encode(['error' => ['code' => 43, 'message' => '免费套餐已过期']]);
    setcookie("errcode", "free_package_expired");
    die("data: $msg\n\n\n\n");
}

// 从GET参数获取conversation_id
$conversationId = isset($_GET['conversation_id']) ? $_GET['conversation_id'] : 0;
$message = isset($_GET['message']) ? $_GET['message'] : '';
$model = "gpt-35-turbo";

// 如果conversation_id为0，则创建一个新的对话
if ($conversationId == 0) {
    $conversationId =  createConversation($user['id'], $model, $message);
    echo "data: " . json_encode(['conversation_id' => $conversationId]) . "\n\n";
}

// 获取指定conversation_id的所有conversation_records
$records = getConversationRecords($conversationId);

// 获取GET参数中的message，并与conversation_records结合构建messages数组
$messages = [
    ["role" => "system", "content" => "You are a helpful assistant."]
];
foreach ($records as $record) {
    $messages[] = ['role' => 'user', 'content' => $record['user_message']];
    $messages[] = ['role' => 'assistant', 'content' => $record['assistant_message']];
}
$messages[] = ['role' => 'user', 'content' => $message];

// 构建postData数组
$postData = [
    "temperature" => isset($_GET['temperature']) ? floatval($_GET['temperature']) : 0,
    "stream" => true,
    "messages" => $messages,
];

$api_url = $appConfig['END_POINT_URL'] . '/openai/deployments/gpt35turbo/chat/completions?api-version=2023-05-15';

$token_size = strlen(json_encode($postData, JSON_UNESCAPED_UNICODE));
if ($token_size > 4000) {
    $model = 'gpt-35-turbo-16k';
    $api_url = $appConfig['END_POINT_URL'] . '/openai/deployments/gtp35turbo16k/chat/completions?api-version=2023-05-15';
}

if ($appConfig['OPENAI_TYPE'] == 'OPENAI') {
    $postData["model"] = $model;
}

setcookie("errcode", ""); //EventSource无法获取错误信息，通过cookie传递
setcookie("errmsg", "");

$price = 0;

$callback = function ($ch, $data) use ($user, $postData, &$price, $start_time, $dontDeductFlag) {

    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time) * 1000;
    error_log("运行时间：$execution_time 毫秒");

    global $responsedata;
    $complete = json_decode($data);
    if (isset($complete->error)) {
        setcookie("errcode", $complete->error->code);
        setcookie("errmsg", $data);
        if (strpos($complete->error->message, "Rate limit reached") === 0) { //访问频率超限错误返回的code为空，特殊处理一下
            setcookie("errcode", "rate_limit_reached");
        }
        if (strpos($complete->error->message, "Your access was terminated") === 0) { //违规使用，被封禁，特殊处理一下
            setcookie("errcode", "access_terminated");
        }
        if (strpos($complete->error->message, "You didn't provide an API key") === 0) { //未提供API-KEY
            setcookie("errcode", "no_api_key");
        }
        if (strpos($complete->error->message, "You exceeded your current quota") === 0) { //API-KEY余额不足
            setcookie("errcode", "insufficient_quota");
        }
        if (strpos($complete->error->message, "That model is currently overloaded") === 0) { //OpenAI模型超负荷
            setcookie("errcode", "model_overloaded");
        }
        $responsedata = $data;
    } else {
        log_debug($data);
        $pattern = '/^data: \\[DONE\\]/m';
        if (preg_match($pattern, $data) && !$dontDeductFlag) {
            $responsedata .= $data;
            $answer = build_answer($responsedata);

            putenv("https_proxy="); // unset the https_proxy environment variable

            $token_size = strlen(json_encode($postData, JSON_UNESCAPED_UNICODE) . $answer);

            $pricePerToken = 0.004 / 1e3 * 7.3 * 1.4; // factor

            $price = $pricePerToken * $token_size;
            $price *= 100;
            if ($price < 1) {
                $price = 1;
            }

            // Save the updated balance back to the user's data
            $newBalance = deductUserBalance($user['id'], $price);
            $datanb = json_encode(["newBalance" => $newBalance]);
            log_debug(var_export(preg_replace($pattern, "data: $datanb\n\n" . 'data: [DONE]', $data)));
            echo preg_replace($pattern, "data: $datanb\n\n" . 'data: [DONE]', $data);
            flush();
        } else {
            echo $data;
            $responsedata .= $data;
            flush();
        }
    }
    return strlen($data);
};

if (!empty($appConfig['https_proxy'])) {
    putenv("https_proxy=$appConfig[https_proxy]");
}
$OPENAI_API_KEY = $appConfig['OPENAI_API_KEY'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$headers = array(
    'Content-Type: application/json',
);
if ($appConfig['OPENAI_TYPE'] == 'AZURE') {
    $headers[] = 'api-key: ' . $OPENAI_API_KEY;
} else {
    $headers[] = 'Authorization: Bearer ' . $OPENAI_API_KEY;
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData, JSON_UNESCAPED_UNICODE));
log_debug(json_encode($postData, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_WRITEFUNCTION, $callback);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 0);
// curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
curl_close($ch);

$answer = build_answer($responsedata);

addConversationRecord($conversationId, $message, $answer, $price);
