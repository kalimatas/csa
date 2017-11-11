<?php

declare(strict_types=1);

$file = new SplFileObject('/Users/kalimatas/Downloads/graph_profile_desc.csv', 'r');
$file->setCsvControl(',');
$file->setFlags(
    SplFileObject::READ_CSV
    | SplFileObject::SKIP_EMPTY
    | SplFileObject::READ_AHEAD
    | SplFileObject::DROP_NEW_LINE
);

$header = $file->fgetcsv();

$connections = [];
$stops = [];
$trips = [];
foreach (new \LimitIterator($file, 1) as $rawRecord) {
    $connection = array_combine($header, $rawRecord);
    $connections[] = [
        'from' => $connection['from'],
        'to' => $connection['to'],
        'departure' => (int) $connection['departure'],
        'arrival' => (int) $connection['arrival'],
        'trip' => $connection['trip'],
        'change_time' => $connection['change_time'],
    ];

    $stops[$connection['from']] = true;
    $stops[$connection['to']] = true;
    $trips[$connection['trip']] = true;
}

$stops = array_keys($stops);
$trips = array_keys($trips);
