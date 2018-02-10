<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use InfluxDB\Point;
use InfluxDB\Database;

class PhilipsHue{

	protected $client;
	protected $ifclint;
	protected $ifdatabase;

	public function __construct(){
		$this->client = new Client([
		    'timeout'  => 20,
		]);

		$this->ifclient = new InfluxDB\Client(getenv('INFLUX_IP'), 8086);
		$this->ifdatabase = $this->ifclient->selectDB(getenv('INFLUX_DB'));
	}

	private function getLights(){
		$req = $this->client->request('GET', 'http://'.getenv('HUE_BRIDGE_IP').'/api/'.getenv('HUE_USER').'/lights');
		return json_decode($req->getBody());
	}

	public function insertLightsOn(){
		$count = 0;
		$lights = $this->getLights();
		foreach($lights as $l)
		{
			if($l->state->on && $l->state->reachable){
				$count++;
			}
		}

		$point = new Point(
			'lights_on', 
			$count,
			[],
			[],
			time()
		);
		echo "Writing in ".$count." Philips HUE bulbs on now...\n";
		$result = $this->ifdatabase->writePoints([$point], Database::PRECISION_SECONDS);
	}

	

}