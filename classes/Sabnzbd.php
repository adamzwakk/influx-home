<?php

use GuzzleHttp\Client;
use InfluxDB\Point;

class Sabnzbd extends InfluxHome{

	protected $client;
	protected $api;
	protected $session;

	public function __construct(){
		parent::__construct();
		$this->client = new Client([
		    'timeout'  => 20,
		]);
	}

	public function insertBandwidthStats(){
		$points = [];
		$req = $this->client->request('GET', 'http://'.getenv('SABNZBD_IP').':'.getenv('SABNZBD_PORT').'/sabnzbd/api?output=json&apikey='.getenv('SABNZBD_KEY').'&mode=qstatus');

		$stats = json_decode($req->getBody())->value;

		$points[] = new Point(
			'sabnzbd.downRate', 
			($stats->kbpersec*1000),
			[],
			[],
			time()
		);

		echo "Writing in the Sabnzbd download speed ".$stats->kbpersec."kb/s... \n";
		

		if(count($points)){
			$this->writePoints($points);
		}
	}

}