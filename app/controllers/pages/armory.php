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

//var_dump($newURL);

//echo $newURL[6];
//echo $newURL[7];
if($newURL[2] !== "us.battle.net") {
echo 'error happened';
}else{
//$json = file_get_contents('https://us.battle.net/api/wow/character/'.$newURL[6].'/'.$newURL[7].'?fields=items,talents,professions');
$json = file_get_contents('https://us.battle.net/api/wow/character/Stonemaul/Lilfade?fields=guild,items,talents,professions,pvp,progression,titles,feed,audit');
$obj = json_decode($json);
//echo $obj->access_token;
echo '<pre>';
var_dump($obj->guild);
echo '</pre>';

//echo 'last mod'. $obj->lastModified;

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

//check for current talent spec and grab data based on the selected spec
if (isset($obj->talents[0]->selected)) {
	/*	talents array $obj->talents[0]->talents[id]->tier
	[id] int
		[tier] int //0-5
		[column] int //0-2
		[spell] //object
			[id] int //spell id
			[name] string //name of spell
			[icon] string //name of spell icon
			[description] string // description of spell name
			[castTime] string //cast time of spell

	//get all talents ->talents[0,1,2,3,4,5]
	echo 'tier:'. $obj->talents[0]->talents[0]->tier;
	echo '<br />';
	echo 'column:'. $obj->talents[0]->talents[0]->column;
	echo '<br />';
	echo 'spell id:'. $obj->talents[0]->talents[0]->spell->id;
	echo '<br />';
	echo 'spell id:'. $obj->talents[0]->talents[0]->spell->name;
	echo '<br />';
	echo 'spell id:'. $obj->talents[0]->talents[0]->spell->icon;
	echo '<br />';
	echo 'spell id:'. $obj->talents[0]->talents[0]->spell->description;
	echo '<br />';
	echo 'spell id:'. $obj->talents[0]->talents[0]->spell->castTime;

	*/
	echo 'spec one is active';
	//echo '<pre>';
	//var_dump($obj->talents[0]->talents);
	//echo '</pre>';
	
	/* glyph array $obj->talents[0]->glyphs->major/minor[id]->field
	[major] int
		[glyph] int //glyph id
		[item] int //item id of glyph
		[name] string //glyph name string
		[icon] string //glyph icon name
	
	echo 'glyph id:'. $obj->talents[0]->glyphs->major[0]->glyph;
	echo '<br />';
	echo 'glyph item:'. $obj->talents[0]->glyphs->major[0]->item;
	echo '<br />';
	echo 'glyph name:'. $obj->talents[0]->glyphs->major[0]->name;
	echo '<br />';
	echo 'glyph icon:'. $obj->talents[0]->glyphs->major[0]->icon;
	echo '<br />';
	*/
	
	//echo '<pre>';
	//var_dump($obj->talents[0]->glyphs);
	//echo '</pre>';
}else{
	echo 'off spec active';
	echo '<pre>';
	var_dump($obj->talents[1]->talents);
	echo '</pre>';
}



/* professions $obj->professions->primary/secondary[id]->field
	[id] int //id of profession
	[name] string //string name of profession
	[icon] string //string name of profession icon
	[rank] int //current level of profession
	[max] int //max level of profession

echo '<br />';
echo '1st Profession'. $obj->professions->primary[0]->name;
echo '<br />';
echo '2nd Profession'. $obj->professions->primary[1]->name;
echo '<br />';
*/

/* titles $obj->titles[id]->field
	[id] int //id of title
	[name] string //string name of title
	[selected] bool //flag shows true on selected title

$i = 0;
while (!isset($obj->titles[$i]->selected)) {
	$i++;
}
$tid = $i;
echo 'current title is: '.$obj->titles[$tid]->name;
*/

/* guild $obj->guild->fields
	[name] string //string name of guild
	[realm] string //string name of realm for guild
	[battlegroup] string //string name of battlegroup
	[level] int //level of guild
	[members] int //number of guild members in guild
	[achievementPoints] int //number of achiev points for guild
	[emblem] obj //emblem object for guild
		[icon] int
		[iconColor] string
		[border] int
		[borderColor] string
		[backgroundColor] string

  ["name"]=> 
  string(9) "NightFury"
  ["realm"]=>
  string(9) "Stonemaul"
  ["battlegroup"]=>
  string(7) "Cyclone"
  ["level"]=>
  int(25)
  ["members"]=>
  int(198)
  ["achievementPoints"]=>
  int(1150)
  ["emblem"]=>
  object(stdClass)#3 (5) {
    ["icon"]=>
    int(126)
    ["iconColor"]=>
    string(8) "ffb1b8b1"
    ["border"]=>
    int(0)
    ["borderColor"]=>
    string(8) "ffffffff"
    ["backgroundColor"]=>
    string(8) "ff003582"
  }
}
*/

/* get the emblem from the armory [not working for now, dont use]
function showEmblem($showlevel=TRUE, $width=215){
		$finalimg = createEmblem($showlevel,$width);
			header('Content-Type: image/png');
			imagepng($finalimg);
		imagedestroy($finalimg);
   	}

function createEmblem($showlevel=TRUE, $width=215){
if ($width > 1 AND $width < 215){
	$height = ($width/215)*230;
	$finalimg = imagecreatetruecolor($width, $height);
	$trans_colour = imagecolorallocatealpha($finalimg, 0, 0, 0, 127);
	imagefill($finalimg, 0, 0, $trans_colour);
	imagesavealpha($finalimg,true);
	imagealphablending($finalimg, true);
}
			
//if ($this->guildData['side'] == 0){
	$ring = 'alliance';
//} else {
//	$ring = 'horde';
//}
	   		
$imgOut = imagecreatetruecolor(215, 230);
			
$emblemURL = dirname(__FILE__)."/img/emblems/emblem_".sprintf("%02s",$obj->guild->emblem->icon).".png";
$borderURL = dirname(__FILE__)."/img/borders/border_".sprintf("%02s",$obj->guild->emblem->border).".png";
$ringURL = dirname(__FILE__)."/img/static/ring-".$ring.".png";
$shadowURL = dirname(__FILE__)."/img/static/shadow_00.png";
$bgURL = dirname(__FILE__)."/img/static/bg_00.png";
$overlayURL = dirname(__FILE__)."/img/static/overlay_00.png";
$hooksURL = dirname(__FILE__)."/img/static/hooks.png";
$levelURL = dirname(__FILE__)."/img/static/";
			
imagesavealpha($imgOut,true);
imagealphablending($imgOut, true);
$trans_colour = imagecolorallocatealpha($imgOut, 0, 0, 0, 127);
imagefill($imgOut, 0, 0, $trans_colour);
			
$ring = imagecreatefrompng($ringURL);
$ring_size = getimagesize($ringURL);
			
$emblem = imagecreatefrompng($emblemURL);
$emblem_size = getimagesize($emblemURL);
imagelayereffect($emblem, IMG_EFFECT_OVERLAY);
$emblemcolor = preg_replace('/^ff/i','',$obj->guild->emblem->iconColor);
$color_r = hexdec(substr($emblemcolor,0,2));
$color_g = hexdec(substr($emblemcolor,2,2));
$color_b = hexdec(substr($emblemcolor,4,2));
imagefilledrectangle($emblem,0,0,$emblem_size[0],$emblem_size[1],imagecolorallocatealpha($emblem, $color_r, $color_g, $color_b,0));
			
			
$border = imagecreatefrompng($borderURL);
$border_size = getimagesize($borderURL);
imagelayereffect($border, IMG_EFFECT_OVERLAY);
$bordercolor = preg_replace('/^ff/i','',$obj->guild->emblem->borderColor);
$color_r = hexdec(substr($bordercolor,0,2));
$color_g = hexdec(substr($bordercolor,2,2));
$color_b = hexdec(substr($bordercolor,4,2));
imagefilledrectangle($border,0,0,$border_size[0]+100,$border_size[0]+100,imagecolorallocatealpha($border, $color_r, $color_g, $color_b,0));
			
$shadow = imagecreatefrompng($shadowURL);
			
$bg = imagecreatefrompng($bgURL);
$bg_size = getimagesize($bgURL);
imagelayereffect($bg, IMG_EFFECT_OVERLAY);
$bgcolor = preg_replace('/^ff/i','',$obj->guild->emblem->backgroundColor);
$color_r = hexdec(substr($bgcolor,0,2));
$color_g = hexdec(substr($bgcolor,2,2));
$color_b = hexdec(substr($bgcolor,4,2));
imagefilledrectangle($bg,0,0,$bg_size[0]+100,$bg_size[0]+100,imagecolorallocatealpha($bg, $color_r, $color_g, $color_b,0));
			
			
$overlay = imagecreatefrompng($overlayURL);
$hooks = imagecreatefrompng($hooksURL);
			
$x = 20;
$y = 23;

$emblemHideRing = false;
			
if (!$emblemHideRing){
	imagecopy($imgOut,$ring,0,0,0,0, $ring_size[0],$ring_size[1]);
}

$size = getimagesize($shadowURL);
imagecopy($imgOut,$shadow,$x,$y,0,0, $size[0],$size[1]);
imagecopy($imgOut,$bg,$x,$y,0,0, $bg_size[0],$bg_size[1]);
imagecopy($imgOut,$emblem,$x+17,$y+30,0,0, $emblem_size[0],$emblem_size[1]);
imagecopy($imgOut,$border,$x+13,$y+15,0,0, $border_size[0],$border_size[1]);
$size = getimagesize($overlayURL);
imagecopy($imgOut,$overlay,$x,$y+2,0,0, $size[0],$size[1]);
$size = getimagesize($hooksURL);
imagecopy($imgOut,$hooks,$x-2,$y,0,0, $size[0],$size[1]);
			
if ($showlevel){
	$level = $obj->guild->level;
	if ($level < 10){
		$levelIMG = imagecreatefrompng($levelURL.$level.".png");
	} else {
		$digit[1] = substr($level,0,1);
		$digit[2] = substr($level,1,1);
		$digit1 = imagecreatefrompng($levelURL.$digit[1].".png");
		$digit2 = imagecreatefrompng($levelURL.$digit[2].".png");
		$digitwidth = imagesx($digit1);
		$digitheight = imagesy($digit1);
		$levelIMG = imagecreatetruecolor($digitwidth*2,$digitheight);
		$trans_colour = imagecolorallocatealpha($levelIMG, 0, 0, 0, 127);
		imagefill($levelIMG, 0, 0, $trans_colour);
		imagesavealpha($levelIMG,true);
		imagealphablending($levelIMG, true);
		// Last image added first because of the shadow need to be behind first digit
		imagecopy($levelIMG,$digit2,$digitwidth-12,0,0,0, $digitwidth, $digitheight);
		imagecopy($levelIMG,$digit1,12,0,0,0, $digitwidth, $digitheight);
	}
	$size[0] = imagesx($levelIMG);
	$size[1] = imagesy($levelIMG);
	$levelemblem = imagecreatefrompng($ringURL);
	imagesavealpha($levelemblem,true);
	imagealphablending($levelemblem, true);
	imagecopy($levelemblem,$levelIMG,(215/2)-($size[0]/2),(215/2)-($size[1]/2),0,0,$size[0],$size[1]);
	imagecopyresampled($imgOut, $levelemblem, 143, 150,0,0, 215/3, 215/3, 215, 215);
}
			
if ($width > 1 AND $width < 215){
	imagecopyresampled($finalimg, $imgOut, 0, 0, 0, 0, $width, $height, 215, 230);
} else {
	$finalimg = $imgOut;
}
imagepng($finalimg,$imgfile);
return $finalimg;
}


//$imgguild = showEmblem();




echo '<br /><br />';
//$img = null;
//echo '<img src="$imgguild" alt"guild image" />';
*/

/* gather pvp stats for selected character $obj->pvp->brackets->named_bracket->fields
	[brackets]
		[ARENA_BRACKET_2v2]
			[slug]
			[rating]
			[weeklyPlayed]
			[weeklyWon]
			[weeklyLost]
			[seasonPlayed]
			[seasonWon]
			[seasonLost]
		[ARENA_BRACKET_3v3]
			[slug]
			[rating]
			[weeklyPlayed]
			[weeklyWon]
			[weeklyLost]
			[seasonPlayed]
			[seasonWon]
			[seasonLost]
		[ARENA_BRACKET_5v5]
			[slug]
			[rating]
			[weeklyPlayed]
			[weeklyWon]
			[weeklyLost]
			[seasonPlayed]
			[seasonWon]
			[seasonLost]
		[ARENA_BRACKET_RBG]
			[slug]
			[rating]
			[weeklyPlayed]
			[weeklyWon]
			[weeklyLost]
			[seasonPlayed]
			[seasonWon]
			[seasonLost]
*/

/* gather progression data for raids $obj->progression[id]
	[progression]
		[array_id]
			[name]
			[normal]
			[heroic]
			[id]
			[bosses]
				[array_id]
					[id]
					[name]
					if lfrflag
					[lfrKills]
					lfrTimestamp]
					[normalKills]
					[normalTimestamp]
					if heroicflag
					[heroicKills]
					[heroicTimestamp]
					if flexflag
					[flexKills]
					[flexTimestamp]
*/

/* gather feed data from character $obj->feed->

*/

//echo '<pre>';
//var_dump($obj->professions);
//echo '</pre>';

/* get class color based on class name
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
 }elseif($className == "10") { $class = "Monk"; //Monk
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
 

$i = 0;
while (!isset($obj->titles[$i]->selected)) {
	$i++;
}
$tid = $i;
$title = $obj->titles[$tid]->name;

//$patt = ["%s"];
//$rep = [$name];
$named_title = preg_replace('/\%s/', $name, $title);

echo 'Your character is '.$named_title.' of '.$server.', a level:'.$level.' '.$spec.' '.$race.' '.$class.' with a ilvl of: '.$ilvl;
}
?>