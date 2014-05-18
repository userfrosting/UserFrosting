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

//Functions that interact mainly with .characters table
//------------------------------------------------------------------------------
//Delete a defined array of characters
function deleteCharacters($characters) 
{
	global $mysqli,$db_table_prefix; 
	$i = 0;
	$stmt = $mysqli->prepare("DELETE FROM ".$db_table_prefix."characters 
		WHERE character_id = ?");
	foreach($characters as $id){
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$i++;
	}
	$stmt->close();
	return $i;
}

//Retrieve complete user information by character name, token or ID
function fetchCharacterDetails($charactername=NULL,$token=NULL, $id=NULL)
{
	if($charactername!=NULL) {
		$column = "character_name";
		$data = $charactername;
	}
	elseif($id!=NULL) {
		$column = "character_id";
		$data = $id;
	}
	//sql for character table
	//user_id, character_id, character_name, character_server, character_ilvl, 
	//character_level, character_spec, character_class, armory_link, added_stamp, last_update_stamp
	global $mysqli,$db_table_prefix; 
	$stmt = $mysqli->prepare("SELECT 
		character_id,
		user_id,
		character_name,
		armory_link,
		added_stamp,
		last_update_stamp
		FROM ".$db_table_prefix."characters
		WHERE
		$column = ?
		LIMIT 1");
		
	if (!$stmt)
		return false;
		
	$stmt->bind_param("s", $data);

	$stmt->execute();
	$stmt->bind_result($cid, $userid, $cname, $armory, $added, $update);
	
	if($stmt->fetch()){
		return array('character_id' => $cid, 'user_id' => $userid, 'character_name' => $cname, 'armory_link' => $armory, 'added_stamp' => $added, 'last_update_stamp' => $update);
		$stmt->close();
	} else {
		return false;
	}
}

//Check if a character name exists in the DB
function characterNameExists($charactername)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("SELECT character_name
		FROM ".$db_table_prefix."characters
		WHERE
		character_name = ?
		LIMIT 1");
	$stmt->bind_param("s", $charactername);	
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

//Check if a user ID exists in the DB
function characterIdExists($id)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("SELECT character_id
		FROM ".$db_table_prefix."characters
		WHERE
		character_id = ?
		LIMIT 1");
	$stmt->bind_param("i", $id);	
	$stmt->execute();
	$stmt->store_result();
	$num_returns = $stmt->num_rows;
	$stmt->close();
	
	if ($num_returns > 0)
	{
		return true;
	}
	else
	{
		return false;	
	}
}

//update functions for characters

//Update a characters name <--- needs to be written
function updateCharacterName($id, $cname)
{

}

//Update a characters server
function updateServer($id, $cserver)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."characters
		SET 
		character_server = ?
		WHERE
		character_id = ?");	
	$stmt->bind_param("si", $cserver, $cid);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;	
}

//Update a characters ilvl
function updateIlvl($id, $cilvl)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."characters
		SET 
		character_ilvl = ?
		WHERE
		character_id = ?");
	$stmt->bind_param("si", $cilvl, $id);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;	
}

//Update a characters level
function updateLevel($id, $clevel)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."characters
		SET 
		character_level = ?
		WHERE
		character_id = ?");
	$stmt->bind_param("si", $clevel, $id);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;	
}

//Update a characters class
function updateClass($id, $cclass)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."characters
		SET 
		character_class = ?
		WHERE
		character_id = ?");
	$stmt->bind_param("si", $cclass, $id);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;	
}

//Update a characters spec
function updateSpec($id, $cspec)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."characters
		SET 
		character_spec = ?
		WHERE
		character_id = ?");
	$stmt->bind_param("si", $cspec, $id);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;	
}

//Update a characters race
function updateRace($id, $crace)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."characters
		SET 
		character_race = ?
		WHERE
		character_id = ?");
	$stmt->bind_param("si", $crace, $id);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;	
}

//Update a characters armory link
function updateArmory($id, $alink)
{
	global $mysqli,$db_table_prefix;
	$stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."characters
		SET 
		armory_link = ?
		WHERE
		character_id = ?");
	$stmt->bind_param("si", $alink, $id);
	$result = $stmt->execute();
	$stmt->close();	
	return $result;	
}

//Load characters for user view
function loadCharacter($character_id){
    try {
      global $db_table_prefix;
      
      $results = array();
      
      $db = pdoConnect();
      
      $sqlVars = array();
      
      $query = "select {$db_table_prefix}characters.character_id as character_id, user_id, character_name, character_server, character_ilvl, character_level, character_spec, character_class, character_race, armory_link, character_raider, added_stamp, last_update_stamp from {$db_table_prefix}characters where {$db_table_prefix}characters.character_id = :character_id";
      
      $sqlVars[':character_id'] = $character_id;
      
      //echo $query;
      $stmt = $db->prepare($query);
      $stmt->execute($sqlVars);
      
      if (!($results = $stmt->fetch(PDO::FETCH_ASSOC))){
          addAlert("danger", "Invalid user id specified");
          $results = array("errors" => 1, "successes" => 0);
      }
      
      $stmt = null;
    
      return $results;
      
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
    }
}

