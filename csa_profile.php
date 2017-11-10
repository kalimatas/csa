<?php

// Calculates earliest arrival times from all stops to
// target stop t in some date range, i.e. N -> 1 at [d1, d2].

declare(strict_types=1);

require_once 'bootstrap.php';
require_once 'connections/includes.php';
require_once 'connections/two_direct.php';
//require_once 'connections/one_ic.php';

global $l, $connections, $stops, $trips;

uasort($connections, function ($c1, $c2) {
    return $c1['departure'] - $c2['departure'];
});

foreach ($trips as $t) {
    printTrip($t);
}

// --------------------------

// sort by departure desc
uasort($connections, function ($c1, $c2) {
    return $c2['departure'] - $c1['departure'];
});

// Initial profiles
//$profiles = array_fill_keys($stops, [
//    [
//        'departure_start' => INF,
//        'arrival_end' => INF,
//        'enter_conn' => null,
//        'exit_conn' => null,
//    ],
//]);
//$tripsEA = array_fill_keys($trips, INF);
//$tripsExitConn = array_fill_keys($trips, null);

$profiles = [];
$tripsEA = [];

// input
$from = 'S1';
$to = 'S4';
$departureTimestamp = -1;

$l->info(sprintf("Depart from %s to %s at %d\n\n", $from, $to, $departureTimestamp));
$start = microtime(true);

// --------------------------

function dominates(array $q, array $p): bool {
    return ($q[0] < $p[0] && $q[1] <= $p[1]) || ($q[0] <= $p[0] && $q[1] < $p[1]);
};

foreach ($connections as $cI => $c) {
    $l->debug(sprintf("Inspecting C %s on %s\n", getConnectionId($cI, $c), $c['trip']));

    // I. --------------------------------------------------------
    // Find the minimum arrival time among of all values, that
    // this connection can introduce.

    $t = INF;
    $t1 = INF;
    $t2 = INF;
    $t3 = INF;

    // The options are:

    // a) Arrive at target stop
    if ($c['to'] === $to) {
        $t1 = $c['arrival'];
    }

    // b) Continue on the vehicle from the trip, i.e. remain seated
    if (array_key_exists($c['trip'], $tripsEA)) {
        $t2 = $tripsEA[$c['trip']];
    }

    // c) Arrival time when transferring
    if (array_key_exists($c['to'], $profiles)) {
        // earliest pair of arrival stop
        $ep = current($profiles[$c['to']]);
        while ($ep[0] < $c['arrival']) {
            $ep = next($profiles[$c['to']]);
        }
        $t3 = $ep[1];
    }

    $t = min($t1, $t2, $t3);

    // II. -------------------------------------------------------
    // Incorporate $t into $tripsEA and $profiles.

    $p = [$c['departure'], $t]; // todo: change time?

    // earliest pair of departure stop
    $q = array_key_exists($c['from'], $profiles) ? $profiles[$c['from']][0] : [INF, INF];

    if (false === dominates($q, $p)) {
        if ($q[0] !== $p[0]) {
            if (false === array_key_exists($c['from'], $profiles)) {
                $profiles[$c['from']][] = $p;
            } else {
                array_unshift($profiles[$c['from']], $p);
            }
        } else {
            $profiles[$c['from']][0] = $p;
        }
    }

    $tripsEA[$c['trip']] = $t;
}

$l->info('Finished traversing');

echo PHP_EOL;

// ------------------ Results -----------------------

print_r($profiles[$from]);

$end = microtime(true);
echo 'Start: ' . $start . PHP_EOL;
echo 'End: ' . $end . PHP_EOL;
echo 'Time: ' . ($end - $start) . PHP_EOL;

exit();

$routes = [];
foreach ($profiles[$from] as $profileIndex => $profile) {
    if ($profile['arrival_end'] === INF) continue;

    $journey = [];
    $localProfile = $profile;
    while (true) {
        $enterConnection = $connections[$localProfile['enter_conn']];
        $exitConnection = $connections[$localProfile['exit_conn']];

        $journey[] = [
            'trip' => $enterConnection['trip'],
            'from' => $enterConnection['from'],
            'depart' => $localProfile['departure_start'],
            'to' => $exitConnection['to'],
            'arrive' => $exitConnection['arrival'],
            'profile' => $profileIndex,
        ];

        if ($to === $exitConnection['to']) {
            break;
        }

        $localProfile = $profiles[$exitConnection['to']][$profileIndex];
    }

    $routes[] = $journey;
}

foreach ($routes as $routeIndex => $journeys) {
    printf("Route #%d\n", $routeIndex);

    foreach ($journeys as $journey) {
        printf(
            "Trip %s: depart from %s at %d, arrive to %s at %d, profileIndex = %d\n",
            $journey['trip'],
            $journey['from'],
            $journey['depart'],
            $journey['to'],
            $journey['arrive'],
            $journey['profile']
        );
    }

    echo PHP_EOL;
}

echo PHP_EOL;

// other stops
echo 'Stops:' . PHP_EOL;
foreach ($profiles as $stop => $stopPrs) {
    foreach ($stopPrs as $profile) {
        if (INF === $profile['departure_start'])
            continue;

        printf("%s->%s: (%s, %s)\n", $stop, $to, $profile['departure_start'], $profile['arrival_end']);
    }
}

echo PHP_EOL;

echo 'Trips:' . PHP_EOL;
foreach ($tripsEA as $trip => $eat) {
    if (INF === $eat)
        continue;

    printf("%s: %s\n", $trip, $eat);
}

print_r($tripsExitConn);

echo PHP_EOL;
