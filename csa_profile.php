<?php

// Calculates earliest arrival times from all stops to
// target stop t in some date range, i.e. N -> 1 at [d1, d2].

declare(strict_types=1);

require_once 'bootstrap.php';
require_once 'connections/includes.php';
//require_once 'connections/two_direct.php';
//require_once 'connections/one_ic.php';
require_once 'connections/graph.php';

global $l, $connections, $stops, $trips;

//uasort($connections, function ($c1, $c2) {
//    return $c1['departure'] - $c2['departure'];
//});
//
//foreach ($trips as $t) {
//    printTrip($t);
//}

// --------------------------

// sort by departure desc
//uasort($connections, function ($c1, $c2) {
//    return $c2['departure'] - $c1['departure'];
//});

// Initial profiles
$profiles = array_fill_keys($stops, [[INF, INF, null, null]]);
$tripsEA = array_fill_keys($trips, [INF, null]);

// input
$from = '1';
$to = '30';
$departureTimestamp = 1510354800;

$l->info(sprintf("Depart from %s to %s at %d\n\n", $from, $to, $departureTimestamp));
$start = microtime(true);

// --------------------------

function dominates(array $q, array $p): bool {
    return ($q[0] < $p[0] && $q[1] <= $p[1]) || ($q[0] <= $p[0] && $q[1] < $p[1]);
};

foreach ($connections as $cI => $c) {
    //$l->debug(sprintf("Inspecting C %s on %s\n", getConnectionId($cI, $c), $c['trip']));

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
    $t2 = $tripsEA[$c['trip']][0];

    // c) Arrival time when transferring
    // arrival time of the earliest pair of arrival stop after $c[arrival]
    $t3 = firstAfter($profiles, $c['to'], $c['arrival'])[1];

    $t = min($t1, $t2, $t3);

    if ($t === INF) continue; // todo: this is not in the algorithm

    // II. -------------------------------------------------------
    // Incorporate $t into $tripsEA and $profiles.

    [$currentTripEA, $currentTripExitCon] = $tripsEA[$c['trip']];
    $tripsEA[$c['trip']] = [$t, $t < $currentTripEA ? $cI : $currentTripExitCon];

    $p = [$c['departure'], $t, $cI, $tripsEA[$c['trip']][1]]; // todo: change time?

    // earliest pair of departure stop
    $q = $profiles[$c['from']][0];

    if (false === dominates($q, $p)) {
        if ($q[0] !== $p[0]) {
            array_unshift($profiles[$c['from']], $p);
        } else {
            $profiles[$c['from']][0] = $p;
        }
    }
}

$l->info('Finished traversing');

echo PHP_EOL;

// ------------------ Results -----------------------

//print_r($profiles);

if (false === array_key_exists($from, $profiles)) {
    $l->info('No trips found!');
    exit();
}

//var_dump($profiles[$from]);

$routes = [];

foreach ($profiles[$from] as $profile) {
    if (INF === $profile[0]) continue;

    // 04:00 next day
    if ($profile[0] > 1510459200) {
        continue;
    }

    $route = [];

    $pExitCon = $connections[$profile[3]];

    $leg = [$profile[2], $profile[3]];
    $route[] = $leg;

    // direct
    if ($pExitCon['to'] === $to) {
        $routes[] = $route;
        continue;
    }

    // skip IC for now
//    continue;

    // IC
    while (true) {
        $p = firstAfter($profiles, $pExitCon['to'], $pExitCon['arrival']);

        $pExitCon = $connections[$p[3]];

        $leg = [$p[2], $p[3]];
        $route[] = $leg;

        // reached target
        if ($pExitCon['to'] === $to) {
            $routes[] = $route;
            break;
        }
    }
}

$end = microtime(true);

$l->info(sprintf('Found %d route(s)', count($routes)));
echo PHP_EOL;

foreach ($routes as $routeIndex => $route) {
    $duration = 0;

    foreach ($route as $leg) {
        $enterCon = $connections[$leg[0]];
        $exitCon = $connections[$leg[1]];

        $duration += $exitCon['arrival'] - $enterCon['departure'];

        $l->info(
            sprintf(
                "From %s to %s [%s, %s], trip %s\n",
                $enterCon['from'],
                $exitCon['to'],
                date('Y-m-d H:i:s', $enterCon['departure']),
//                $enterCon['departure'],
                date('Y-m-d H:i:s', $exitCon['arrival']),
//                $exitCon['arrival'],
                $enterCon['trip']
            )
        );
    }

    if ($duration > 0) {
        $l->info(sprintf("Duration: %s\n", secondsToTime($duration)));
    }

    echo PHP_EOL;
}

echo 'Start: ' . $start . PHP_EOL;
echo 'End: ' . $end . PHP_EOL;
echo 'Time: ' . ($end - $start) . PHP_EOL;

echo PHP_EOL;
