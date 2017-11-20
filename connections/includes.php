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

function f(array &$profiles, $s, int $t, array $defaultReturn): array
{
    if (false === isset($profiles[$s])) {
        return $defaultReturn;
    }

    $ret = $defaultReturn;
    foreach ($profiles[$s] as $p) {
        if ($p[0] > $t) {
            $ret = $p;
            break;
        }
    }

    return $ret;
}

function firstAfter(array &$profiles, $s, int $t): array
{
    return f($profiles, $s, $t, [INF, INF, null, null]);
}

function firstAfterVector(array &$profiles, $s, int $t): array
{
    return f($profiles, $s, $t, [INF, [INF, INF, INF], null, null]);
}

// component wise minimum
function minVector(array $a, array $b): array
{
    assert(count($a) === count($b), 'not equally sized vectors');

    $r = [];
    for ($i = 0, $c = count($a); $i < $c; $i++) {
        $r[$i] = min($a[$i], $b[$i]);
    }

    return $r;
}

function equalVectors(array $a, array $b): bool
{
    assert(count($a) === count($b), 'not equally sized vectors');

    for ($i = 0, $c = count($a); $i < $c; $i++) {
        if ($a[$i] !== $b[$i]) {
            return false;
        }
    }

    return true;
}

function secondsToTime($seconds): string
{
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

function dominatesVector(array $q, array $p): bool
{
    if (equalVectors($q, $p)) {
        return false;
    }

    return equalVectors($q, minVector($q, $p));
}

function shiftVectorRight(array $v): array
{
    assert(count($v) === 3);

    array_pop($v);
    array_unshift($v, INF);

    return $v;
}
