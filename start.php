<?php

require 'vendor/autoload.php';

define('ROOT_DIR', __DIR__);

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$influx = new Influx();

$n = new Nest();
$nestDevices = $n->getDevices();

foreach($nestDevices->thermostats as $nd){
	echo "Writing in the current temp ".$nd->ambient_temperature_c."C from ".$nd->name."\n";
	$influx->insertHouseTemp($nd->ambient_temperature_c, $nd->target_temperature_c);
}