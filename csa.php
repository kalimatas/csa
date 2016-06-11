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
    1 => [
        'from' => 'A',
        'to' => 'B',
        'departure' => 0,
        'arrival' => 10,
        'trip' => 'T1',
        'change_time' => 4,
    ],
//    2 => [
//        'from' => 'C',
//        'to' => 'B',
//        'departure' => 10,
//        'arrival' => 18,
//        'trip' => 'T1',
//        'change_time' => 4,
//    ],
    3 => [
        'from' => 'B',
        'to' => 'D',
        'departure' => 10,
        'arrival' => 20,
        'trip' => 'T1',
        'change_time' => 4,
    ],
    9 => [
        'from' => 'D',
        'to' => 'E',
        'departure' => 20,
        'arrival' => 30,
        'trip' => 'T1',
        'change_time' => 4,
    ],
    // T2
    4 => [
        'from' => 'A',
        'to' => 'B',
        'departure' => 10,
        'arrival' => 20,
        'trip' => 'T2',
        'change_time' => 4,
    ],
    5 => [
        'from' => 'B',
        'to' => 'D',
        'departure' => 20,
        'arrival' => 30,
        'trip' => 'T2',
        'change_time' => 4,
    ],
    6 => [
        'from' => 'D',
        'to' => 'E',
        'departure' => 30,
        'arrival' => 40,
        'trip' => 'T2',
        'change_time' => 4,
    ],
//    // T3
    7 => [
        'from' => 'B',
        'to' => 'D',
        'departure' => 10,
        'arrival' => 20,
        'trip' => 'T3',
        'change_time' => 4,
    ],
    8 => [
        'from' => 'D',
        'to' => 'E',
        'departure' => 20,
        'arrival' => 28,
        'trip' => 'T3',
        'change_time' => 4,
    ],
//    // T4
//    [
//        'from' => 'C',
//        'to' => 'F',
//        'departure' => 25,
//        'arrival' => 26,
//        'trip' => 'T4',
//        'change_time' => 4,
//    ],
//    // T5
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
//    // T6
//    [
//        'from' => 'B',
//        'to' => 'E',
//        'departure' => 17,
//        'arrival' => 50,
//        'trip' => 'T6',
//        'change_time' => 4,
//    ],
];

// sort by departure desc
uasort($connections, function ($c1, $c2) {
    return $c2['departure'] - $c1['departure'];
});

//print_r($connections); die();

$profiles = array_fill_keys(array_values($stops), [
    [
        'departure_start' => PHP_INT_MAX,
        'arrival_end' => PHP_INT_MAX,
        'enter_conn' => null,
        'exit_conn' => null,
    ],
]);
$tripsEA = array_fill_keys(array_values($trips), PHP_INT_MAX);
$tripsExitConn = array_fill_keys(array_values($trips), null);

// todo: cEval? regardless of target stop
// Hange ¨ (cdeptime, min(t, P(cdepstop)[front]. arrtime))
//  an den Anfang von P(cdepstop);

// input
$from = 'A';
$to = 'E';
$departureTimestamp = -1;

// --------------------------

foreach ($connections as $cI => $c) {
    $t = PHP_INT_MAX;

    // remain seated?
    $t = min($t, $tripsEA[$c['trip']]);

    // exit?
    if ($c['to'] == $to) {
        $t = min($t, $c['arrival']);
        $tripsExitConn[$c['trip']] = $cI;
    }

    // Evaluating profile.
    // Can we exit and use a new connection?
    foreach ($profiles[$c['to']] as $pr) {
        if ($c['arrival'] <= $pr['departure_start']) {
            $t = min($t, $pr['arrival_end']);
            break;
        }
    }

    // t now contains the earliest arrival time over all journeys starting in c
    $tripsEA[$c['trip']] = $t;

    // Update the profiles
    if ($t < $profiles[$c['from']][0]['arrival_end']) {
        if ($c['departure'] == $profiles[$c['from']][0]['departure_start']) {
            $profiles[$c['from']][0] = [
                'departure_start' => $c['departure'], // todo: no need to update - it's the same
                'arrival_end' => $t,
                'enter_conn' => $cI,
                'exit_conn' => $tripsExitConn[$c['trip']],
            ];
        } else {
            array_unshift(
                $profiles[$c['from']],
                [
                    'departure_start' => $c['departure'],
                    'arrival_end' => $t,
                    'enter_conn' => $cI,
                    'exit_conn' => $tripsExitConn[$c['trip']],
                ]
            );
        }
    }
}

// P[x] is now the full profile from x to the target_stop for every stop x
print_r($profiles);
print_r($tripsEA);


return;





























//if (! isset($inConnections[$to])) {
if ($inConnections[$to] === null) {
    printf("No path from %s to %s at %s\n", $from, $to, $departureTimestamp);
    exit();
}

// Build path
$path = [];
$connectionIndex = $inConnections[$to];
while ($connectionIndex !== null) {
    $connection = $connections[$connectionIndex];
    array_unshift($path, $connection);
    $connectionIndex = $inConnections[$connection['from']];

    //$connectionIndex = isset($inConnections[$connection['from']])
    //    ? $inConnections[$connection['from']]
    //    : null;
}

foreach ($path as $p) {
    printf("From %s to %s [%d, %d], trip %s\n", $p['from'], $p['to'], $p['departure'], $p['arrival'], $p['trip']);
}

echo PHP_EOL;
