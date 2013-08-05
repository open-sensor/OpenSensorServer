<?php
include 'db_interface/db_manager.php';

class APIController
{
	private $_Status = 200;
	private $_Message="";
	private $_Body = "";
	private $_ContentType = "text/html";

	private $_AllVarsList = array("batch_request", "url_invalid", 
						"sensor_name", "location", 
						"datefrom", "dateto", "aggregate");

	private $_CurrentValuesList = array();
	private $_CurrentVarsList = array();
	private $_IsRequestUrlValid = null;
	private $_CurrentQueryBuilder = null;

	function __construct() {

    	}

	// Gather and check if the any of the variables that were set are of the valid/expected,
	// as described in the appropriate URI scheme specification.
	private function gatherGETVariablesAndValidateRequestUrl() {
		// Check if the base url (/senseapi/data) is valid...	
		if($_SERVER['REQUEST_URI'] == "/senseapi/data" || $_SERVER['REQUEST_URI'] == "/senseapi/data/") {
			$this->_IsRequestUrlValid = true;
			return;
		}
		if(isset($_GET['url_invalid'])) {
			if($_GET['url_invalid'] == 'true') {
				$this->_IsRequestUrlValid = false;
				return;
			}
		}
		// The base url is valid, check for the GET variables now...
		foreach($this->_AllVarsList as $variable) {
			if(isset($_GET[$variable]) && $_GET[$variable] != "" && $variable != 'url_invalid') {
				$this->_CurrentVarsList[] = $variable;
				$this->_CurrentValuesList[$variable] = $_GET[$variable];
				$this->_IsRequestUrlValid = true;
			}
		}
	}
	
	private function validateAndSetGETData($queryBuilder) {
		for($i=0 ; $i<sizeof($this->_CurrentVarsList) ; $i++) {
			if($this->_CurrentVarsList[$i] == "sensor_name") {
				$isOk = DataParser::checkSensorName($this->_CurrentValuesList[$this->_CurrentVarsList[$i]]);
				if(!$isOk) {
					return false;
				}
				else {
					$queryBuilder->setSensorName($this->_CurrentValuesList[$this->_CurrentVarsList[$i]]);
				}
			}
			else if($this->_CurrentVarsList[$i] == "aggregate") {
				if($this->_CurrentValuesList[$this->_CurrentVarsList[$i]] != "true" &&
					$this->_CurrentValuesList[$this->_CurrentVarsList[$i]] != "false"){
					return false;
				}
				else {
					$queryBuilder->setAggregate($this->_CurrentValuesList[$this->_CurrentVarsList[$i]]);
				}
			}
			else if($this->_CurrentVarsList[$i] == "dateto" || $this->_CurrentVarsList[$i] == "datefrom") {
				$isOk = strtotime($this->_CurrentValuesList[$this->_CurrentVarsList[$i]]);
				if($isOk == false) {
					return false;
				}
				else {
					if($this->_CurrentVarsList[$i] == "dateto") {
						$queryBuilder->setDateTo($this->_CurrentValuesList[$this->_CurrentVarsList[$i]]);
					} else if($this->_CurrentVarsList[$i] == "datefrom"){
						$queryBuilder->setDateFrom($this->_CurrentValuesList[$this->_CurrentVarsList[$i]]);
					}
				}
			} // Location cannot be validated at the moment (its just any string)...
			else if($this->_CurrentVarsList[$i] == "location") {
				$queryBuilder->setLocation($this->_CurrentValuesList[$this->_CurrentVarsList[$i]]);
			}
		}
		return $queryBuilder;
	}

	private function performInsert($dataToStore) {
		$dbManager = new DatabaseManager();
		$queryBuilder = new QueryBuilder("insert");
		
		$isOk = $queryBuilder->setInsertDataString($dataToStore);
		if($isOk) {
			$success = $dbManager->runInsertQuery($queryBuilder);
			if(!$success) {
				unset($dbManager);
				unset($queryBuilder);
				return false;
			}
			return true;
		}
		else {
			unset($dbManager);
			unset($queryBuilder);
			return false;
		}
	}


