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
    3 => 'T3',
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
    // T3
    7 => [
        'from' => 'A',
        'to' => 'B',
        'departure' => 10,
        'arrival' => 20,
        'trip' => 'T3',
        'change_time' => 4,
    ],
    8 => [
        'from' => 'B',
        'to' => 'D',
        'departure' => 20,
        'arrival' => 28,
        'trip' => 'T3',
        'change_time' => 4,
    ],
    9 => [
        'from' => 'D',
        'to' => 'E',
        'departure' => 28,
        'arrival' => 38,
        'trip' => 'T3',
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
$to = 'E';
$departureTimestamp = -1;

// --------------------------

foreach ($connections as $cI => $c) {
    // I. --------------------------------------------------------
    // Find the minimum arrival time among of all values, that
    // this connection can introduce.

    $t = PHP_INT_MAX;

    // The options are:

    // 1. Continue on the vehicle from the trip, i.e. remain seated.
    $t = min($t, $tripsEA[$c['trip']]);

    // 2. Change the vehicle. Evaluating the profile of arrival stop.
    foreach ($profiles[$c['to']] as $pr) {
        if ($c['arrival'] <= $pr['departure_start']) {
            $t = min($t, $pr['arrival_end']);
            break;
        }
    }

    // 3. Arrive at target stop.
    if ($c['to'] == $to) {
        $t = min($t, $c['arrival']);
    }

    // II. -------------------------------------------------------
    // Update trip's arrival time. `t` now contains the earliest
    // arrival time over all journeys starting in c.

    if ($t < $tripsEA[$c['trip']]) {
        $tripsEA[$c['trip']] = $t;
        $tripsExitConn[$c['trip']] = $cI;
    }

    // III. ------------------------------------------------------
    // Update the profile of the current connection's departure stop.

    if ($t < $profiles[$c['from']][0]['arrival_end']) {
        if ($c['departure'] == $profiles[$c['from']][0]['departure_start']) {
            $profiles[$c['from']][0] = [
                'departure_start' => $c['departure'],
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

// ------------------ Results -----------------------

echo 'Stops:' . PHP_EOL;
foreach ($profiles as $stop => $stopPrs) {
    foreach ($stopPrs as $profile) {
        if (PHP_INT_MAX === $profile['departure_start'])
            continue;

        printf("%s->%s: (%s, %s)\n", $stop, $to, $profile['departure_start'], $profile['arrival_end']);
    }
}

echo PHP_EOL;

echo 'Trips:' . PHP_EOL;
foreach ($trips as $trip => $eat) {
    if (PHP_INT_MAX === $eat)
        continue;

    printf("%s: %s\n", $trip, $eat);
}

//print_r($profiles);
//print_r($tripsEA);
//print_r($tripsExitConn);


echo PHP_EOL;
