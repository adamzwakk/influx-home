<?php

use InfluxDB\Point;
use Martial\Transmission\API\TorrentIdList;

class Transmission extends InfluxHome{

	protected $client;
	protected $api;
	protected $session;

	public function __construct(){
		parent::__construct();
		$this->client = new GuzzleHttp\Client(['base_uri' => 'http://'.getenv('TRANSMISSION_HOST').':'.getenv('TRANSMISSION_PORT').'/transmission/rpc']);
		$this->api = new \Martial\Transmission\API\RpcClient($this->client, getenv('TRANSMISSION_USER'), getenv('TRANSMISSION_PW'));

		$this->session = '';

		try {
		    $this->api->sessionGet($this->session);
		} catch (\Martial\Transmission\API\CSRFException $e) {
		    $this->session = $e->getSessionId();
		} catch (\Martial\Transmission\API\TransmissionException $e) {
		    die('API error: ' . $e->getResult());
		}
	}

	public function insertBandwidthStats(){
		$dr = [];
		$ur = [];
		$averageUp = 0;
		$averageDown = 0;
		$points = [];

		$rates = $this->api->sessionStats($this->session);

		$points[] = new Point(
			'transmission.averageDownRate', 
			$rates['downloadSpeed'],
			[],
			[],
			time()
		);

		echo "Writing in the Transmission download speed ".($rates['downloadSpeed']/1000)."kb/s... \n";

		$points[] = new Point(
			'transmission.averageUpRate', 
			$rates['uploadSpeed'],
			[],
			[],
			time()
		);
		echo "Writing in the Transmission upload speed ".($rates['uploadSpeed']/1000)."kb/s... \n";
		

		if(count($points)){
			$this->writePoints($points);
		}
	}

}