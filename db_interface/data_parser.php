<?php
include 'sensor_dictionary.php';

/**
* Utility class providing methods for parsing and transforming data between
* JSON string format and PHP data structures, while also validating them.
* used before/after INSERTS/SELECTS against the database.
* author: Nikos Moumoulidis
*/
class DataParser 
{
	public static function fromJSONToArray($jsonDataString) {
    		// Decode the JSON string.
    		$dataArrayOfArrays = @json_decode($jsonDataString, true);

		if($dataArrayOfArrays != false) {
		
	    		// Get the assoc array keys from its first element (should be the same for all).
	    		$keysArray = array_keys($dataArrayOfArrays[0]);
	    		
	    		// Create new array where each sensor type value will have its own subarray element.
	   	 	$newDataArray = array();
	    		for($i=0 ; $i<sizeof($dataArrayOfArrays) ; $i++) {
	    			for($j=0 ; $j<sizeof($keysArray) ; $j++) {
	    				if($keysArray[$j] != 'datetime' && $keysArray[$j] != 'location' ) 
	    				{
	    					// We need to transform the datetime from 
	    					// string to a PHP Date object first...
	    					$timestamp = strtotime($dataArrayOfArrays[$i]['datetime']);
	    					$date = date("Y-m-d H:i:s", $timestamp);
	    					
	    					// Validate the values that we are about to add to a new array element.
	    					if(!isset($date) || !isset($dataArrayOfArrays[$i]['location'])
	    						|| !isset($keysArray[$j]) || !isset($dataArrayOfArrays[$i][$keysArray[$j]])) {
	    						// If any of them are null, skip this entry...
	    						continue;
	    					}
	    					
	    					// Then create a new array...
	    					$newDataArray[] = array( 
	    						'datetime' => $date,
	    						'location' => $dataArrayOfArrays[$i]['location'],
	    						'sensor_name' => $keysArray[$j],
	    						'sensor_value' => $dataArrayOfArrays[$i][$keysArray[$j]] );
	    				}
	    			}
	    		}
	    		return $newDataArray;
	    	}
	    	else {
	    		return false;
	    	}
    	}
    	
    	public static function fromDBResultsToJSON(QueryBuilder $querybuilder, $resultSet) {
    		$results = array();
    		while($row = mysqli_fetch_array($resultSet)) {
			if($querybuilder->getAggregate() == "true") {
				$results[] = array( "date" => $row["DATE(datetime)"],
								"location" => $row["location"],
								"sensor_name" => $row["sensor_name"],
								"avg_value" => $row["AVG(sensor_value)"],
								"min_value" => $row["MIN(sensor_value)"],
				 				"max_value" => $row["MAX(sensor_value)"]);
				
			}
			else if($querybuilder->getAggregate() == "false"){
				$results[] = array( "datetime" => $row["datetime"],
								"location" => $row["location"],
								"sensor_name" => $row["sensor_name"],
								"sensor_value" => $row["sensor_value"]);
			}
		}
		
		if(sizeof($results) > 0) {
			$finalResultObject["resultsEmpty"] = "false";
			$finalResultObject["data"] = $results;
			return json_encode($finalResultObject);
		}
		else {
			$finalResultObject["resultsEmpty"] = "true";
			$finalResultObject["data"] = $results;
			return json_encode($finalResultObject);
		}
		
    	}
    	
    	public static function checkSensorName($sensorname) {
    		return SensorDictionary::isSensorValid($sensorname);
    	}
}
?>
