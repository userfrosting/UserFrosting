<?php
/*

UserFrosting Version: 0.1
By Alex Weissman
Copyright (c) 2014

Based on the UserCake user management system, v2.0.2.
Copyright (c) 2009-2012

UserFrosting, like UserCake, is 100% free and open-source.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the 'Software'), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

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

//list of stuff to update
//character_name, character_server, character_ilvl, character_level, character_class, character_spec, character_race, armory_link 
if (!isset($_POST["csrf_token"]) or !$loggedInUser->csrf_validate(trim($_POST["csrf_token"]))){
  $errors[] = lang("ACCESS_DENIED");
} else {
	
	//grab character details based on character id
	$characterdetails = fetchCharacterDetails(NULL, NULL, $id); //Fetch character details
	/*
	find out why the why this is even firing on a regular update of character information besides the character name...
	*/
	//Update character name
	if ($characterdetails['character_name'] != $_POST['character_name']){
		$charactername = trim($_POST['character_name']);
		
		//Validate display name
		if(characterNameExists($charactername))
		{
			$errors[] = lang("ACCOUNT_CHARACTERNAME_IN_USE",array($charactername));
		}
		elseif(minMaxRange(1,50,$charactername))
		{
			$errors[] = lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(1,50));
		}
		else {
			if (updateCharacterName($id, $charactername)){
				$successes[] = lang("ACCOUNT_CHARACTERNAME_UPDATED", array($charactername));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
	}
	else {
		$charactername = $characterdetails['character_name'];
	}

	//Update server
	if ($characterdetails['character_server'] != $_POST['character_server']){
		$cserver = trim($_POST['character_server']);
		
		//Validate server
		if(minMaxRange(1,50,$cserver))
		{
			$errors[] = lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,50));
		}
		else {
			if (updateServer($id, $cserver)){
				$successes[] = lang("ACCOUNT_TITLE_UPDATED", array ($charactername, $cserver));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
	
	//Update ilvl
	if ($characterdetails['character_ilvl'] != $_POST['character_ilvl']){
		$cilvl = trim($_POST['character_ilvl']);
		
		//Validate ilvl
		if(minMaxRange(1,50,$cilvl))
		{
			$errors[] = lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,50));
		}
		else {
			if (updateIlvl($id, $cilvl)){
				$successes[] = lang("ACCOUNT_TITLE_UPDATED", array ($charactername, $cilvl));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
	
	//Update level
	if ($characterdetails['character_level'] != $_POST['character_level']){
		$clevel = trim($_POST['character_level']);
		
		//Validate level
		if(minMaxRange(1,50,$clevel))
		{
			$errors[] = lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,50));
		}
		else {
			if (updateLevel($id, $clevel)){
				$successes[] = lang("ACCOUNT_TITLE_UPDATED", array ($charactername, $clevel));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
	
	//Update class
	if ($characterdetails['character_class'] != $_POST['character_class']){
		$cclass = trim($_POST['character_class']);
		
		//Validate title
		if(minMaxRange(1,50,$cclass))
		{
			$errors[] = lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,50));
		}
		else {
			if (updateClass($id, $cclass)){
				$successes[] = lang("ACCOUNT_TITLE_UPDATED", array ($charactername, $cclass));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
	
	//Update spec
	if ($characterdetails['character_spec'] != $_POST['character_spec']){
		$cspec = trim($_POST['character_spec']);
		
		//Validate title
		if(minMaxRange(1,50,$cspec))
		{
			$errors[] = lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,50));
		}
		else {
			if (updateSpec($id, $cspec)){
				$successes[] = lang("ACCOUNT_TITLE_UPDATED", array ($charactername, $cspec));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
	
	//Update race
	if ($characterdetails['character_race'] != $_POST['character_race']){
		$crace = trim($_POST['character_race']);
		
		//Validate title
		if(minMaxRange(1,50,$crace))
		{
			$errors[] = lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,50));
		}
		else {
			if (updateRace($id, $crace)){
				$successes[] = lang("ACCOUNT_TITLE_UPDATED", array ($charactername, $crace));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
	
	//Update armory link
	if ($characterdetails['armory_link'] != $_POST['armory_link']){
		$alink = trim($_POST['armory_link']);
		
		//Validate title
		if(minMaxRange(1,50,$alink))
		{
			$errors[] = lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,50));
		}
		else {
			if (updateArmory($id, $alink)){
				$successes[] = lang("ACCOUNT_TITLE_UPDATED", array ($charactername, $alink));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
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
