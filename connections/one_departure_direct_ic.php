<?php

declare(strict_types=1);

$connections = [
    [
        'from' => 'S',
        'to' => 'Z',
        'departure' => 5,
        'arrival' => 8,
        'trip' => 'T1',
        'change_time' => 0,
    ],
    [
        'from' => 'Z',
        'to' => 'T',
        'departure' => 9,
        'arrival' => 13,
        'trip' => 'T1',
        'change_time' => 0,
    ],
    [
        'from' => 'Z',
        'to' => 'X',
        'departure' => 9,
        'arrival' => 10,
        'trip' => 'T2',
        'change_time' => 0,
    ],
    [
        'from' => 'X',
        'to' => 'T',
        'departure' => 11,
        'arrival' => 12,
        'trip' => 'T2',
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
