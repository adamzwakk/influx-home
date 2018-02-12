<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use InfluxDB\Point;

use Medoo\Medoo;

class Nest extends InfluxHome {

	protected $nestID;
	protected $nestSecret;
	protected $nestPIN;
	protected $client;
	protected $accessToken;
	protected $retryCount;

	public function __construct(){
		parent::__construct();
		$this->nestID = getenv('NEST_ID');
		$this->nestSecret = getenv('NEST_SECRET');
		$this->retryCount = 0;

		$this->client = new Client([
		    'timeout'  => 20,
		]);

		$this->db = new Medoo([
			'database_type' => 'sqlite',
			'database_file' => ROOT_DIR.'/settings.db'
		]);

		$token = $this->getSetting("access_token");

		if($token === FALSE)
		{
			$this->nestPIN = trim(readline("No Access Token Found, please enter your PIN...\n"));
			$this->accessToken = $this->getAuthToken();
		}
		else
		{
			$this->accessToken = $token;
		}
	}

	private function getAuthToken(){
		$req = $this->client->request('POST', 'https://api.home.nest.com/oauth2/access_token', [
			'form_params' => [
				'client_id' => $this->nestID,
				'client_secret' => $this->nestSecret,
				'code' => $this->nestPIN,
				'grant_type' => 'authorization_code'
			]
		]);

		$result = json_decode($req->getBody());

		$this->db->insert("nest_settings", [
			"key" => "access_token",
			"value" => $result->access_token,
		]);

		echo "Stored Nest Token: ".$result->access_token."\n";

		return $result->access_token;
	}

	private function getSetting($key){
		$value = $this->db->get("nest_settings","value", [
			"key" => $key	
		]);

		return $value;
	}

	public function getDevices(){
		try {
			$onRedirect = function(RequestInterface $request, ResponseInterface $response, UriInterface $uri) {
			    $this->db->insert("nest_settings", [
					"key" => "redirect_uri",
					"value" => (string)$uri
				]);
			};

			$url = $this->getSetting("redirect_uri") !== FALSE ? $this->getSetting("redirect_uri") : 'https://developer-api.nest.com/';

			$req = $this->client->request('GET', $url,[
				'allow_redirects' => [
			        'max'             => 10,        // allow at most 10 redirects.
			        'referer'         => false,      // add a Referer header
			        'protocols'       => ['https'], // only allow https URLs
			        'on_redirect'     => $onRedirect,
			        'track_redirects' => true
			    ],
				'headers' => [
					'Authorization' => 'Bearer '.$this->accessToken,
					'Content-Type' => 'application/json'
				],
			]);

			$result = json_decode($req->getBody());

			$this->devices = $result->devices;

			return $result->devices;
		} catch (RequestException $e) {
			// TODO: For some reason this fails the first time, but always gets it with the new URL
			if (!$this->retryCount) {
				return $this->getDevice();
		    } else {
		    	echo Psr7\str($e->getResponse());
		    }			
		}
	}

	public function insertHouseTemps(){
		foreach($this->devices->thermostats as $nd){
			$target = $nd->target_temperature_c;

			if($nd->hvac_mode === 'eco' && $nd->previous_hvac_mode === 'heat')
			{
				$target = $nd->eco_temperature_low_c;
			} 
			else if($nd->hvac_mode === 'cool' && $nd->previous_hvac_mode === 'eco')
			{
				$target = $nd->eco_temperature_high_c;
			}

			echo "Writing in the current temp ".$nd->ambient_temperature_c."C with a min target of ".$target." from ".$nd->name."...\n";
			
			$point = new Point(
				'house_temp', 
				$nd->ambient_temperature_c,
				['thermostat_name'=>$nd->name],
				['target_temp' => $target],
				time()
			);

			$this->writePoints([$point]);
		}
	}
}