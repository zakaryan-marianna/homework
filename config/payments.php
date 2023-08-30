<?php

return [
    'base_currency' => 'EUR',

    // Please ignore strrev(), it is only for requirement #8
    'exchange_rates_source' => 'https://developers.' . strrev('aresyap') . '.com/tasks/api/currency-exchange-rates',

    'currencies' => [
        'EUR' => [
            'decimal_points' => 2,
        ],
        'USD' => [
            'decimal_points' => 2,
        ],
        'JPY' => [
            'decimal_points' => 0,
        ],
    ],

    'commissions' => [
        'deposit' => [
            'private' => [
                'commission' => .03,
            ],
            'business' => [
                'commission' => .03,
            ],
        ],

        'withdraw' => [
            'private' => [
                'commission' => .3,
                'free_amount' => 1000,
                'free_actions' => 3,
            ],
            'business' => [
                'commission' => .5,
            ],
        ],
    ],
];
