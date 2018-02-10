<?php

use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\Exception as OWMException;

class OpenWeather{

	protected $own;

	public function __construct(){
		$this->owm = new OpenWeatherMap();
		$this->owm->setApiKey(getenv('OWM_KEY'));
	}

	public function getOutsideTemp(){
		$weather = $this->owm->getWeather(intval(getenv('OWM_CITY')), 'metric', 'en');
		
		return $weather->temperature->getValue();
	}

}