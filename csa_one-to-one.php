<?php

$stops = [
    1 => 'A',
    2 => 'B',
    3 => 'C',
    4 => 'D',
    5 => 'E',
    6 => 'F',
    7 => 'H',
    8 => 'I',
];

$trips = [
    1 => 'T1',
    2 => 'T2',
    3 => 'T3',
    4 => 'T4',
    5 => 'T5',
];

$connections = [
    // T1
    [
        'from' => 'A',
        'to' => 'B',
        'departure' => 0,
        'arrival' => 10,
        'trip' => 'T1',
        'change_time' => 4,
    ],
    [
        'from' => 'B',
        'to' => 'D',
        'departure' => 10,
        'arrival' => 20,
        'trip' => 'T1',
        'change_time' => 4,
    ],
    [
        'from' => 'D',
        'to' => 'E',
        'departure' => 20,
        'arrival' => 30,
        'trip' => 'T1',
        'change_time' => 4,
    ],
    // T2
    [
        'from' => 'A',
        'to' => 'B',
        'departure' => 10,
        'arrival' => 20,
        'trip' => 'T2',
        'change_time' => 4,
    ],
    [
        'from' => 'B',
        'to' => 'D',
        'departure' => 20,
        'arrival' => 30,
        'trip' => 'T2',
        'change_time' => 4,
    ],
    [
        'from' => 'D',
        'to' => 'E',
        'departure' => 30,
        'arrival' => 47,
        'trip' => 'T2',
        'change_time' => 4,
    ],
    // T3
    [
        'from' => 'B',
        'to' => 'D',
        'departure' => 25,
        'arrival' => 35,
        'trip' => 'T3',
        'change_time' => 4,
    ],
    [
        'from' => 'D',
        'to' => 'E',
        'departure' => 35,
        'arrival' => 45,
        'trip' => 'T3',
        'change_time' => 4,
    ],
    // T4
    [
        'from' => 'B',
        'to' => 'D',
        'departure' => 25,
        'arrival' => 32,
        'trip' => 'T4',
        'change_time' => 4,
    ],
    [
        'from' => 'D',
        'to' => 'E',
        'departure' => 32,
        'arrival' => 38,
        'trip' => 'T4',
        'change_time' => 4,
    ],
    // T5
//    [
//        'from' => 'H',
//        'to' => 'E',
//        'departure' => 25,
//        'arrival' => 35,
//        'trip' => 'T5',
//        'change_time' => 4,
//    ],
//    [
//        'from' => 'E',
//        'to' => 'I',
//        'departure' => 35,
//        'arrival' => 45,
//        'trip' => 'T5',
//        'change_time' => 5,
//    ],
    // T6
//    [
//        'from' => 'B',
//        'to' => 'E',
//        'departure' => 17,
//        'arrival' => 50,
//        'trip' => 'T6',
//        'change_time' => 4,
//    ],
];

// sort by departure
usort($connections, function ($c1, $c2) {
    return $c1['departure'] - $c2['departure'];
});

//print_r($connections); die();

$earliestArrival = array_fill_keys(array_values($stops), PHP_INT_MAX);
$inConnection = array_fill_keys(array_values($stops), null);
$tripReachability = array_fill_keys(array_values($trips), false);

// input
$from = 'A';
$to = 'E';
$departureTimestamp = 5;

// --------------------------

$earliestArrival[$from] = $departureTimestamp;

foreach ($connections as $cI => $c) {
    $changeTime = false === $tripReachability[$c['trip']]
        ? $c['change_time']
        : 0;

    if ($c['departure'] >= ($earliestArrival[$c['from']] + $changeTime) && $c['arrival'] < $earliestArrival[$c['to']]) {
        $tripReachability[$c['trip']] = true;
        $earliestArrival[$c['to']] = $c['arrival'];
        $inConnection[$c['to']] = $cI;
    }
}

//print_r($earliestArrival);

if ($inConnection[$to] === null) {
    printf("No path from %s to %s at %s\n", $from, $to, $departureTimestamp);
    exit();
}

// Build path
$path = [];
$connectionIndex = $inConnection[$to];
while ($connectionIndex !== null) {
    $connection = $connections[$connectionIndex];
    array_unshift($path, $connection);
    $connectionIndex = $inConnection[$connection['from']];
}

foreach ($path as $p) {
    printf("From %s to %s [%d, %d], trip %s\n", $p['from'], $p['to'], $p['departure'], $p['arrival'], $p['trip']);
}

echo PHP_EOL;
