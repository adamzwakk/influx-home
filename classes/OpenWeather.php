<?php

use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\Exception as OWMException;
use InfluxDB\Point;
use InfluxDB\Database;

class OpenWeather{

	protected $own;
	protected $ifclint;
	protected $ifdatabase;

	public function __construct(){
		$this->owm = new OpenWeatherMap();
		$this->owm->setApiKey(getenv('OWM_KEY'));

		$this->ifclient = new InfluxDB\Client(getenv('INFLUX_IP'), 8086);
		$this->ifdatabase = $this->ifclient->selectDB(getenv('INFLUX_DB'));
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
		$result = $this->ifdatabase->writePoints([$point], Database::PRECISION_SECONDS);
	}

}