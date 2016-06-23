<?php

$stops = [
    1 => 'A',
    2 => 'B',
    3 => 'C',
    4 => 'D',
    5 => 'E',
];

$trips = [
    1 => 'T1',
    2 => 'T2',
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
    2 => [
        'from' => 'B',
        'to' => 'D',
        'departure' => 10,
        'arrival' => 20,
        'trip' => 'T1',
        'change_time' => 4,
    ],
    3 => [
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
];

// sort by departure desc
uasort($connections, function ($c1, $c2) {
    return $c2['departure'] - $c1['departure'];
});

// Initial profiles
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

// input
$from = 'A';
$to = 'D';
$departureTimestamp = -1;

// --------------------------

foreach ($connections as $cI => $c) {
    $t = PHP_INT_MAX;

    // remain seated?
    $t = min($t, $tripsEA[$c['trip']]);

    // exit?
    if ($c['to'] == $to) {
        $t = min($t, $c['arrival']);
    }

    // Evaluating profile.
    foreach ($profiles[$c['to']] as $pr) {
        if ($c['arrival'] <= $pr['departure_start']) {
            $t = min($t, $pr['arrival_end']);
            break;
        }
    }

    // t now contains the earliest arrival time over all journeys starting in c
    if ($t < $tripsEA[$c['trip']]) {
        $tripsEA[$c['trip']] = $t;
        $tripsExitConn[$c['trip']] = $cI;
    }

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
print_r($tripsExitConn);


echo PHP_EOL;
