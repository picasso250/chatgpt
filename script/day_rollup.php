<?php

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.");
}

define('DATA_ROOT', dirname(__DIR__) . '/data');

require_once dirname(__DIR__) . '/html/logic.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require dirname(__DIR__). '/PHPMailer/src/Exception.php';
require dirname(__DIR__). '/PHPMailer/src/PHPMailer.php';
require dirname(__DIR__). '/PHPMailer/src/SMTP.php';

// 设置时区为+8:00
$setTimezoneSql = "SET time_zone = '+08:00'";
$setTimezoneParams = [];
executePreparedStmt($setTimezoneSql, $setTimezoneParams);

// 获取前一天的日期
$yesterdaySql = "SELECT CURDATE() - INTERVAL 1 DAY AS yesterday";
$yesterdayParams = [];
$yesterdayStmt = executePreparedStmt($yesterdaySql, $yesterdayParams);
$yesterdayResult = $yesterdayStmt->fetchAll();
$yesterday = $yesterdayResult[0]['yesterday'];

// 获取前天的日期
$dayBeforeYesterdaySql = "SELECT CURDATE() - INTERVAL 2 DAY AS day_before_yesterday";
$dayBeforeYesterdayParams = [];
$dayBeforeYesterdayStmt = executePreparedStmt($dayBeforeYesterdaySql, $dayBeforeYesterdayParams);
$dayBeforeYesterdayResult = $dayBeforeYesterdayStmt->fetchAll();
$dayBeforeYesterday = $dayBeforeYesterdayResult[0]['day_before_yesterday'];

// 获取昨天的总积分
$yesterdayTotalPointsSql = "SELECT SUM(used_points) AS total_points FROM users ";
$yesterdayTotalPointsParams = [];
$yesterdayTotalPointsStmt = executePreparedStmt($yesterdayTotalPointsSql, $yesterdayTotalPointsParams);
$yesterdayTotalPointsResult = $yesterdayTotalPointsStmt->fetchAll();
$yesterdayTotalPoints = $yesterdayTotalPointsResult[0]['total_points'];

// 将昨天的结果插入到 statistics 表中
$insertYesterdaySql = "INSERT INTO statistics (total_points, date) VALUES (:total_points, :date)";
$insertYesterdayParams = [
    ':total_points' => $yesterdayTotalPoints,
    ':date' => $yesterday,
];

executePreparedStmt($insertYesterdaySql, $insertYesterdayParams);

// 获取前天的总积分
$dayBeforeYesterdayTotalPointsSql = "SELECT total_points FROM statistics WHERE date = :date";
$dayBeforeYesterdayTotalPointsParams = [':date' => $dayBeforeYesterday];
$dayBeforeYesterdayTotalPointsStmt = executePreparedStmt($dayBeforeYesterdayTotalPointsSql, $dayBeforeYesterdayTotalPointsParams);
$dayBeforeYesterdayTotalPointsResult = $dayBeforeYesterdayTotalPointsStmt->fetchAll();
$dayBeforeYesterdayTotalPoints = $dayBeforeYesterdayTotalPointsResult[0]['total_points'];

// 计算差值
$difference = $yesterdayTotalPoints - $dayBeforeYesterdayTotalPoints;

// 输出差值
echo "Difference between $yesterday and $dayBeforeYesterday: $difference\n";

// Read username and password from mail.ini
$config = parse_ini_file('mail.ini', true);

// Accessing values
$username = $config['credentials']['username'];
$password = $config['credentials']['password'];

// 将mail_list_main字符串拆分成数组
$mailListArray = explode(',', $config['mail_list']['mail_list_main']);

// 设置阈值
$threshold = 100 * 100;

// 发送告警邮件
if ($difference > $threshold) {
    // 发送邮件的代码，这里使用 PHPMailer 作为示例
    $mail = new PHPMailer(true);

    try {
        // 邮件服务器配置
        $mail->isSMTP();
        $mail->Host       = 'smtp-mail.outlook.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // 邮件内容
        $mail->setFrom($username, $username);
        // 遍历数组，添加每个地址
        foreach ($mailListArray as $email) {
            $mail->addAddress(trim($email));
        }
        $mail->isHTML(true);
        $mail->Subject = 'Warning: Exceeded Points Threshold';
        $mail->Body    = "Total points on $yesterday exceeded the threshold. Total Points: $difference";

        // 发送邮件
        $mail->send();

        echo 'Alert email sent successfully!';
    } catch (Exception $e) {
        echo "Error sending alert email: {$mail->ErrorInfo}";
    }
} else {
    echo 'No alert needed. Total points within threshold.';
}
