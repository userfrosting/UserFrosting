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

class Character
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
	
	function __construct($current_user_id, $name, $server, $ilvl, $level, $class, $spec, $race, $armory_link, $classColor)
	{
		//Used for display only
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
		}
	}
	
	public function AddCharacter()
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