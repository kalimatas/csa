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
}
