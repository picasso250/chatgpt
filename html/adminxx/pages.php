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
                'data' => 'time',
            ],
            'last_ip' => [
                'name' => '最后IP地址',
            ],
            'free_package_end' => [
                'name' => '免费套餐截止时间',
            ],
            'op' => [
                'name' => '操作',
                'func' => function ($value, $key, $row, $id) {
                    return '<a class="btn" href="adminchatlog.php?uid=' . $id . '">聊天记录</a>' .
                        '<a class="btn" href="adminxchatlog.php?uid=' . $id . '">充值</a>';
                }
            ],
        ],
    ],
];

return $config;
