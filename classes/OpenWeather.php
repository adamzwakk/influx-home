<?php

use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\Exception as OWMException;
use InfluxDB\Point;

class OpenWeather extends InfluxHome{

	protected $own;

	public function __construct(){
		parent::__construct();
		$this->owm = new OpenWeatherMap();
		$this->owm->setApiKey(getenv('OWM_KEY'));
	}

	public function getOutsideTemp(){
		$weather = $this->owm->getWeather(intval(getenv('OWM_CITY')), 'metric', 'en');
		$this->weather = $weather;
		
		return $weather->temperature->getValue();
	}

	public function insertOutsideTemp(){
		$point = new Point(
			'outside_temp', 
			$this->weather->temperature->getValue(),
			[],
			[],
			time()
		);
		echo "Writing in the outside temp ".$this->weather->temperature->getValue()."C... \n";
		$this->writePoints([$point]);
	}

}