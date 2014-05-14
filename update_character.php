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

	$d = $characterdetails['character_id']; //$res[$l]['character_id'];
	$a = $characterdetails['armory_link']; //$res[$l]['armory_link'];
	$n = $characterdetails['character_name'];
		
	//grab the armory data from here
	$newURL = explode("/", $a);

	if($newURL[2] !== $bnet_string) {
		$errors[] = 'error happened';
	}else{
		//check for .json file for character or grab newest one
		//check if we have a copy already or not, if not cache the file before we go further
		$filename = 'chars/'.$n.'.json';
		
		//check if locale?= is set
		//&locale=es_MX
		if (isset($locale_string)) {
			$ls = $locale_string;
		}else{
			$ls = 'en_US';
		}
		
		if (!file_exists($filename)) {
			//echo "The file dosent exist";
			$errors = 'no file found grabbing one for cache';
			$json = file_get_contents('https://'.$bnet_string.'/api/wow/character/'.$newURL[6].'/'.$newURL[7].'?fields=guild,items,talents,professions,pvp,progression,titles,feed,audit&amp;locale='.$ls);
			$new = json_decode($json);
			$file = fopen($filename, "w");
			fwrite($file, json_encode($new));
			fclose($file);
			
			$json = file_get_contents($filename);
			$obj = json_decode($json);
		} else {
			//echo "file found";
			$successes[] = 'we found a cache file';
			$json = file_get_contents($filename);
			$obj = json_decode($json);
				
			//check version of lastModified
			$json_check = file_get_contents('https://'.$bnet_string.'/api/wow/character/'.$newURL[6].'/'.$newURL[7]);
			$obj_check = json_decode($json_check);
			
			//check and see if the file we have is the latest version
			if($obj->lastModified !== $obj_check->lastModified) {
				//echo 'old file found, updated';
				$errors[] = 'old file found, updated';
				//if not delete file then grab a new one
				unlink($filename);
				$json = file_get_contents('https://'.$bnet_string.'/api/wow/character/'.$newURL[6].'/'.$newURL[7].'?fields=guild,items,talents,professions,pvp,progression,titles,feed,audit&amp;locale='.$ls);
				$new = json_decode($json);
				$file = fopen($filename, "w");
				fwrite($file, json_encode($new));
				fclose($file);
			}
		}
			//------------------------------------
			//update based on information provided
			//------------------------------------
			
	}
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
