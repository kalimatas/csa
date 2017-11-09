<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$stream = new StreamHandler(STDOUT, Logger::INFO);
//$stream = new StreamHandler(STDOUT);
$stream->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s.u'));
$l = new Logger('csa');
$l->pushHandler($stream);