//load characters roster view
function loadRoster($character_id){
    try {
      global $db_table_prefix;
      
      $results = array();
      
      $db = pdoConnect();
      
      $sqlVars = array();
      
      $query = "select {$db_table_prefix}characters.character_id as character_id, user_id, character_name, character_server, character_ilvl, character_level, character_spec, character_class, character_race, armory_link, character_raider, added_stamp, last_update_stamp from {$db_table_prefix}characters where {$db_table_prefix}characters.character_id = :character_id";
      
      $sqlVars[':character_id'] = $character_id;
      
      //echo $query;
      $stmt = $db->prepare($query);
      $stmt->execute($sqlVars);
      
      if (!($results = $stmt->fetch(PDO::FETCH_ASSOC))){
          addAlert("danger", "Invalid user id specified");
          $results = array("errors" => 1, "successes" => 0);
      }
      
      $stmt = null;
    
      return $results;
      
    } catch (PDOException $e) {
      addAlert("danger", "Oops, looks like our database encountered an error.");
      error_log($e->getMessage());
    } catch (ErrorException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
    } catch (RuntimeException $e) {
      addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
      error_log($e->getMessage());
    }
}

//dont think this function is even being used anymore - look into this
/*
function updateFileCache() 
{ 	
	//change name of this function it dosen't update anything
	//only grab data from database here
	//rip out rest and put into mass_update_characters.php asap to make this all work ... hopefully
	global $mysqli,$db_table_prefix;	
	$stmt = $mysqli->prepare("SELECT character_id, armory_link
		FROM ".$db_table_prefix."characters");	
	if (!$stmt)
		return false;

	$stmt->execute();
	$stmt->bind_result($cid, $alink);

	if($stmt->fetch()){
		return array('character_id' => $cid, 'armory_link' => $alink);
		$stmt->close();
	} else {
		return false;
	}
}
*/

function updateFileCache(array $characterdetails, $bnet_string, $locale_string)
{

	$errors = array();
	$successes = array();

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
	}
	return [$obj, $errors, $successes];
}

