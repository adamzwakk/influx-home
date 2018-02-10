<?php

use InfluxDB\Point;
use InfluxDB\Database;

class Influx{

	protected $client;
	protected $database;

	public function __construct(){
		$this->client = new InfluxDB\Client(getenv('INFLUX_IP'), 8086);
		$this->database = $this->client->selectDB(getenv('INFLUX_DB'));
	}

	public function insertHouseTemp($ambient,$target){
		$point = new Point(
			'house_temp', 
			$ambient,
			[],
			['target_temp' => $target],
			time()
		);

		$result = $this->database->writePoints([$point], Database::PRECISION_SECONDS);
	}

	public function insertOutsideTemp($temp){
		$point = new Point(
			'outside_temp', 
			$temp,
			[],
			[],
			time()
		);

		$result = $this->database->writePoints([$point], Database::PRECISION_SECONDS);
	}
}