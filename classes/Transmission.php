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

		$rates = $this->api->torrentGet($this->session,new TorrentIdList([]),[
			'rateDownload',
			'rateUpload'
		]);

		foreach($rates as $r){
			if($r['rateDownload'] !== 0){
				$dr[] = $r['rateDownload'];
			}

			if($r['rateUpload'] !== 0){
				$ur[] = $r['rateUpload'];
			}
		}

		if(count($dr)){
			$averageDown = array_sum($dr)/count($dr);

			$points[] = new Point(
				'transmission.averageDownRate', 
				$averageDown,
				[],
				[],
				time()
			);

			echo "Writing in the Transmission download speed ".($averageDown/1000)."kb/s... \n";
		}

		if(count($ur)){
			$averageUp = array_sum($ur)/count($ur);

			$points[] = new Point(
				'transmission.averageUpRate', 
				$averageUp,
				[],
				[],
				time()
			);
			echo "Writing in the Transmission upload speed ".($averageUp/1000)."kb/s... \n";
		}

		if(count($points)){
			$this->writePoints($points);
		}


	}

}