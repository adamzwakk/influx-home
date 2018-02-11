<?php

require 'vendor/autoload.php';

use GO\Scheduler;

define('ROOT_DIR', __DIR__);

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$scheduler = new Scheduler();

$scheduler->call(function () {
	// Call Nest API Every 5mins

    $n = new Nest();
	$nestDevices = $n->getDevices();
	$n->insertHouseTemps();
	
})->at('*/5 * * * *');

$scheduler->call(function () {
	// Call OpenWeather API Every 5mins

    $owm = new OpenWeather();
$temp = $owm->getOutsideTemp();
$owm->insertOutsideTemp();

})->at('*/15 * * * *');


$scheduler->call(function () {
	// Call HUE Api every 1min

    $ph = new PhilipsHue();
	$lightsOnCount = $ph->insertLightsOn();

})->everyMinute();

$scheduler->call(function () {
	// Call Transmission RPC every 1min

    $ph = new Transmission();
	$lightsOnCount = $ph->insertBandwidthStats();

})->everyMinute();

$scheduler->call(function () {
	// Call Sabnzbd API every 1min

    $sb = new Sabnzbd();
	$sb->insertBandwidthStats();

})->everyMinute();

$scheduler->run();