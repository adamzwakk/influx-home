<?php

require 'vendor/autoload.php';

define('ROOT_DIR', __DIR__);

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$influx = new Influx();

$owm = new OpenWeather();
$temp = $owm->getOutsideTemp();
echo "Writing in the outside temp ".$temp."C \n";
$influx->insertOutsideTemp($temp);

$n = new Nest();
$nestDevices = $n->getDevices();

foreach($nestDevices->thermostats as $nd){
	echo "Writing in the current temp ".$nd->ambient_temperature_c."C from ".$nd->name."\n";
	$influx->insertHouseTemp($nd->ambient_temperature_c, $nd->target_temperature_c);
}

$ph = new PhilipsHue();
$lightsOnCount = $ph->getLightsOnCount();
echo "Writing in ".$lightsOnCount." light(s) on right now \n";
$influx->insertLightCount($lightsOnCount);