function updateToon($obj) 
{
		//--------------------------------
		//set up the vars for the new data
		//--------------------------------
		
		//grab the characters name
		$name = $obj->name;
		
		//grab the characters realm name
		$server = $obj->realm;
		
		//grab the characters equipped item level
		$ilvl = $obj->items->averageItemLevelEquipped;
		
		//grab the characters current level
		$level = $obj->level;
		
		//grab the characters class - returns a int
		$className = $obj->class;
	
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

		//setup the color for the character based on class just for flare
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
		
		//grab the characters race - returns a int
		$raceName = $obj->race;
						
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
		
		//grab the current spec of the character
		//0 for spec 1, 1 for second spec, selected spec will have selected flag
		if (isset($obj->talents[0]->selected)) {
			$spec = $obj->talents[0]->spec->name;
		}else{
			$spec = $obj->talents[1]->spec->name;
		}
		
		//grab the pvp variables
		$total_honor_kills = $obj->totalHonorableKills;
			
		//variables for 2's
		$two_slug = $obj->pvp->brackets->ARENA_BRACKET_2v2->slug;
		$two_rating = $obj->pvp->brackets->ARENA_BRACKET_2v2->rating;
		$two_wplayed = $obj->pvp->brackets->ARENA_BRACKET_2v2->weeklyPlayed;
		$two_wwon = $obj->pvp->brackets->ARENA_BRACKET_2v2->weeklyWon;
		$two_wlost = $obj->pvp->brackets->ARENA_BRACKET_2v2->weeklyLost;
		$two_splayed = $obj->pvp->brackets->ARENA_BRACKET_2v2->seasonPlayed;
		$two_swon = $obj->pvp->brackets->ARENA_BRACKET_2v2->seasonWon;
		$two_slost = $obj->pvp->brackets->ARENA_BRACKET_2v2->seasonLost;
		
		//variables for 3's
		$three_slug = $obj->pvp->brackets->ARENA_BRACKET_3v3->slug;
		$three_rating = $obj->pvp->brackets->ARENA_BRACKET_3v3->rating;
		$three_wplayed = $obj->pvp->brackets->ARENA_BRACKET_3v3->weeklyPlayed;
		$three_wwon = $obj->pvp->brackets->ARENA_BRACKET_3v3->weeklyWon;
		$three_wlost = $obj->pvp->brackets->ARENA_BRACKET_3v3->weeklyLost;
		$three_splayed = $obj->pvp->brackets->ARENA_BRACKET_3v3->seasonPlayed;
		$three_swon = $obj->pvp->brackets->ARENA_BRACKET_3v3->seasonWon;
		$three_slost = $obj->pvp->brackets->ARENA_BRACKET_3v3->seasonLost;
		
		//variables for 5's
		$five_slug = $obj->pvp->brackets->ARENA_BRACKET_5v5->slug;
		$five_rating = $obj->pvp->brackets->ARENA_BRACKET_5v5->rating;
		$five_wplayed = $obj->pvp->brackets->ARENA_BRACKET_5v5->weeklyPlayed;
		$five_wwon = $obj->pvp->brackets->ARENA_BRACKET_5v5->weeklyWon;
		$five_wlost = $obj->pvp->brackets->ARENA_BRACKET_5v5->weeklyLost;
		$five_splayed = $obj->pvp->brackets->ARENA_BRACKET_5v5->seasonPlayed;
		$five_swon = $obj->pvp->brackets->ARENA_BRACKET_5v5->seasonWon;
		$five_slost = $obj->pvp->brackets->ARENA_BRACKET_5v5->seasonLost;
		
		//variables for rated battle grounds
		$rbg_slug = $obj->pvp->brackets->ARENA_BRACKET_RBG->slug;
		$rbg_rating = $obj->pvp->brackets->ARENA_BRACKET_RBG->rating;
		$rbg_wplayed = $obj->pvp->brackets->ARENA_BRACKET_RBG->weeklyPlayed;
		$rbg_wwon = $obj->pvp->brackets->ARENA_BRACKET_RBG->weeklyWon;
		$rbg_wlost = $obj->pvp->brackets->ARENA_BRACKET_RBG->weeklyLost;
		$rbg_splayed = $obj->pvp->brackets->ARENA_BRACKET_RBG->seasonPlayed;
		$rbg_swon = $obj->pvp->brackets->ARENA_BRACKET_RBG->seasonWon;
		$rbg_slost = $obj->pvp->brackets->ARENA_BRACKET_RBG->seasonLost;
		
		//grab the characters current title
		$i = 0;
		while (!isset($obj->titles[$i]->selected)) {
			$i++;
		}
		$tid = $i;
		$title = $obj->titles[$tid]->name;
		//title with character name in it
		//$named_title = preg_replace('/\%s/', $name, $title);
		
		//grab the characters professions
		//get primary professions first then secondary if they exist
		$pri_prof_0_name = $obj->professions->primary[0]->name;
		$pri_prof_0_icon = $obj->professions->primary[0]->icon;
		$pri_prof_0_rank = $obj->professions->primary[0]->rank;
		$pri_prof_0_max = $obj->professions->primary[0]->max;
		
		$pri_prof_1_name = $obj->professions->primary[1]->name;
		$pri_prof_1_icon = $obj->professions->primary[1]->icon;
		$pri_prof_1_rank = $obj->professions->primary[1]->rank;
		$pri_prof_1_max = $obj->professions->primary[1]->max;
		
		//grab secondary professions first aid, archaeology, fishing, cooking
		$sec_prof_0_name = $obj->professions->secondary[0]->name;
		$sec_prof_0_icon = $obj->professions->secondary[0]->icon;
		$sec_prof_0_rank = $obj->professions->secondary[0]->rank;
		$sec_prof_0_max = $obj->professions->secondary[0]->max;
		
		$sec_prof_1_name = $obj->professions->secondary[1]->name;
		$sec_prof_1_icon = $obj->professions->secondary[1]->icon;
		$sec_prof_1_rank = $obj->professions->secondary[1]->rank;
		$sec_prof_1_max = $obj->professions->secondary[1]->max;
		
		$sec_prof_2_name = $obj->professions->secondary[2]->name;
		$sec_prof_2_icon = $obj->professions->secondary[2]->icon;
		$sec_prof_2_rank = $obj->professions->secondary[2]->rank;
		$sec_prof_2_max = $obj->professions->secondary[2]->max;
		
		$sec_prof_3_name = $obj->professions->secondary[3]->name;
		$sec_prof_3_icon = $obj->professions->secondary[3]->icon;
		$sec_prof_3_rank = $obj->professions->secondary[3]->rank;
		$sec_prof_3_max = $obj->professions->secondary[3]->max;
		
		//grab the characters guild information
		$gname = $obj->guild->name;
		$grealm = $obj->guild->realm;
		$gbattlegroup = $obj->guild->battlegroup;
		$glevel = $obj->guild->level;
		$gmembers = $obj->guild->members;
		$gachivevmentPoints = $obj->guild->achievementPoints;
		$g_icon = $obj->guild->emblem->icon;
		$g_iconColor = $obj->guild->emblem->iconColor;
		$g_border = $obj->guild->emblem->border;
		$g_borderColor = $obj->guild->emblem->borderColor;
		$g_backgroundColor = $obj->guild->emblem->backgroundColor;
			
		//grab the characters progression data		
}


?>