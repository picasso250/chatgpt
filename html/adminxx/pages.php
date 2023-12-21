<?php
$config = [
    'users' => [
        'name' => '用户',
        'fields' => [
            'id' => [
                'name' => 'ID',
            ],
            'username' => [
                'name' => '用户名',
            ],
            'balance' => [
                'name' => '余额',
            ],
            'last_updated' => [
                'name' => '最后更新时间',
                'data' => 'datetime',
            ],
            'last_ip' => [
                'name' => '最后IP地址',
            ],
            'free_package_end' => [
                'name' => '免费套餐截止时间',
                'data' => 'datetime',
            ],
            'used_points' => [
                'name' => '使用积分',
            ],
            'referer' => [
                'name' => '来源',  // 新添加的 referer 列
            ],
            'click_recharge_dialog' => [
                'name' => '是否点击付费弹框',  // 新添加的 referer 列
                'data' => 'boolean',
            ],
            'op' => [
                'name' => '操作',
                'func' => function ($value, $key, $row, $id) {
                    return '<a class="btn" href="adminchatlog.php?uid=' . $id . '">聊天记录</a>' .
                        '<input type="number" max="100" class="input-number" style="width:4em" />' .
                        '<a class="btn recharge-btn" data-uid=' . htmlentities($id) . ' href="javascript:void(0);">充值</a>' .
                        '<a class="btn" href="orders.php?username=' . htmlentities($row['username']) . '">查看订单</a>';
                },
                'sort' => false,
            ],
        ],
    ],
    'orders' => [
        'name' => '订单',
        'fields' => [
            'id' => [
                'name' => 'ID',
            ],
            'order_time' => [
                'name' => '订单时间',
                'data' => 'datetime',
            ],
            'order_number' => [
                'name' => '订单编号',
            ],
            'payment_amount' => [
                'name' => '支付金额',
            ],
            'username' => [
                'name' => '用户名',
                'func' => function ($value, $key, $row, $id) {
                    $params = ['username' => $value];
                    $queryParams = http_build_query($params);
                    $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    return '<a href="users.php?' . $queryParams . '">' . $escapedValue . '</a>';
                },
            ],
            'user_id' => [
                'name' => '用户ID',
            ],
            'request_id' => [
                'name' => '请求ID',
            ],
            'is_paid' => [
                'name' => '是否支付',
                'data' => 'boolean',
            ],
            'payment_success_time' => [
                'name' => '支付成功时间',
                'data' => 'datetime',
            ],
            'subscription_name' => [
                'name' => '订阅名称',
            ],
        ],
    ],
];

return $config;
