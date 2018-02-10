<?php

require 'vendor/autoload.php';

define('ROOT_DIR', __DIR__);

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$n = new Nest();
$nestDevices = $n->getDevices();

echo json_encode($nestDevices);