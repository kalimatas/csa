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
$inConnection = array_fill_keys($stops, null);
$tripReachability = array_fill_keys($trips, false);

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
    $isReachable = true === $tripReachability[$c['trip']]
        || $c['departure'] >= ($earliestArrival[$c['from']] + $c['change_time']);

    // Does using C improve the EAT of C's arrival stop?
    $improvesArrivalTime = $c['arrival'] < $earliestArrival[$c['to']];

    $l->debug(sprintf("isReachable = %s, improves = %s\n", var_export($isReachable, true), var_export($improvesArrivalTime, true)));

    if ($isReachable && $improvesArrivalTime) {
        $l->debug("Take it [x]\n");

        $tripReachability[$c['trip']] = true;
        $earliestArrival[$c['to']] = $c['arrival'];
        $inConnection[$c['to']] = $cI;
    }

    echo PHP_EOL;
}

echo PHP_EOL;

if ($inConnection[$to] === null) {
    $l->debug(sprintf("No path from %s to %s at %s\n", $from, $to, $departureTimestamp));
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
    $l->debug(sprintf("From %s to %s [%d, %d], trip %s\n", $p['from'], $p['to'], $p['departure'], $p['arrival'], $p['trip']));
}

echo PHP_EOL;
