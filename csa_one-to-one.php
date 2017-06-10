<?php

declare(strict_types=1);

$stops = ['S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8'];
$trips = ['T1', 'T2', 'T3', 'T4', 'T5'];

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
        'to' => 'S4',
        'departure' => 10,
        'arrival' => 20,
        'trip' => 'T1',
        'change_time' => 4,
    ],
    [
        'from' => 'S4',
        'to' => 'S5',
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
        'to' => 'S4',
        'departure' => 20,
        'arrival' => 30,
        'trip' => 'T2',
        'change_time' => 4,
    ],
    [
        'from' => 'S4',
        'to' => 'S5',
        'departure' => 30,
        'arrival' => 47,
        'trip' => 'T2',
        'change_time' => 4,
    ],
    // T3
    [
        'from' => 'S2',
        'to' => 'S4',
        'departure' => 25,
        'arrival' => 35,
        'trip' => 'T3',
        'change_time' => 4,
    ],
    [
        'from' => 'S4',
        'to' => 'S5',
        'departure' => 35,
        'arrival' => 45,
        'trip' => 'T3',
        'change_time' => 4,
    ],
    // T4
    [
        'from' => 'S2',
        'to' => 'S4',
        'departure' => 25,
        'arrival' => 32,
        'trip' => 'T4',
        'change_time' => 4,
    ],
    [
        'from' => 'S4',
        'to' => 'S5',
        'departure' => 32,
        'arrival' => 38,
        'trip' => 'T4',
        'change_time' => 4,
    ],
    // T5
//    [
//        'from' => 'S7',
//        'to' => 'S5',
//        'departure' => 25,
//        'arrival' => 35,
//        'trip' => 'T5',
//        'change_time' => 4,
//    ],
//    [
//        'from' => 'S5',
//        'to' => 'S8',
//        'departure' => 35,
//        'arrival' => 45,
//        'trip' => 'T5',
//        'change_time' => 5,
//    ],
    // T6
//    [
//        'from' => 'S2',
//        'to' => 'S5',
//        'departure' => 17,
//        'arrival' => 50,
//        'trip' => 'T6',
//        'change_time' => 4,
//    ],
];

function getConnectionId(int $cId, array $c): string
{
    return sprintf('%2d/%s@%2d->%s@%2d', $cId, $c['from'], $c['departure'], $c['to'], $c['arrival']);
}

function printTrip(string $tripId)
{
    global $connections;

    echo $tripId . ': ';

    $prevC = null;
    foreach ($connections as $cI => $c) {
        if ($c['trip'] != $tripId) continue;

        if ($prevC != null) {
            echo str_repeat(' ', $c['departure'] - $prevC['arrival']);
        } else {
            if ($c['departure'] != 0) echo str_repeat(' ', 17);
            echo str_repeat(' ', $c['departure']);
        }

        printf('%s %s ', getConnectionId($cI, $c), str_repeat('=', $c['arrival'] - $c['departure']));

        $prevC = $c;
    }

    echo PHP_EOL . PHP_EOL;
}

// sort by departure
usort($connections, function ($c1, $c2) {
    return $c1['departure'] - $c2['departure'];
});

foreach ($trips as $t) {
    printTrip($t);
}
//print_r($connections); die();

$earliestArrival = array_fill_keys($stops, INF);
$inConnection = array_fill_keys($stops, null);
$tripReachability = array_fill_keys($trips, false);

// input
$from = 'S2';
$to = 'S5';
$departureTimestamp = 11;

printf("Depart from %s to %s at %d\n\n", $from, $to, $departureTimestamp);

// --------------------------

$earliestArrival[$from] = $departureTimestamp;

foreach ($connections as $cI => $c) {
    printf("Inspecting C %s on %s\n", getConnectionId($cI, $c), $c['trip']);

    // A connection C is reachable, iff either a passenger:
    //  a) has already been on another connection of the same trip T
    //  b) is standing at the C's departure stop on time
    $isReachable = true === $tripReachability[$c['trip']]
        || $c['departure'] >= ($earliestArrival[$c['from']] + $c['change_time']);

    // Does using C improve the EAT of C's arrival stop?
    $improvesArrivalTime = $c['arrival'] < $earliestArrival[$c['to']];

    printf("isReachable = %s, improves = %s\n", var_export($isReachable, true), var_export($improvesArrivalTime, true));

    if ($isReachable && $improvesArrivalTime) {
        print("Take it [x]\n");

        $tripReachability[$c['trip']] = true;
        $earliestArrival[$c['to']] = $c['arrival'];
        $inConnection[$c['to']] = $cI;
    }

    echo PHP_EOL;
}

echo PHP_EOL;
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
