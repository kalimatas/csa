<?php

// Calculates earliest arrival times from all stops to
// target stop t in some date range, i.e. N -> 1 at [d1, d2].
// Also limits the number of legs, i.e. interconnections.

declare(strict_types=1);

require_once 'bootstrap.php';
require_once 'connections/includes.php';
//require_once 'connections/two_direct.php';
//require_once 'connections/two_ic.php';
require_once 'connections/example_from_paper.php';
//require_once 'connections/one_departure_direct_ic.php';
//require_once 'connections/graph.php';

global $l, $connections, $stops, $trips;

//uasort($connections, function ($c1, $c2) {
//    return $c1['departure'] - $c2['departure'];
//});
//
//foreach ($trips as $t) {
//    printTrip($t);
//}

// sort by departure desc
uasort($connections, function ($c1, $c2) {
    return $c2['departure'] - $c1['departure'];
});

// Initial profiles
$profiles = array_fill_keys($stops, [[INF, [INF, INF, INF], [null, null, null], [null, null, null]]]);
$tripsEA = array_fill_keys($trips, [[INF, INF, INF], [null, null, null]]);

// input
$from = 'S';
$to = 'T';
$departureTimestamp = -1;

$l->info(sprintf("Depart from %s to %s at %d\n\n", $from, $to, $departureTimestamp));
$start = microtime(true);

// --------------------------

foreach ($connections as $cI => $c) {
    $l->debug(sprintf("Inspecting C %s on %s\n", getConnectionId($cI, $c), $c['trip']));

    // I. --------------------------------------------------------
    // Find the minimum arrival time among of all values, that
    // this connection can introduce.

    $t = [INF, INF, INF];
    $t1 = [INF, INF, INF];
    $t2 = [INF, INF, INF];
    $t3 = [INF, INF, INF];

    // The options are:

    // a) Arrive at target stop
    if ($c['to'] === $to) {
        $t1 = [$c['arrival'], $c['arrival'], $c['arrival']];
    }

    // b) Continue on the vehicle from the trip, i.e. remain seated
    $t2 = $tripsEA[$c['trip']][0];

    // c) Arrival time when transferring
    // arrival time of the earliest pair of arrival stop after $c[arrival]
    $t3 = shiftVectorRight(firstAfterVector($profiles, $c['to'], $c['arrival'])[1]);

    $t = minVector(minVector($t1, $t2), $t3);

    if ([INF, INF, INF] == $t) continue; // todo: this is not in the algorithm

    // II. -------------------------------------------------------
    // Incorporate $t into $tripsEA and $profiles.

    [$currentTripEA, $currentTripExitCons] = $tripsEA[$c['trip']];
    $isMin = $t < $currentTripEA;
    $tripsEA[$c['trip']] = [$t, $t < $currentTripEA ? [$cI, $cI, $cI] : $currentTripExitCons]; // todo: check exit conn

    //$p = [$c['departure'], $t, $cI, $tripsEA[$c['trip']][1]];

    // earliest pair of departure stop
    //$q = $profiles[$c['from']][0][1];
    $q = $profiles[$c['from']][0];

    $x = minVector($q[1], $t);

    $p = [$c['departure'], $x, [$cI, $cI, $cI], $tripsEA[$c['trip']][1]]; // todo: enter/exit connections

    if (false === equalVectors($q, $x)) {
        if ($q[0] !== $p[0]) {
            //array_unshift($profiles[$c['from']], $p);
            //array_unshift($profiles[$c['from']], [$c['departure'], $x, [$cI, $cI, $cI], $tripsEA[$c['trip']][1]]);
            array_unshift($profiles[$c['from']], $p);
        } else {
            $profiles[$c['from']][0] = $p;
        }


    }
}

$l->info('Finished traversing');

echo PHP_EOL;

// ------------------ Results -----------------------

//exit('Results are skipped');

print_r($profiles);
echo 'From: ' . PHP_EOL;
print_r($profiles[$from]);
exit();

$routes = [];
foreach ($profiles[$from] as $profile) {
    if (INF === $profile[0]) continue;

    // 04:00 next day
    //if ($profile[0] > 1510459200) continue;

    // each profile entry is a start (and an end in case of direct) of a route
    $route = [];
    $p = $profile;
    do {
        $leg = [$p[2], $p[3]]; // enter/exit connections form a leg
        $route[] = $leg;

        $pExitCon = $connections[$p[3]];
        $p = firstAfter($profiles, $pExitCon['to'], $pExitCon['arrival']);
    } while ($pExitCon['to'] !== $to);

    $routes[] = $route;
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
                //date('Y-m-d H:i:s', $enterCon['departure']),
                $enterCon['departure'],
                //date('Y-m-d H:i:s', $exitCon['arrival']),
                $exitCon['arrival'],
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
