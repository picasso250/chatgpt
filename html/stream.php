<?php

require_once 'lib.php';
require_once 'logic.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: text/event-stream");
header("X-Accel-Buffering: no");
set_time_limit(0);
session_start();
$postData = $_SESSION['data'];
$responsedata = "";
$OPENAI_API_KEY = "";

// 获取用户名
$username = getUsernameFromCookie();

// 在数据库中插入或更新用户
$user = insertOrUpdateUser($username);

if ($user['balance'] <= 0) {
    $msg = json_encode(['error' => ['code' => 42, 'message' => '余额不足']]);
    setcookie("errcode", "insufficient_balance");
    die("data: $msg\n\n\n\n");
}


//如果首页开启了输入自定义apikey，则采用用户输入的apikey
if (isset($_SESSION['key'])) {
    $OPENAI_API_KEY = $_SESSION['key'];
}
session_write_close();

setcookie("errcode", ""); //EventSource无法获取错误信息，通过cookie传递
setcookie("errmsg", "");

$price = 0;

$callback = function ($ch, $data) use ($user, $postData, &$price) {
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
        log_data($data);
        $pattern = '/^data: \\[DONE\\]/m';
        log_data("!!!" . (preg_match($pattern, $data)));
        if (preg_match($pattern, $data)) {
            $responsedata .= $data;
            $answer = build_answer($responsedata);

            putenv("https_proxy="); // unset the https_proxy environment variable

            $token_size = strlen($postData . $answer);

            $pricePerToken = 0.003 / 1e3 * 7.3;

            $price = $pricePerToken * $token_size;
            $price *= 100;
            if ($price < 1) {
                $price = 1;
            }

            // Save the updated balance back to the user's data
            $newBalance = deductUserBalance($user['id'], $price);
            $datanb = json_encode(["newBalance" => $newBalance]);
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

$configFile = '../config/config.ini';
$appConfig = parse_ini_file($configFile);

if (!empty($appConfig['https_proxy'])) {
    putenv("https_proxy=$appConfig[https_proxy]");
}
$OPENAI_API_KEY = $appConfig['OPENAI_API_KEY'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $OPENAI_API_KEY,
));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
log_data($postData);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, $callback);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 0);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
curl_close($ch);

function build_answer($responsedata)
{
    $answer = "";
    if (substr(trim($responsedata), -6) == "[DONE]") {
        $responsedata = substr(trim($responsedata), 0, -6) . "{";
    }
    $responsearr = explode("}\n\ndata: {", $responsedata);

    foreach ($responsearr as $msg) {
        $contentarr = json_decode("{" . trim($msg) . "}", true);
        if (isset($contentarr['choices'][0]['delta']['content'])) {
            $answer .= $contentarr['choices'][0]['delta']['content'];
        }
    }
    return $answer;
}
$answer = build_answer($responsedata);
$questionarr = json_decode($postData, true);

addChatLog($_SESSION['user_ses']['id'], $questionarr, $answer, $price);
