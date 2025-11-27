<?php
declare(strict_types=1);

return [
    'defaultCurrency' => 'IRR',
    'localCurrency' => 'IRT',
    'currencies' => [
        'IRR' => [
            'decimals' => 0,
            'decimalPoint' => '٫',
            'thousandsSep' => '٬',
            'symbolLeft' => '',
            'symbolRight' => 'ریال',
        ],
        'IRT' => [
            'decimals' => 0,
            'decimalPoint' => '٫',
            'thousandsSep' => '٬',
            'symbolLeft' => '',
            'symbolRight' => 'تومان',
        ],
    ],
    'exchange' => [
        'IRR' => [
            'IRT' => 0.1,
        ],
        'IRT' => [
            'IRR' => 10,
        ],
    ],
];
