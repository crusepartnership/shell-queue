<?php
require 'vendor/autoload.php';
require 'lib/Worker.php';
use ShellQueue\Worker;

$workers = 3;
$worker = new Worker($workers);
$worker->run();




