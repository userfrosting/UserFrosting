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

class CharacterUpdate
{
	public $status = false;
	private $current_user_id;
	private $name;
	private $server;
	private $ilvl;
	private $level;
	private $class;
	private $spec;
	private $race;
	private $armory_link;
	private $classColor;
	public $sql_failure = false;
	public $name_taken = false;
	public $success = NULL;
	
	function __construct() //$current_user_id, $name, $server, $ilvl, $level, $class, $spec, $race, $armory_link, $classColor)
	{
		/*//Used for display only
		$this->current_user_id = $current_user_id;
		$this->name = $name;
		$this->server = $server;
		$this->ilvl = $ilvl;
		$this->level = $level;
		$this->class = $class;
		$this->spec = $spec;
		$this->race = $race;
		$this->armory_link = $armory_link;
		$this->class_color = $classColor;
		
		if(characterNameExists($this->name))
		{
			$this->name_taken = true;
		}
		else
		{
			//No problems have been found.
			$this->status = true;
		}*/
		
	}
	
	public function ArmoryLink()
	{
		try {
			global $db_table_prefix;
      
			$results = array();
      
			$db = pdoConnect();
			
			$sqlVars = array();
			
			$query = "SELECT id, armory_link from {$db_table_prefix}characters";
	  
			//$sqlVars[':character_id'] = $character_id;
      
			//echo $query;
			$stmt = $db->prepare($query);
			$stmt->execute($sqlVars);
      
			while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
				//do update logic here
				
				$id = $r['id'];
				$a = $r['armory_link'];
				//$results[$id] = $r;
				
				//grab the armory data from here
				$newURL = explode("/", $a);

				if($newURL[2] !== "us.battle.net") {
					echo 'error happened';
				}else{
				$json = file_get_contents('https://us.battle.net/api/wow/character/'.$newURL[6].'/'.$newURL[7].'?fields=guild,items,talents,professions,pvp,progression,titles,feed,audit');
				$obj = json_decode($json);
				
				//set up the vars for the new data
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
				
				//grab the pvp variables
				
				//grab the characters current title
				$i = 0;
				while (!isset($obj->titles[$i]->selected)) {
					$i++;
				}
				$title_array_id = $i;
				$title = $obj->titles[$tid]->name;
				
				//grab the characters professions
				
				//grab the characters guild information
				
				//grab the characters progression data
				
				//update the tables with the new data we got from the armory
				
				
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
	
	public function UpdateCharacter()
	{
		global $mysqli,$db_table_prefix;
		// Default inserted value, in case of errors
		$inserted_id = -1;
		//ChromePhp::log($this->);
		//Prevent this function being called if there were construction errors
		if($this->status)
		{
			//Insert the user into the database providing no errors have been found.
			$stmt = $mysqli->prepare("INSERT INTO ".$db_table_prefix."characters (user_id, character_name, character_server, 
			character_ilvl, character_level, character_spec, character_class, character_race, armory_link, class_color, character_raider, added_stamp, last_update_stamp)
			VALUES (?, ?, ?, ?,	?, ?, ?, ?,	?, ?, '0', '".time()."', '".time()."')");
			//ChromePhp::log('got data binding');
			//$current_user_id, $name, $server, $ilvl, $level, $class, $spec, $race
			$stmt->bind_param("issiisssss", $_SESSION["userCakeUser"]->user_id, $this->name, $this->server, $this->ilvl, $this->level, $this->spec, $this->class, $this->race, $this->armory_link, $this->class_color);
			//ChromePhp::log('ready to send');
			$stmt->execute();
			//ChromePhp::log('sent returning');
			$inserted_id = $mysqli->insert_id;
			$stmt->close();
		}
	return $inserted_id;
	}
}
?>