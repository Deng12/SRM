<?php

return [
    'consul' => [
        'order'=>[
            'address' => '192.168.232.204',
            'port'    => 8550,
            'register' => [
                'ID'                =>'order_1',
                'Name'              =>'order',
                'Tags'              =>['xdp-/core.order'],
                'Address'           =>'192.168.232.201',
                'Port'              =>18307,
                'Check'             => [
                    'name'    =>'order_1.check',
                    'tcp'      => '192.168.232.201:18307',
                    'interval' => '10s',
                    'timeout'  => '2s',
                ]
            ],
            'discovery' => [
                'name' => 'order',
                'dc' => 'xdp_dc',
                'passing' => true
            ]
        ],
        'goods'=>[
            'address' => '192.168.232.204',
            'port'    => 8550,
            'register' => [
                'ID'                =>'goods_1',
                'Name'              =>'goods',
                'Tags'              =>['xdp-/core.product'],
                'Address'           =>'192.168.232.200',
                'Port'              =>18307,
                'Check'             => [
                    'name'    =>'goods_1.check',
                    'tcp'      => '192.168.232.200:18307',
                    'interval' => '10s',
                    'timeout'  => '2s',
                ]
            ],
            'discovery' => [
                'name' => 'goods',
                'dc' => 'xdp_dc',
                'passing' => true
            ]
        ]
    ],
];
