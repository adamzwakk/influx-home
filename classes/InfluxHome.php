<?php

use InfluxDB\Point;
use InfluxDB\Database;

class InfluxHome{

	public $ifclient;
	public $ifdatabase;

	public function __construct(){
		$this->ifclient = new InfluxDB\Client(getenv('INFLUX_IP'), 8086);
		$this->ifdatabase = $this->ifclient->selectDB(getenv('INFLUX_DB'));
	}

	public function writePoints($points){
		$result = $this->ifdatabase->writePoints($points, Database::PRECISION_SECONDS);
	}

}