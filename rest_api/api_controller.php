<?php
include 'db_manager.php';

class APIController
{
	private $_Status = 200;
	private $_Body = "";
	private $_ContentType = "text/html";

//	private $_FirstVarList = array("data", "status", "location", "datetime", "sensorlist");

	function __construct() {
		
    	}

	// Check if the first variable is one of the valid/expected, as described in the
	// appropriate URI list specification.
	private function isFirstVarValid($var1) {
		foreach ($this->_FirstVarList as $command) {
			if($var1 == $command) {
				return true;
			}
		}
		return false;
	}

	// Check if the second variable is one of the valid/expected, as described in the
	// appropriate URI list specification.
	private function isSecondVarValid($var2) {
	//	$secondVarList = $this->_DataReader->getSerialCommandList();
		$secondVarList[] = "";
		foreach ($secondVarList as $command) {
			if($var2 == $command) {
				return true;
			}
		}
		return false;
	}


	public function handleRequest()
	{

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
			$msg ="";
			switch ($this->_Status)
			{
			case 404:
				$msg = "The requested resource does not exist.";
				break;
			case 405:
				$msg = "The requested method is not allowed.";
				break;
			case 406:
				$msg = "Accepting only 'application/json' MIME type for this resource.";
				break;
			case 503:
				$msg = "The sensor station is unavailable at this time.";
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
					    <p>' . $msg . '</p>
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
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    503 => 'Service Unavailable'
		);
		return (isset($codes[$status])) ? $codes[$status] : '';
	}

	function __destruct() {
		
    	}
}

?>
