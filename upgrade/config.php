<?php
/*

UserFrosting Version: 0.2.2
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

// Used to force backend scripts to log errors rather than print them as output
function logAllErrors($errno, $errstr, $errfile, $errline, array $errcontext) {
	ini_set("log_errors", 1);
	ini_set("display_errors", 0);
	
    error_log("Error ($errno): $errstr in $errfile on line $errline");
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

require_once("../models/db-settings.php");
require_once("../models/class_validator.php");
require_once("../models/password.php");
require_once("../models/db_functions.php");
require_once("../models/funcs.php");
require_once("../models/languages/en.php");
require_once("../models/class.mail.php");
require_once("../models/class.user.php");
require_once("../models/error_functions.php");
require_once("../models/secure_functions.php");

defined("MENU_TEMPLATES")
    or define("MENU_TEMPLATES", dirname(__FILE__) . "/menu-templates/");

// Construct default site path for inserting into the database
$hostname = $_SERVER['HTTP_HOST'];
$app_path = $_SERVER['PHP_SELF'];

// Get the parent directory of this (the install) directory
$app_dir_raw = dirname(dirname($app_path));

// Replace backslashes in local root (if we're in a windows environment)
$app_dir = str_replace('\\', '/', $app_dir_raw);

// Known issue with dirname: If the path is the root path, dirname will return a single slash.  Otherwise, it does not use a trailing slash. So, we need to check for this.
if ($app_dir == "/"){
    $app_dir = "";
}

$url = $hostname . $app_dir . '/';

session_start();