<?php

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.");
}

define('DATA_ROOT', dirname(__DIR__) . '/data');

require_once dirname(__DIR__) . '/html/logic.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// 获取前一天的日期
$yesterday = date('Y-m-d', strtotime('-1 day'));

// 构建文件路径
$filePath = DATA_ROOT . "/points-$yesterday.txt";

// 检查文件是否存在
if (file_exists($filePath)) {
    // 读取文件内容
    $fileContent = file_get_contents($filePath);

    // 将文件内容转换为数字
    $pointsFromFile = intval($fileContent);
} else {
    // 文件不存在时，默认值为0
    $pointsFromFile = 0;
}

$sql = "SELECT SUM(used_points) AS total_points FROM users ";
$params = [];
$stmt = executePreparedStmt($sql, $params);
$result = $stmt->fetchAll();

// 获取查询结果
$totalPointsFromDB = $result[0]['total_points'];

$today = date('Y-m-d', strtotime('now'));

// 构建文件路径
$filePath = DATA_ROOT . "/points-$today.txt";

file_put_contents($filePath, $totalPointsFromDB);

// 计算最终结果
$finalPoints = -$pointsFromFile + $totalPointsFromDB;

// 输出最终结果
echo "前一天的数据为: $finalPoints";


// 设置阈值
$threshold = 100 * 100;
$threshold = 1;

// 发送告警邮件
if ($totalPoints > $threshold) {
    // 发送邮件的代码，这里使用 PHPMailer 作为示例
    $mail = new PHPMailer(true);

    try {
        // 邮件服务器配置
        $mail->isSMTP();
        $mail->Host       = 'your_smtp_server';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_smtp_username';
        $mail->Password   = 'your_smtp_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // 邮件内容
        $mail->setFrom('your_email@example.com', 'Your Name');
        $mail->addAddress('recipient@example.com', 'Recipient Name');
        $mail->isHTML(true);
        $mail->Subject = 'Warning: Exceeded Points Threshold';
        $mail->Body    = "Total points on $yesterday exceeded the threshold. Total Points: $totalPoints";

        // 发送邮件
        $mail->send();

        echo 'Alert email sent successfully!';
    } catch (Exception $e) {
        echo "Error sending alert email: {$mail->ErrorInfo}";
    }
} else {
    echo 'No alert needed. Total points within threshold.';
}