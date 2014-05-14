<?php
/*
Create Character Version: 0.1
By Lilfade (Bryson Shepard)
Copyright (c) 2014

Based on the UserFrosting User Script v0.1.
Copyright (c) 2014

Licensed Under the MIT License : http://www.opensource.org/licenses/mit-license.php
Removing this copyright notice is a violation of the license.
*/
require_once("models/config.php");
set_error_handler('logAllErrors');

// Recommended admin-only access
if (!securePage($_SERVER['PHP_SELF'])){
	addAlert("danger", "Whoops, looks like you don't have permission to update a character.");
	if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
	  echo json_encode(array("errors" => 1, "successes" => 0));
	} else {
	  header("Location: " . getReferralPage());
	}
	exit();
}

//Check if selected character exists

if(!isset($_POST['character_id']) or !characterIdExists($_POST['character_id'])){
	addAlert("danger", "I'm sorry, the character id you specified is invalid!");
	if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
	  echo json_encode(array("errors" => 1, "successes" => 0));
	} else {
	  header("Location: " . getReferralPage());
	}
	exit();
}

// Required: id
$id = $_POST['character_id'];
$name = $_POST['character_name'];
$armory_link = $_POST['armory_link'];

//list of stuff to update
//character_name, character_server, character_ilvl, character_level, character_class, character_spec, character_race, armory_link 
if (!isset($_POST["csrf_token"]) or !$loggedInUser->csrf_validate(trim($_POST["csrf_token"]))){
	$errors[] = lang("ACCESS_DENIED");
} else {
	
	//grab character details based on character id
	$characterdetails = fetchCharacterDetails(NULL, NULL, $id); //Fetch character details
	
	$results = updateFileCache($characterdetails, $bnet_string, $locale_string);
	
	list($obj, $errors[], $successes[]) = $results;
	
	//------------------------------------
	//update based on information provided
	//------------------------------------
	//updateToon($obj);
	
}

restore_error_handler();

foreach ($errors as $error){
  addAlert("danger", $error);
}
foreach ($successes as $success){
  addAlert("success", $success);
}
  
if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
  echo json_encode(array(
	"errors" => count($errors),
	"successes" => count($successes)));
} else {
  header('Location: ' . getReferralPage());
  exit();
}

?>
