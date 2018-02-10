<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use Medoo\Medoo;

class Nest {

	protected $nestID;
	protected $nestSecret;
	protected $nestPIN;
	protected $client;
	protected $accessToken;

	public function __construct(){
		$this->nestID = getenv('NEST_ID');
		$this->nestSecret = getenv('NEST_SECRET');

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

	public function getDevice(){
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

			return $result->devices;
		} catch (RequestException $e) {
			if ($e->hasResponse()) {
		        echo Psr7\str($e->getResponse());
		    }
		}

		
	}
}