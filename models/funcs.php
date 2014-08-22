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

//Functions that do not interact with DB
//------------------------------------------------------------------------------

//Retrieve a list of all .php files in models/languages
function getLanguageFiles()
{
	$directory = "../models/languages/";
	$languages = glob($directory . "*.php");
	//print each file name
	return $languages;
}

//Retrieve a list of all .php files in a given directory
function getPageFiles($directory)
{
	$pages = glob("../" . $directory . "/*.php");
	$row = array();
	//print each file name
	foreach ($pages as $page){
		$page_with_path = $directory . "/" . basename($page);
		$row[$page_with_path] = $page_with_path;
	}
	return $row;
}

//Destroys a session as part of logout
function destroySession($name)
{
	if(isset($_SESSION[$name]))
	{
		$_SESSION[$name] = NULL;
		unset($_SESSION[$name]);
	}
}

//Generate a unique code
function getUniqueCode($length = "")
{	
	$code = md5(uniqid(rand(), true));
	if ($length != "") return substr($code, 0, $length);
	else return $code;
}

//Generate an activation key
function generateActivationToken($gen = null)
{
	do
	{
		$gen = md5(uniqid(mt_rand(), false));
	}
	while(validateActivationToken($gen));
	return $gen;
}

// Master function for validating passwords.  Ensures backwards compatibility with sha1 (usercake) and the old homegrown implementation of crypt
function passwordVerifyUF($password, $hash){
	if (getPasswordHashTypeUF($hash) == "sha1"){
		$salt = substr($hash, 0, 25);		// Extract the salt from the hash
		$hash_input = $salt . sha1($salt . $password);
		if ($hash_input == $hash){
			return true;
		} else {
			return false;
		}
	}	
	// Homegrown implementation (assuming that current install has been using a cost parameter of 12)
	else if (getPasswordHashTypeUF($hash) == "homegrown"){
		/*used for manual implementation of bcrypt*/
		$cost = '12'; 
		if (substr($hash, 0, 60) == crypt($password, "$2y$".$cost."$".substr($hash, 60))){
			return true;
		} else {
			return false;
		}
	// Modern implementation
	} else {
		return password_verify($password, $hash);
	}
}

// Hash a new password.  Uses the modern implementation.
function passwordHashUF($password){
	return password_hash($password, PASSWORD_BCRYPT);
}

function getPasswordHashTypeUF($hash){
	// If the password in the db is 65 characters long, we have an sha1-hashed password.
	if (strlen($hash) == 65)
		return "sha1";
	else if (substr($hash, 0, 7) == "$2y$12$")
		return "homegrown";
	else
		return "modern";
}

//Checks if an email is valid
function isValidEmail($email)
{
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return true;
	}
	else {
		return false;
	}
}

function isValidName($name) {
	return preg_match('/^[A-Za-z0-9 ]+$/', $name);
}

//Inputs language strings from selected language.
function lang($key,$markers = NULL)
{
	global $lang;
	if($markers == NULL)
	{
		$str = $lang[$key];
	}
	else
	{
		//Replace any dyamic markers
		$str = $lang[$key];
		$iteration = 1;
		foreach($markers as $marker)
		{
			$str = str_replace("%m".$iteration."%",$marker,$str);
			$iteration++;
		}
	}
	//Ensure we have something to return
	if($str == "")
	{
		return ("No language key found");
	}
	else
	{
		return $str;
	}
}

//Checks if a string is within a min and max length
function minMaxRange($min, $max, $what)
{
	if(strlen(trim($what)) < $min)
		return true;
	else if(strlen(trim($what)) > $max)
		return true;
	else
	return false;
}

// Basic templating system.  Replaces hooks with specified text
function replaceDefaultHook($str)
{
	global $default_hooks,$default_replace;	
	return (str_replace($default_hooks,$default_replace,$str));
}

// Convert text that might be in a different case or with trailing/leading whitespace to a standard form
function str_normalize($str)
{
	return strtolower(trim($str));
}

// Get the last referral page.
function getReferralPage(){
	if (isset($_SESSION['referral_page'])){
		return $_SESSION['referral_page'];
	} else {
		if(isUserLoggedIn()) {
			return ACCOUNT_ROOT;
		} else {
			return SITE_ROOT;
		}
	}
}

// Set the referral page to the specified page.
function setReferralPage($page){
	$_SESSION['referral_page'] = $page;
}

/**
* Converts phone numbers to the formatting standard
*
* @param   String   $num   A unformatted phone number
* @return  String   Returns the formatted phone number
*/
function formatPhone($num)
{
$num = preg_replace('/[^0-9]/', '', $num);
 
$len = strlen($num);
if($len == 7)
$num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
elseif($len == 10)
$num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num);
 
return $num;
}


//multipurpose security function. works on strings, array's etc.

function security($value) {
   if(is_array($value)) {
      $value = array_map('security', $value);
   } else {
      if(!get_magic_quotes_gpc()) {
         $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
      } else {
         $value = htmlspecialchars(stripslashes($value), ENT_QUOTES, 'UTF-8');
      }
      $value = str_replace("\\", "\\\\", $value);
   }
   return $value;
}

//get ip address
//taken from https://gist.github.com/cballou/2201933
function get_ip_address() {
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (validate_ip($ip)) {
                    return $ip;
                }
            }
        }
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
}

//validate ip address
function validate_ip($ip)
{
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    return true;
}

//getuseragent
//taken from comments @ php.net
function getBrowser() 
{ 
    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) { 
        $bname = 'Internet Explorer'; 
        $ub = "MSIE"; 
    } 
    elseif(preg_match('/Firefox/i',$u_agent)) { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Chrome/i',$u_agent)) { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$u_agent)) { 
        $bname = 'Apple Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Opera/i',$u_agent)) { 
        $bname = 'Opera'; 
        $ub = "Opera"; 
    } 
    elseif(preg_match('/Netscape/i',$u_agent))  { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 
    } 
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        //no match
    }
    $i = count($matches['browser']);
    if ($i != 1) {
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){ $version= $matches['version'][0];}else{ $version= $matches['version'][1];}
    }
    else { $version= $matches['version'][0];}
    if ($version==null || $version=="") {$version="?";}
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}

//to be used with csrf token system

/*
	simply add inside of a form tag like so:
	form_protect($loggedInUser->csrf_token);
	
	then in the processing script:
	
	require_once __DIR__ . '/models/post.php';
	
	< OR >
	
	require_once 'models/post.php';
*/
function form_protect($token)
{
	if(isUserLoggedIn())
	{echo '<input type="hidden" name="csrf_token" value="'. $token .'">';}	
}

// Useful for testing output of API functions
function prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}

// Parse a comment block into a description and array of parameters
function parseCommentBlock($comment){
	$lines = explode("\n", $comment);
	$result = array('description' => "", 'parameters' => array());
	foreach ($lines as $line){
		if (!preg_match('/^\s*\/?\*+\/?\s*$/', $line)){
			// Extract description or parameters
			if (preg_match('/^\s*\**\s*@param\s+(\w+)\s+\$(\w+)\s+(.*)$/', $line, $matches)){
				$type = $matches[1];
				$name = $matches[2];
				$description = $matches[3];
				$result['parameters'][$name] = array('type' => $type, 'description' => $description);
			} else if (preg_match('/^\s*\**\s*@(.*)$/', $line, $matches)){
				// Skip other types of special entities
			} else if (preg_match('/^\s*\**\s*(.*)$/', $line, $matches)){
				$description = $matches[1];
				$result['description'] .= $description;
			}
		}
	}
	return $result;
}

?>
