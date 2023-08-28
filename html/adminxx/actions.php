<?php

require_once '../lib.php';
require_once '../logic.php';


function action_Recharge()
{

    // 获取user_id或username和point
    $user_id = _post('user_id');
    $username = _post('username');
    $point = _post('point');

    // Check if either user_id or username is provided
    if ((!empty($user_id) || !empty($username)) && is_numeric($point)) {

        // If username is provided, get user_id using getUsernameById function
        if (!empty($username)) {
            $user = getUserByUsername($username);
            if ($user)
                $user_id = $user['id'];
        }

        // If user_id is available, proceed with the recharge logic
        if (!empty($user_id)) {

            // Use getUserById function to get user information
            $user = getUserById($user_id);

            // Check if user exists
            if ($user) {
                // Perform the recharge operation, you can add recharge records to the database or any other persistence operation
                $result = rechargeUser($user_id, $point);

                if ($result) {
                    echo json_encode(['data' => "用户 $user[username] 充值成功。已添加积分：$point"]);
                } else {
                    echo json_encode(['error' => "Failed to recharge user $user_id."]);
                }
            } else {
                // If the user does not exist, return an error message
                echo json_encode(['error' => "User $user_id does not exist."]);
            }
        } else {
            // If neither user_id nor username is valid, return an error message
            echo json_encode(['error' => 'Invalid user_id or username.']);
        }
    } else {
        // If the user_id or point is missing or not numeric, return an error message
        echo json_encode(['error' => 'Invalid user_id or point.']);
    }
}
