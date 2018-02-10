<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

class PhilipsHue{

	protected $client;

	public function __construct(){
		$this->client = new Client([
		    'timeout'  => 20,
		]);
	}

	public function getLightsOnCount(){
		$count = 0;
		$lights = $this->getLights();
		foreach($lights as $l)
		{
			if($l->state->on && $l->state->reachable){
				$count++;
			}
		}
		return $count;
	}

	private function getLights(){
		$req = $this->client->request('GET', 'http://'.getenv('HUE_BRIDGE_IP').'/api/'.getenv('HUE_USER').'/lights');
		return json_decode($req->getBody());
	}

}