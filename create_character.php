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

//get the current user id to add a character to
// $loggedInUser->user_id 

// Create a user from the admin panel.
// Request method: POST

require_once("./models/config.php");

set_error_handler('logAllErrors');

// Recommended admin-only access
if (!securePage($_SERVER['PHP_SELF'])){
  addAlert("danger", "Whoops, looks like you don't have permission to create a user.");
  if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
	echo json_encode(array("errors" => 1, "successes" => 0));
  } else {
	header("Location: " . getReferralPage());
  }
  exit();
}

//Forms posted
if (!empty($_POST)) {
	$errors = array();
	$armory_link = trim($_POST["armory_link"]);
	$csrf_token = trim($_POST["csrf_token"]);
	$current_user_id = $_SESSION["userCakeUser"]->user_id;
	if (!isset($_POST["csrf_token"]) or !$loggedInUser->csrf_validate(trim($_POST["csrf_token"]))){
	  $errors[] = lang("ACCESS_DENIED");
	}
	
	/*//check for the user id so we can associate this character with this user
	if (!isset($_POST["user_id"]) or !$loggedInUser->user_id(trim($_POST["user_id"]))){
		$errors[] = lang("ACCESS_DENIED");
	}*/
	
	//check is the armory link is valid
	$armory_link = $_POST['armory_link'];
	
	$newArmory = explode("/", $armory_link);

	if($newArmory[2] !== "us.battle.net") {
		//not a battle.net address kill the script
		//echo 'error happened';
		$errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(1,25));
	}else{
		//the link is vaild set up variables from the data extracted
		$json = file_get_contents('https://us.battle.net/api/wow/character/'.$newArmory[6].'/'.$newArmory[7].'?fields=items,talents');
		$obj = json_decode($json);
		
		$name = $obj->name;
		$server = $obj->realm;
		$ilvl = $obj->items->averageItemLevelEquipped;
		$level = $obj->level;
		$className = $obj->class;
		
		if (isset($obj->talents[0]->selected)) {
			$spec = $obj->talents[0]->spec->name;
		}else{
			$spec = $obj->talents[1]->spec->name; //0 for selected spec 1 for non selected spec
		}
		$raceName = $obj->race;
		
		//grab the name of the class rather then a int
		if($className == "6") { $class = "Death Knight"; //Death Knight
		}elseif($className == "11") { $class = "Druid"; //Druid
		}elseif($className == "3") { $class = "Hunter"; //Hunter
		}elseif($className == "8") { $class = "Mage"; //Mage
		}elseif($className == "10") { $class = "Monk"; //Monk
		}elseif($className == "2") { $class = "Paladin"; //Paladin
		}elseif($className == "5") { $class = "Priest"; //Priest
		}elseif($className == "4") { $class = "Rogue"; //Rogue
		}elseif($className == "7") { $class = "Shaman"; //Shaman
		}elseif($className == "9") { $class = "Warlock"; //Warlock
		}elseif($className == "1") { $class = "Warrior"; //Warrior
		}else { $class = "No Class Found"; }
		
		//gsetup the color for the character based on class just for flare
		if($className == "6") { $classColor = "#C41F3B"; //Death Knight
		}elseif($className == "11") { $classColor = "#FF7D0A"; //Druid
		}elseif($className == "3") { $classColor = "#ABD473"; //Hunter
		}elseif($className == "8") { $classColor = "#69CCF0"; //Mage
		}elseif($className == "10") { $classColor = "#558A84"; //Monk
		}elseif($className == "2") { $classColor = "#F58CBA"; //Paladin
		}elseif($className == "5") { $classColor = "#FFFFFF"; //Priest
		}elseif($className == "4") { $classColor = "#FFF569"; //Rogue
		}elseif($className == "7") { $classColor = "#0070DE"; //Shaman
		}elseif($className == "9") { $classColor = "#9482C9"; //Warlock
		}elseif($className == "1") { $classColor = "#C79C6E"; //Warrior
		}else { $classColor = "#000000"; }
		
		//grab the name of the race rather then a int
		if($raceName == "11") { $race = "Draenei"; //Death Knight
		}elseif($raceName == "1") { $race = "Human"; //Druid
		}elseif($raceName == "5") { $race = "Undead"; //Hunter
		}elseif($raceName == "7") { $race = "Gnome"; //Mage
		}elseif($raceName == "8") { $race = "Troll"; //Monk
		}elseif($raceName == "2") { $race = "Orc"; //Paladin
		}elseif($raceName == "3") { $race = "Dwarf"; //Priest
		}elseif($raceName == "4") { $race = "Night Elf"; //Rogue
		}elseif($raceName == "10") { $race = "Blood Elf"; //Shaman
		}elseif($raceName == "22") { $race = "Worgan"; //Warlock
		}elseif($raceName == "6") { $race = "Tauren"; //Warrior
		}elseif($raceName == "24") { $race = "Neutral Pandaren"; //Warrior
		}elseif($raceName == "26") { $race = "Horde  Pandaren"; //Warrior
		}elseif($raceName == "25") { $race = "Alliance  Pandaren"; //Warrior
		}elseif($raceName == "9") { $race = "Goblin"; //Warrior
		}else { $race = "No Race Found"; } 
		
	}
	
	$new_character_id = "";
	
	//End data validation
	if(count($errors) == 0)
	{	
		//Construct a user object
		$character = new Character($current_user_id, $name, $server, $ilvl, $level, $class, $spec, $race, $armory_link, $classColor);
		
		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if(!$character->status)
		{
			if($character->name_taken) $errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($name));
		}
		else
		{
			//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
			$new_character_id = $character->AddCharacter();
			if($new_character_id == -1)
			{
				if($character->sql_failure)  $errors[] = lang("SQL_ERROR");
			}
		}
	}
} else {
	$errors[] = lang("NO_DATA");
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