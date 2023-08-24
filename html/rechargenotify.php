<?php

require_once 'lib.php';
require_once 'logic.php';

// Log the entire POST body
$body = file_get_contents('php://input');
error_log("rechargenotify.php Received POST body:\n" . $body);

if ($_POST['code'] != 0) {
    http_response_code(500);
    echo "Error occurred";
    return;
}

$requestData = $_POST;

$code = $requestData['code'];
$timestamp = $requestData['timestamp'];
$mch_id = $requestData['mch_id'];
$order_no = $requestData['order_no'];
$out_trade_no = $requestData['out_trade_no'];
$pay_no = $requestData['pay_no'];
$total_fee = $requestData['total_fee'];

$data = array(
    'code' => $code,
    'timestamp' => $timestamp,
    'mch_id' => $mch_id,
    'order_no' => $order_no,
    'out_trade_no' => $out_trade_no,
    'pay_no' => $pay_no,
    'total_fee' => $total_fee
);

if (sign($data, '6932ff1c91c71e872d40453eec4263f7') !== $requestData['sign']) {
    http_response_code(500);
    echo "bad sign";
    return;
}

try {
    $pdo = getInitializedPDO();

    $orderNumber = $requestData['out_trade_no'];

    // Check if the order exists
    $orderDetails = getOrderDetails($orderNumber);

    if (!$orderDetails) {
        error_log("Order not found: $orderNumber");
        http_response_code(404);
        echo "Order not found";
        exit;
    }

    if ($orderDetails['is_paid'] == 1) {
        // Order is already paid, no further processing needed
        http_response_code(200);
        echo "SUCCESS";
        exit;
    }

    // Calculate the amount in cents (total_fee * 100)
    $amountInCents = $orderDetails['total_fee'] * 100;

    // Start a transaction
    $pdo->beginTransaction();

    // Recharge user balance
    rechargeUserBalance($orderDetails['attach'], $amountInCents);

    // Update payment status
    updatePaymentStatus($orderNumber);

    // Commit the transaction
    $pdo->commit();

    // Send a successful response
    http_response_code(200);
    echo "SUCCESS";
} catch (Exception $e) {
    // If an error occurs, rollback the transaction
    if (isset($pdo)) {
        $pdo->rollback();
    }

    // Log the error
    error_log("Error: " . $e->getMessage());

    // Send an error response
    http_response_code(500);
    echo "Error occurred";
}
