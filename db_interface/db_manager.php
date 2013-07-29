<?php
include 'db_query_builder.php';

class DatabaseManager
{
	private $_DBCONN;
	private $_HOSTNAME = "localhost";
	private $_USERNAME = "root";
	private $_PASSWORD = "root";
	private $_SCHEMA = "OpenSensingDB";
	
	const KEY_ID = 'id';
	
	// Table sensor_dictionary...
	const TABLE_DICTIONARY = "sensor_dictionary";
	const KEY_SENSOR_NAME = "sensor_name";
	
	// Table sensor_data...
	const TABLE_DATA = "sensor_data";
	const KEY_DATETIME = "datetime";
	const KEY_LOCATION = "location";
	const KEY_SENSOR_NAME_ID = "sensor_name_id";
	const KEY_SENSOR_VALUE = "sensor_value";
	
	public function __construct() {
		$this->openConnection();
	}
	
	private function openConnection() {
		$this->_DBCONN = mysqli_connect($this->_HOSTNAME, $this->_USERNAME, $this->_PASSWORD, $this->_SCHEMA);
		
		if(mysqli_connect_errno($this->_DBCONN)) {
			echo "Failed to connect to schema: ".$this->_SCHEMA."...<br>";
			echo mysqli_connect_error();
		}
	}
	
	private function closeConnection() {
		mysqli_close($this->_DBCONN);
	}
	
	public function selectQuery(QueryBuilder $querybuilder) {
		$query = $querybuilder->getQuery();
		$resultSet = mysqli_query($this->_DBCONN, $query) or die("Query Error: ".mysqli_error($this->_DBCONN));

		$results = array();
		while($row = mysqli_fetch_array($resultSet)) {
			$results[] = $row;
		}
		return $results;
	}
	
	public function getDBConn() {
		return $this->_DBCONN;
	}
	
	public function __destruct() {
		$this->closeConnection();
	}
	
}
?>
