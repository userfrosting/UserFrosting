<?php
/* useless for now just a backup
	find out why the why this is even firing on a regular update of character information besides the character name...
	
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
*/
?>