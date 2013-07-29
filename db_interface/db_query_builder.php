<?php

class QueryBuilder
{
	private $_dbManager = null;
	private $_sensorName = null;
	private $_location = null;
	private $_dateFrom = null;
	private $_dateTo = null;
	private $_aggregate = null; // boolean
	
	private $querySelectClause="";
	private $queryFromClause="";
	private $queryWhereClause="";
	private $queryJoinConnectionClause="";
	private $queryGroupByClause="";

	public function __construct() {
		$this->_aggregate = true; // default value
		
		// Date default values is today...
		$this->dateFrom = date("yyyy-mm-dd");
		$this->dateTo= date("yyyy-mm-dd h:i:s");
		
		$this->queryFromClause = " FROM ".DatabaseManager::TABLE_DATA.", ".DatabaseManager::TABLE_DICTIONARY." ";
		$this->queryJoinConnectionClause = " ".DatabaseManager::TABLE_DATA.".".DatabaseManager::KEY_SENSOR_NAME_ID." = "
							.DatabaseManager::TABLE_DICTIONARY.".".DatabaseManager::KEY_ID." ";
    	}
    	
    	public function setDBManager(DatabaseManager $dbManager) {
    		$this->_dbManager = $dbManager;
    	}
    	
    	public function setSensorName($sensorname) {
    		$this->_sensorName = $sensorname;
    	}
    	
    	public function setLocation($location) {
    		$this->_location = $location;
    	}
    	
    	public function setDateFrom($datefrom) {
    		$this->_dateFrom = $datefrom;
    	}
    	
    	public function setDateTo($dateto) {
    		$this->_dateTo = $dateto;
    	}
    	
    	public function removeSensorName() {
    		$this->_sensorName = null;
    	}
    	
    	public function removeLocation() {
    		$this->_location = null;
    	}
    	
    	public function removeDateFrom() {
    		$this->_dateFrom = null;
    	}
    	
    	public function removeDateTo() {
    		$this->_dateTo = null;
    	}
    	
    	public function setAggregate($wantAggregateResults) {
    		$this->_aggregate = $wantAggregateResults;
    	}
    	   	
    	public function getQuery() {
    		$this->escapeParamStrings();
    		
    		if($this->_aggregate == true) {
    			$this->querySelectClause = "SELECT "
    			.DatabaseManager::KEY_LOCATION.", "
    			.DatabaseManager::KEY_SENSOR_NAME.", "
    			."AVG(".DatabaseManager::KEY_SENSOR_VALUE."), "
    			."MIN(".DatabaseManager::KEY_SENSOR_VALUE."), "
    			."MAX(".DatabaseManager::KEY_SENSOR_VALUE."), "
    			."DATE(".DatabaseManager::KEY_DATETIME.") ";

    			$this->queryGroupByClause=" GROUP BY DATE(".DatabaseManager::KEY_DATETIME."), "
    								.DatabaseManager::KEY_LOCATION.", "
    								.DatabaseManager::KEY_SENSOR_NAME." ";
    		}
    		else {
    			$this->querySelectClause = "SELECT "
    			.DatabaseManager::TABLE_DATA.".".DatabaseManager::KEY_DATETIME.", "
    			.DatabaseManager::TABLE_DATA.".".DatabaseManager::KEY_LOCATION.", "
    			.DatabaseManager::TABLE_DICTIONARY.".".DatabaseManager::KEY_SENSOR_NAME.", "
    			.DatabaseManager::TABLE_DATA.".".DatabaseManager::KEY_SENSOR_VALUE." ";
    		}
    		
    		if($this->_sensorName != null) {
    			$this->connectWhereClauses();
    			$this->queryWhereClause .= " ".DatabaseManager::KEY_SENSOR_NAME." = '".$this->_sensorName."' ";
    		}
    		if($this->_location != null) {
			$this->connectWhereClauses();
    			$this->queryWhereClause .= " ".DatabaseManager::KEY_LOCATION." = '".$this->_location."' ";
    		}
    		if($this->_dateFrom != null) {
    			$this->connectWhereClauses();
    			$this->queryWhereClause .= " ".DatabaseManager::KEY_DATETIME." >= '".$this->_dateFrom."' ";
    		}
    		if($this->_dateTo != null) {
    			$this->connectWhereClauses();
    			$this->queryWhereClause .= " ".DatabaseManager::KEY_DATETIME." <= '".$this->_dateTo."' ";
    		}
		$this->connectWhereClauses();
		
		$query = $this->querySelectClause.
				$this->queryFromClause.
				$this->queryWhereClause.
				$this->queryJoinConnectionClause.
				$this->queryGroupByClause.";";
				
		$this->clearQueryClauses();
		
    		return $query;
    	}
    	
    	private function connectWhereClauses() {
    		if($this->queryWhereClause == "") {
			$this->queryWhereClause .= " WHERE ";
		}
		else {
			$this->queryWhereClause .= " AND ";
		}	
    	}

    	private function escapeParamStrings() {
    		if($this->_sensorName != null) {
    			$this->_sensorName = mysqli_real_escape_string($this->_dbManager->getDBConn(), $this->_sensorName);
    		}
    		if($this->_location != null) {
    			$this->_location = mysqli_real_escape_string($this->_dbManager->getDBConn(), $this->_location);
    		}
    		if($this->_dateFrom != null) {
    			$this->_dateFrom = mysqli_real_escape_string($this->_dbManager->getDBConn(), $this->_dateFrom);
    		}
    		if($this->_dateTo != null) {
    			$this->_dateTo = mysqli_real_escape_string($this->_dbManager->getDBConn(), $this->_dateTo);
    		}
    	}
    	
    	private function clearQueryClauses() {
    		$this->querySelectClause="";
		$this->queryWhereClause="";
		$this->queryGroupByClause="";
    	}
}

?>
