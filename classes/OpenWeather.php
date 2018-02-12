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
		$units = (getenv('TEMP_UNIT') === 'C') ? 'metric' : 'imperial';
		$weather = $this->owm->getWeather(intval(getenv('OWM_CITY')), $units, 'en');
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
		echo "Writing in the outside temp ".$this->weather->temperature->getValue().getenv('TEMP_UNIT')."... \n";
		$this->writePoints([$point]);
	}

}