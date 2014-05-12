<?php
/*
$json1 = file_get_contents('https://us.battle.net/api/wow/data/character/races');
$obj1 = json_decode($json1);
echo '<pre>';
var_dump($obj1);
echo '</pre>';
*/

$url = "http://us.battle.net/wow/en/character/stonemaul/Allfaded/simple";

$newURL = explode("/", $url);

var_dump($newURL);

//echo $newURL[6];
//echo $newURL[7];
if($newURL[2] !== "us.battle.net") {
echo 'error happened';
}else{
//$json = file_get_contents('https://us.battle.net/api/wow/character/'.$newURL[6].'/'.$newURL[7].'?fields=items,talents');
$json = file_get_contents('https://us.battle.net/api/wow/character/Stonemaul/Allfaded?fields=items,talents');
$obj = json_decode($json);
//echo $obj->access_token;
//echo '<pre>';
//var_dump($obj);
//echo '</pre>';

$name = $obj->name;
$server = $obj->realm;
$ilvl = $obj->items->averageItemLevelEquipped;
$level = $obj->level;
$className = $obj->class;
$spec = $obj->talents[0]->spec->name; //0 for selected spec 1 for non selected spec
$raceName = $obj->race;

//$c_spec =  $obj->talents['selectedSpec'];
//$a_spec = $obj->talents['notSelectedSpec'];
//echo $c_spec .' and off spec is '. $a_spec;

if (isset($obj->talents[0]->selected)) {
echo 'spec one is active';
}else{
echo 'off spec active';
}

/*
   	public function getTalents(){
   		return $this->characterData['talents'];
   	}
   	public function getActiveTalents(){
   		return $this->characterData['talents'][$this->characterData['talents']['selectedSpec']];
   	}
   	public function getInactiveTalents(){
   		return $this->characterData['talents'][$this->characterData['talents']['notSelectedSpec']];
   	}
*/

/*
if($className == "6") { $classColor = "#C41F3B"; //Death Knight
 }elseif($className == "11") { $classColor = "#FF7D0A"; //Druid
 }elseif($className == "3") { $classColor = "#ABD473"; //Hunter
 }elseif($className == "8") { $classColor = "#69CCF0"; //Mage
 }elseif($className == "12") { $classColor = "#558A84"; //Monk
 }elseif($className == "2") { $classColor = "#F58CBA"; //Paladin
 }elseif($className == "5") { $classColor = "#FFFFFF"; //Priest
 }elseif($className == "4") { $classColor = "#FFF569"; //Rogue
 }elseif($className == "7") { $classColor = "#0070DE"; //Shaman
 }elseif($className == "9") { $classColor = "#9482C9"; //Warlock
 }elseif($className == "1") { $classColor = "#C79C6E"; //Warrior
 }else { $classColor = "#000000"; }

$results .= "<span style='color: " . $classColor . "'>" . $mname . " -> " . $mrank . "</span><br />";
}
echo $results;
*/
//get name of class
if($className == "6") { $class = "Death Knight"; //Death Knight
 }elseif($className == "11") { $class = "Druid"; //Druid
 }elseif($className == "3") { $class = "Hunter"; //Hunter
 }elseif($className == "8") { $class = "Mage"; //Mage
 }elseif($className == "12") { $class = "Monk"; //Monk
 }elseif($className == "2") { $class = "Paladin"; //Paladin
 }elseif($className == "5") { $class = "Priest"; //Priest
 }elseif($className == "4") { $class = "Rogue"; //Rogue
 }elseif($className == "7") { $class = "Shaman"; //Shaman
 }elseif($className == "9") { $class = "Warlock"; //Warlock
 }elseif($className == "1") { $class = "Warrior"; //Warrior
 }else { $class = "none"; }

//get name of race
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
 }else { $race = "none"; } 
 
echo 'Your character is '.$name.' of '.$server.', a level:'.$level.' '.$spec.' '.$race.' '.$class.' with a ilvl of: '.$ilvl;
}
?>