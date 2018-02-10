<?php

require 'vendor/autoload.php';

define('ROOT_DIR', __DIR__);

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

if(getenv('OWM_KEY') !== FALSE){
	$owm = new OpenWeather();
	$temp = $owm->getOutsideTemp();
	$owm->insertOutsideTemp();
}

if(getenv('NEST_ID') !== FALSE){
	$n = new Nest();
	$nestDevices = $n->getDevices();
	$n->insertHouseTemps();
}

if(getenv('HUE_USER') !== FALSE){
	$ph = new PhilipsHue();
	$ph->insertLightsOn();
}

if(getenv('TRANSMISSION_HOST') !== FALSE){
	$trans = new Transmission();
	$trans->insertBandwidthStats();
}

echo "Done!\n";