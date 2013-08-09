<?php

/**
* Struct-styled class storing a list of supported sensor type codes.
* Used for validating sensor type values from client requests.
* author: Nikos Moumoulidis
*/
class SensorDictionary
{
	private static $_dictionary = array("temp", "humid", "light", 
				"press", "magn", "sound", "carbdx");
				
	
	public function isSensorListValid(array $sensorlist) {
		for($i=0 ; $i<sizeof($sensorlist) ; $i++) {
			if($sensorlist[$i] == "" || $sensorlist[$i] == null) {
				return false;
			}
			else {
				$isSensorInDictionary = false;
				for($j=0 ; $j < sizeof(self::$_dictionary) ; $j++) {
					if($sensorlist[$i] == self::$_dictionary[$j]) {
						$isSensorInDictionary = true;
					}
				}
			
				if($isSensorInDictionary == false) {
					return false;
				}
			}
		}
		return true;
	}
	
	public static function isSensorValid($sensor) {
		for($i=0 ; $i < sizeof(self::$_dictionary) ; $i++) {
			if($sensor == self::$_dictionary[$i]) {
				return true;	
			}
		}
		return false;
	}
}
?>
