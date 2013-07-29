<?php
include 'db_interface/db_manager.php';

$queryBuilder = new QueryBuilder("select");
$dbManager = new DatabaseManager();

// ===================== Select with aggregation test ==================== //
$queryBuilder->setDBManager($dbManager);
// Aggregate is true (by default) here...
$resultsArray = $dbManager->runSelectQuery($queryBuilder);
for($i=0 ; $i<sizeof($resultsArray) ; $i++) {
	echo $resultsArray[$i]["DATE(datetime)"]
	." ".$resultsArray[$i]["location"]
	." ".$resultsArray[$i]["sensor_name"]
	." ".$resultsArray[$i]["AVG(sensor_value)"]
	." ".$resultsArray[$i]["MIN(sensor_value)"]
	." ".$resultsArray[$i]["MAX(sensor_value)"]
	."<br>";
}

// ===================== Select without aggregation test ==================== //
$queryBuilder->setAggregate(false);
$resultsArray = $dbManager->runSelectQuery($queryBuilder);
for($i=0 ; $i<sizeof($resultsArray) ; $i++) {
	echo $resultsArray[$i]["datetime"]
	." ".$resultsArray[$i]["location"]
	." ".$resultsArray[$i]["sensor_name"]
	." ".$resultsArray[$i]["sensor_value"]
	."<br>";
}

/* ===================== Insert test ==================== //
// Testing insert operation by using a data json file. Normally this 
// data would originate from a client POST request...

$queryBuilder = new QueryBuilder("insert");
$queryBuilder->setInsertDataString(file_get_contents('data.json'));
$queryBuilder->setDBManager($dbManager);
$success = $dbManager->runInsertQuery($queryBuilder);
if($success) {
	echo "Insert query was run successfully...";
}
*/
unset($dbManager);
?>
