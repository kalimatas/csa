<?php

declare(strict_types=1);

$connections = [
    [
        'from' => 'S',
        'to' => 'Z',
        'departure' => 7,
        'arrival' => 8,
        'trip' => 'T1',
        'change_time' => 0,
    ],
    [
        'from' => 'Z',
        'to' => 'T',
        'departure' => 9,
        'arrival' => 12,
        'trip' => 'T2',
        'change_time' => 0,
    ],
    [
        'from' => 'S',
        'to' => 'X',
        'departure' => 6,
        'arrival' => 7,
        'trip' => 'T3',
        'change_time' => 0,
    ],
    [
        'from' => 'X',
        'to' => 'Y',
        'departure' => 8,
        'arrival' => 9,
        'trip' => 'T4',
        'change_time' => 0,
    ],
    [
        'from' => 'Y',
        'to' => 'T',
        'departure' => 10,
        'arrival' => 11,
        'trip' => 'T5',
        'change_time' => 0,
    ],
    [
        'from' => 'S',
        'to' => 'T',
        'departure' => 5,
        'arrival' => 14,
        'trip' => 'T6',
        'change_time' => 0,
    ],
    [
        'from' => 'X',
        'to' => 'T',
        'departure' => 8,
        'arrival' => 13,
        'trip' => 'T7',
        'change_time' => 0,
    ],
];

$stops = [];
$trips = [];
foreach ($connections as $c) {
    $stops[$c['from']] = true;
    $stops[$c['to']] = true;
    $trips[$c['trip']] = true;
}

$stops = array_keys($stops);
$trips = array_keys($trips);
