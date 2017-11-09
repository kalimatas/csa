<?php

// Calculates the Earliest Arrival Time from source stop s, to
// all other stops, departing after t, i.e. 1 -> N.

declare(strict_types=1);

require_once 'bootstrap.php';
require_once 'connections/includes.php';
//require_once 'connections/two_direct.php';
require_once 'connections/graph.php';
//require_once 'connections/nested_direct.php';

global $l, $connections, $stops, $trips;

// sort by departure
//usort($connections, function ($c1, $c2) {
//    return $c1['departure'] - $c2['departure'];
//});

//foreach ($trips as $t) {
//    printTrip($t);
//}

$earliestArrival = [];
$tripReachability = [];
$journeyPointers = [];

// input
$from = '1';
$to = '10';
$departureTimestamp = 1510354800;

$l->info(sprintf("Depart from %s to %s at %d\n\n", $from, $to, $departureTimestamp));
$start = microtime(true);

// --------------------------

$earliestArrival[$from] = $departureTimestamp;

foreach ($connections as $cI => $c) {
    $l->debug(sprintf("Inspecting C %s on %s\n", getConnectionId($cI, $c), $c['trip']));

    $earliestArrivalTo = array_key_exists($to, $earliestArrival) ? $earliestArrival[$to] : INF;
    //$tripReachabilityCon = array_key_exists($c['trip'], $tripReachability) ? $tripReachability[$c['trip']] : null;

    if ($earliestArrivalTo <= $c['departure']) {
        $l->info(sprintf('S[%s]=%d cannot be improved by %d anymore Stop here.', $to, $earliestArrivalTo, $c['departure']));
        break;
    }

    // A connection C is reachable, iff either a passenger:
    //  a) has already been on another connection of the same trip T
    //  b) is standing at the C's departure stop on time
    $earliestArrivalConFrom = array_key_exists($c['from'], $earliestArrival) ? $earliestArrival[$c['from']] : INF;
    $isReachable = array_key_exists($c['trip'], $tripReachability)
        || $c['departure'] >= ($earliestArrivalConFrom + $c['change_time']);

    $l->debug(sprintf("isReachable = %s\n", var_export($isReachable, true)));

    if ($isReachable) {
        // Does using C improve the EAT of C's arrival stop?
        $earliestArrivalConTo = array_key_exists($c['to'], $earliestArrival) ? $earliestArrival[$c['to']] : INF;
        $improvesArrivalTime = $c['arrival'] < $earliestArrivalConTo;

        if (false === $improvesArrivalTime) {
            continue;
        }

        $l->debug(sprintf("improves = %s, Take it [x]\n", var_export($improvesArrivalTime, true)));

        if (false === array_key_exists($c['trip'], $tripReachability)) {
            $tripReachability[$c['trip']] = $cI;
        }

        $earliestArrival[$c['to']] = $c['arrival'];
        $journeyPointers[$c['to']] = [$tripReachability[$c['trip']], $cI];
    }

    //echo PHP_EOL;
}

$l->info('Finished traversing');

echo PHP_EOL;

// Build path
$path = [];
while (false !== array_key_exists($to, $journeyPointers)) {
    array_unshift($path, $journeyPointers[$to]);
    $enterConnectionId = $journeyPointers[$to][0];
    $enterConnection = $connections[$enterConnectionId];
    $enterConnectionDepartureStop = $enterConnection['from'];
    $to = $enterConnectionDepartureStop;
}

foreach ($path as $p) {
    $enterCon = $connections[$p[0]];
    $exitCon = $connections[$p[1]];

    $l->info(
        sprintf(
            "From %s to %s [%d, %d], dep trip %s, arr trip %s\n",
            $enterCon['from'],
            $exitCon['to'],
            $enterCon['departure'],
            $exitCon['arrival'],
            $enterCon['trip'],
            $exitCon['trip']
        )
    );
}

$end = microtime(true);
echo 'Start: ' . $start . PHP_EOL;
echo 'End: ' . $end . PHP_EOL;
echo 'Time: ' . ($end - $start) . PHP_EOL;

echo PHP_EOL;
