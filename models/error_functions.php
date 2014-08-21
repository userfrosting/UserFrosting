<?php

// Add a session alert to the queue
function addAlert($type, $message){
    if (!isset($_SESSION["userAlerts"])){
		$_SESSION["userAlerts"] = array();
	}
	$alert = array();
    $alert['type'] = $type;
    $alert['message'] = $message;
    $_SESSION["userAlerts"][] = $alert;
}

// Halt execution of an API page and either return the error code (AJAX mode), or forward to a page
function apiReturnError($ajax = false, $failure_landing_page = null){
	// Default page
	if ($failure_landing_page == null)
		$failure_landing_page = SITE_ROOT . "404.php";
	if ($ajax) {
	  echo json_encode(array("errors" => 1, "successes" => 0));
	} else {
	  header('Location: ' . $failure_landing_page);
	}
	exit();
}

// Checks that the request mode (get or post) matches the parameter.  If so, return the AJAX mode.  Otherwise, halt with error.
function checkRequestMode($mode){
	if (strtolower($mode) == "post"){
		// Confirm that data has been submitted via POST
		if (!($_SERVER['REQUEST_METHOD'] == 'POST')) {
			echo "Error: data must be submitted via POST.";
			error_log("Error: data must be submitted via POST.");
			exit();
			return false;
		} else if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
			return true;
		} else {
			return false;
		}
	} else if (strtolower($mode) == "get"){
		// Confirm that data has been submitted via GET
		if (!($_SERVER['REQUEST_METHOD'] == 'GET')) {
			echo "Error: data must be submitted via GET.";
			error_log("Error: data must be submitted via GET.");
			exit();
		} else if (isset($_GET['ajaxMode']) and $_GET['ajaxMode'] == "true" ){
			return true;
		} else {
			return false;
		}		
	} else {
		error_log("Error: invalid mode specified in checkRequestMode().");
		exit();
	}
}

?>
