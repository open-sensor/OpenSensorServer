<?php
include 'db_interface/db_manager.php';


$queryBuilder = new QueryBuilder();
$dbManager = new DatabaseManager();

$queryBuilder->setDBManager($dbManager);
// Aggregate is true here...
$resultsArray = $dbManager->selectQuery($queryBuilder);
for($i=0 ; $i<sizeof($resultsArray) ; $i++) {
	echo $resultsArray[$i]["DATE(datetime)"]
	." ".$resultsArray[$i]["location"]
	." ".$resultsArray[$i]["sensor_name"]
	." ".$resultsArray[$i]["AVG(sensor_value)"]
	." ".$resultsArray[$i]["MIN(sensor_value)"]
	." ".$resultsArray[$i]["MAX(sensor_value)"]
	."<br>";
}

$queryBuilder->setAggregate(false);
$resultsArray = $dbManager->selectQuery($queryBuilder);
for($i=0 ; $i<sizeof($resultsArray) ; $i++) {
	echo $resultsArray[$i]["datetime"]
	." ".$resultsArray[$i]["location"]
	." ".$resultsArray[$i]["sensor_name"]
	." ".$resultsArray[$i]["sensor_value"]
	."<br>";
}


unset($dbManager);

?>