	public function handleRequest()
	{
		$this->gatherGETVariablesAndValidateRequestUrl();
		$method = $_SERVER["REQUEST_METHOD"];
		$accept = $_SERVER["HTTP_ACCEPT"];
		
		if($this->_IsRequestUrlValid == false) {
			if($method == "GET") {
				$this->_Status = 404;
				$this->_Message = "The requested resource does not exist.";
				return;
			}
		}

		if($method == "GET") {
			if($accept == "application/json") {
				$dbManager = new DatabaseManager();
				$queryBuilder = new QueryBuilder("select");
				
				// SELECT queries require to set a DatabaseManager object...
				$queryBuilder->setDBManager($dbManager); 
				$queryBuilder = $this->validateAndSetGETData($queryBuilder);
				if($queryBuilder != false) {
					$resultsJSON = $dbManager->runSelectQuery($queryBuilder);
					$this->_Body = $resultsJSON;
					unset($dbManager);
					unset($queryBuilder);
					unset($resultsJSON);
				}
				else {
					$this->_Status = 404;
					$this->_Message = "Invalid parameter value(s) provided.";
					unset($dbManager);
					unset($queryBuilder);
					unset($resultsJSON);
				}
			}
			else {
				$this->_Status = 406;
			}
		}
		else if($method == "POST") {
			if($_SERVER['REQUEST_URI'] != "/senseapi/data" && $_SERVER['REQUEST_URI'] != "/senseapi/data/") {
				
				$this->_Status = 400;
				$this->_Message = "Bad Request: The requested location is not a resource placeholder.";
			}
			else {
				$dataToStore = file_get_contents('php://input');
				if($dataToStore != null && $dataToStore != "") {
					$success = $this->performInsert($dataToStore);
					if($success) {
						$this->_Message ="Data stored successfully.";
						return;
					}
					else {
						$this->_Status = 400;
						$this->_Message = "Bad Request: Invalid JSON data provided.";
					}
				}
				else {
					$this->_Status = 400;
					$this->_Message = "Bad Request: Empty data provided.";
				}
			}
		}
		else {
			$this->_Status = 405;
		}
	}

	public function sendResponse()
	{
		$status_header = "HTTP/1.1 " . $this->_Status . " " . $this->getStatusCodeMsg($this->_Status);
		// set the status
		header($status_header);
		// set the content type
		header("Content-type: " . $this->_ContentType);

		// If we have a body already, it means that data is being passed.
		if($this->_Body != "") {
			echo $this->_Body;
			return;
		}
		// We create an HTML body if none is passed, and set the appropriate error message.
		else {
			if($this->_Status == 204) {
				return;
			}
			switch ($this->_Status)
			{
			case 405:
				$this->_Message = "The requested method is not allowed.";
				break;
			case 406:
				$this->_Message = "Accepting only 'application/json' MIME type for this resource.";
				break;
			case 503:
				$this->_Message = "The server is unavailable at this time.";
				break;
			}
			$this->_Body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
				    <html>
					<head>
					    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
					    <title>' . $this->_Status . ' ' . $this->getStatusCodeMsg($this->_Status) . '</title>
					</head>
					<body>
					    <h1>' . $this->getStatusCodeMsg($this->_Status) . '</h1>
					    <p>' . $this->_Message . '</p>
					</body>
				    </html>';
			echo $this->_Body;
			return;
		}
	}

	// Utility method for interpreting HTTP response status codes into their meanings.
	private function getStatusCodeMsg($status)
	{
		$codes = Array(
		    200 => 'OK',
		    204 => 'No Content',
		    400 => 'Bad Request',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    503 => 'Service Unavailable'
		);
		return (isset($codes[$status])) ? $codes[$status] : '';
	}
}

?>
