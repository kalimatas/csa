<?php

// Calculates the Earliest Arrival Time from source stop s, to
// all other stops, departing after t, i.e. 1 -> N.

declare(strict_types=1);

require_once 'bootstrap.php';
require_once 'connections/includes.php';
require_once 'connections/two_direct.php';
//require_once 'connections/nested_direct.php';

global $l, $connections, $stops, $trips;

// sort by departure
usort($connections, function ($c1, $c2) {
    return $c1['departure'] - $c2['departure'];
});

foreach ($trips as $t) {
    printTrip($t);
}

$earliestArrival = array_fill_keys($stops, INF);
$tripReachability = array_fill_keys($trips, null);
$journeyPointers = array_fill_keys($stops, null);

// input
$from = 'S1';
$to = 'S4';
$departureTimestamp = -5;

$l->debug(sprintf("Depart from %s to %s at %d\n\n", $from, $to, $departureTimestamp));

// --------------------------

$earliestArrival[$from] = $departureTimestamp;

foreach ($connections as $cI => $c) {
    $l->debug(sprintf("Inspecting C %s on %s\n", getConnectionId($cI, $c), $c['trip']));

    if ($earliestArrival[$to] <= $c['departure']) {
        $l->debug(sprintf('S[%s]=%d cannot be improved by %d anymore Stop here.', $to, $earliestArrival[$to], $c['departure']));
        break;
    }

    // A connection C is reachable, iff either a passenger:
    //  a) has already been on another connection of the same trip T
    //  b) is standing at the C's departure stop on time
    $isReachable = null !== $tripReachability[$c['trip']]
        || $c['departure'] >= ($earliestArrival[$c['from']] + $c['change_time']);

    // Does using C improve the EAT of C's arrival stop?
    $improvesArrivalTime = $c['arrival'] < $earliestArrival[$c['to']];

    $l->debug(sprintf("isReachable = %s, improves = %s\n", var_export($isReachable, true), var_export($improvesArrivalTime, true)));

    if ($isReachable && $improvesArrivalTime) {
        $l->debug("Take it [x]\n");

        if (null === $tripReachability[$c['trip']]) {
            $tripReachability[$c['trip']] = $cI;
        }

        $earliestArrival[$c['to']] = $c['arrival'];
        $journeyPointers[$c['to']] = [$tripReachability[$c['trip']], $cI];
    }

    echo PHP_EOL;
}

echo PHP_EOL;

// Build path
$path = [];
while (null !== $journeyPointers[$to]) {
    array_unshift($path, $journeyPointers[$to]);
    $enterConnectionId = $journeyPointers[$to][0];
    $enterConnection = $connections[$enterConnectionId];
    $enterConnectionDepartureStop = $enterConnection['from'];
    $to = $enterConnectionDepartureStop;
}

foreach ($path as $p) {
    $enterCon = $connections[$p[0]];
    $exitCon = $connections[$p[1]];

    $l->debug(
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

echo PHP_EOL;
