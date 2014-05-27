<?php

class Validator {
	public $errors;

	function __construct() {
		$this->errors = array();
	}
	
	public function requiredGetVar($varname){
		if (isset($_GET[$varname]))
			return htmlentities($_GET[$varname]);
		else {
			$this->errors[] = "Parameter $varname must be specified!";
			return null;
		}
	}
	
	public function requiredPostVar($varname){
		if (isset($_POST[$varname]))
			return htmlentities($_POST[$varname]);
		else {
			$this->errors[] = "Parameter $varname must be specified!";
			return null;
		}
	}
	
	public function optionalGetVar($varname){
		if (isset($_GET[$varname]))
			return htmlentities($_GET[$varname]);
		else
			return null;
	}
	
	public function optionalPostVar($varname){
		if (isset($_POST[$varname]))
			return htmlentities($_POST[$varname]);
		else
			return null;
	}	

	public function optionalPostArray($varname){
		if (isset($_POST[$varname])) {
			$arr = array();
			foreach ($_POST[$varname] as $val){
				$arr[] = htmlentities($val);
			}
			return $arr;
		} else {
			return array();
		}
	}		
		
}

?>