<?php

// 优惠规则映射
$discountMap = array(
    100 => 20,
    50 => 10,
    20 => 1,
    10 => 0
);

// 周卡月卡规则映射
$subscriptionMap = array(
    "W" => array("name" => "周卡", "price" => 19, "days" => 7),
    "M" => array("name" => "月卡", "price" => 39, "days" => 31)
);

return array(
    'discountMap' => $discountMap,
    'subscriptionMap' => $subscriptionMap
);
