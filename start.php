<?php

require 'vendor/autoload.php';

define('ROOT_DIR', __DIR__);

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$owm = new OpenWeather();
$temp = $owm->getOutsideTemp();
$owm->insertOutsideTemp();

$n = new Nest();
$nestDevices = $n->getDevices();
$n->insertHouseTemps();

$ph = new PhilipsHue();
$lightsOnCount = $ph->insertLightsOn();

echo "Done!\n";