<?php
include 'rest_api/api_controller.php';

/* All HTTP requests are directed to this script by the web server.
It handles the HTTP request using an APIController object. */
$apiController = new APIController();
$apiController->handleRequest();
//$apiController->sendResponse();
unset($apiController);
echo "mod_rewrite works!";
?>
