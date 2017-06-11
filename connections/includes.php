<?php

declare(strict_types=1);

$stops = ['S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8'];
$trips = ['T1', 'T2', 'T3', 'T4', 'T5'];

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
