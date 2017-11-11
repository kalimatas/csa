<?php

declare(strict_types=1);

$connections = [
    // T1
    [
        'from' => 'S1',
        'to' => 'S2',
        'departure' => 0,
        'arrival' => 10,
        'trip' => 'T1',
        'change_time' => 4,
    ],
    [
        'from' => 'S2',
        'to' => 'S3',
        'departure' => 10,
        'arrival' => 20,
        'trip' => 'T1',
        'change_time' => 4,
    ],
    [
        'from' => 'S3',
        'to' => 'S4',
        'departure' => 20,
        'arrival' => 30,
        'trip' => 'T1',
        'change_time' => 4,
    ],
    // T2
    [
        'from' => 'S1',
        'to' => 'S2',
        'departure' => 10,
        'arrival' => 20,
        'trip' => 'T2',
        'change_time' => 4,
    ],
    [
        'from' => 'S2',
        'to' => 'S3',
        'departure' => 20,
        'arrival' => 30,
        'trip' => 'T2',
        'change_time' => 4,
    ],
    [
        'from' => 'S3',
        'to' => 'S4',
        'departure' => 30,
        'arrival' => 47,
        'trip' => 'T2',
        'change_time' => 4,
    ],
    [
        'from' => 'S4',
        'to' => 'S5',
        'departure' => 49,
        'arrival' => 60,
        'trip' => 'T2',
        'change_time' => 4,
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
