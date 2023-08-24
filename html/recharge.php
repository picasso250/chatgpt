<?php


require_once 'lib.php';
require_once 'logic.php';

session_start();

$configFile = '../config/config.ini';
$appConfig = parse_ini_file($configFile);

$user_id = $_SESSION['user_ses']['id'];
$username = $_COOKIE['username'];

$amount = $_GET['amount']; // 获取充值金额参数

if (!is_numeric($amount) || $amount <= 0 || $amount > 100) {
    // 校验金额是否合法
    header('Location: error.php'); // 重定向到错误页面
    exit();
}

$curl = curl_init();

// Generate the unique order number
$out_trade_no = "L" . date("YmdHis")  . uniqid();

// Get the current timestamp
$timestamp = time();

// Set the total fee
$total_fee = $amount;

// Set up the request parameters
$data = [
    "mch_id" => "1651412424",
    "out_trade_no" => $out_trade_no,
    "total_fee" => $total_fee,
    "body" => "人工智能小助手",
    "timestamp" => $timestamp,
    "notify_url" => "https://chatgptmagic.net/rechargenotify.php"
];

$data['sign'] = sign($data, '6932ff1c91c71e872d40453eec4263f7');

$data["time_expire"] = "5m";
$data["attach"] = (string)$username;

// Convert the data to a URL-encoded string
$data_string = http_build_query($data);

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.ltzf.cn/api/wxpay/native",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $data_string,
    CURLOPT_HTTPHEADER => [
        "content-type: application/x-www-form-urlencoded"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

// Log the request details and response
error_log("Request URL: " . "https://api.ltzf.cn/api/wxpay/native" . "\nRequest Parameters: " . $data_string . "\nResponse: " . $response);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    // echo $response;

    // Decode the JSON response
    $responseData = json_decode($response, true);

    // Get the QR code URL from the data
    $qrCodeUrl = $responseData['data']['QRcode_url'];

    // Display the QR code image
    // echo '<img src="' . $qrCodeUrl . '" alt="QR Code">';

    // Insert order information into the database
    $orderData = [
        'order_time' => date('Y-m-d H:i:s'),
        'order_number' => $out_trade_no,
        'payment_amount' => $total_fee,
        'username' => $username,
        'user_id' => $user_id,
        'request_id' => $responseData['data']['request_id'],
        'is_paid' => 0 // Order is not paid yet
    ];

    insertIntoTable('orders', $orderData);

    include 'recharge.html';
}
