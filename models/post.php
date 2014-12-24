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

/**~. (c) 2014 Garrett R. Morris, OpenEx.pw .~**->>             
 *Licensed Under the MIT License : http://www.opensource.org/licenses/mit-license.php
 ***************************************/

//
//CSRF Protection
//

$token = security($_POST["csrf_token"]);
if(empty($token))
{
	$errors[] = "a security error occured. please try again";//we know the token is missing, but we don't let the end user know that.
}else{
	if(!$loggedInUser->csrf_validate($token))
	{
		$errors[] = "a security error occured. please try again.";//the token was wrong, but same here.
	}
}

/*
basic usage

class:
$loggedInUser->csrf_token(true); <-- creates the token

$loggedInUser->csrf_token; <-- accesses the token from its storage in the session object

$loggedInUser->csrf_validate(); <-- validates and regenerates the token

form_protect($loggedInUser->csrf_token); <--inserts the hidden form value and users token inside of a form element where ever its called

require_once 'models/post.php; <--includes this file in the page you need to validate user inputted data.

be sure to require once after the $errors = array(); has already been set in the form processing script.

this token system should only be used on forms where a user is logged in. it does no good on register or login.

*/