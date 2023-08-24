<?php

require_once 'lib.php';
require_once 'logic.php';

// Log the entire POST body
$body = file_get_contents('php://input');
error_log("echargenotify.php Received POST body:\n" . $body);

if ($_POST['code'] != 0) {
    http_response_code(500);
    echo "Error occurred";
    return;
}

try {
    $pdo = getInitializedPDO();

    $requestData = $_POST;

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
