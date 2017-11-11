<?php

declare(strict_types=1);

function getConnectionId(int $cId, array $c): string
{
    return sprintf('%2d/%s@%2d->%s@%2d', $cId, $c['from'], $c['departure'], $c['to'], $c['arrival']);
}

function printTrip($tripId)
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

function firstAfter(array &$profiles, $s, int $t): array
{
    $defaultReturn = [INF, INF, null, null];

    if (false === isset($profiles[$s])) {
        return $defaultReturn;
    }

    $ret = $defaultReturn;
    foreach($profiles[$s] as $p) {
        if ($p[0] > $t) {
            $ret = $p;
            break;
        }
    }

    return $ret;
}

function secondsToTime($seconds): string
{
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}
