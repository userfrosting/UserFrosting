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

// Request method: GET or POST

require_once("../models/config.php");

set_error_handler('logAllErrors');

$validate = new Validator();
$confirm = $validate->optionalPostVar('token');
$initial = $validate->optionalPostVar('initial');

// User has a token and want to reset there password
// Fix code to set lost_password_request to 0 when new pass is set
if(!empty($confirm)) {
    // Add alerts for any failed input validation
    foreach ($validate->errors as $error){
        addAlert("danger", $error);
    }

    // Grab up the token and remove any whitespace
    $token = $validate->requiredPostVar('token');

    // Validate the token to make sure its valid
    if($token == "" || !validateLostPasswordToken($token))
    {
        $errors[] = lang("FORGOTPASS_INVALID_TOKEN");
    } else {

        // Set up variables for new password
        $username = $validate->requiredPostVar('username');
        $password = $validate->requiredPostVar('password');
        $passwordc = $validate->requiredPostVar('passwordc');
        //Fetch user details
        $userdetails = fetchUserAuth('user_name', $username);

        // Get the time stamp of the last request
        $request_time = $userdetails["lost_password_timestamp"];

        // Get the timeout value from the configuration table
        global $token_timeout;
        $current_token_life = time()-$request_time;

        // Check the token time to see if the token is still valid based on the timeout value
        if($current_token_life >= $token_timeout){
            // If not valid make the user restart the password request
            $errors[] = lang("FORGOTPASS_OLD_TOKEN");
            // Reset the password flag
            if(!flagLostPasswordRequest($userdetails["user_name"],0))
            {
                $errors[] = lang("SQL_ERROR");
            }
        }

        //time is good, token is good process the password reset request
        // Check if the password being changed is the same as the current password or not
        if(passwordVerifyUF($password, $userdetails["password"])) {
            $errors[] = lang("ACCOUNT_PASSWORD_NOTHING_TO_UPDATE");
        }

        // Check if the password is empty or not
        if($password == "") {
            $errors[] = lang("ACCOUNT_SPECIFY_NEW_PASSWORD");
            // Check if the confirm password is empty or not
        } else if($passwordc == "") {
            $errors[] = lang("ACCOUNT_SPECIFY_CONFIRM_PASSWORD");
        }
        // Validate length of the password to be changed
        else if(minMaxRange(8,50,$password)) {
            $errors[] = lang("ACCOUNT_NEW_PASSWORD_LENGTH",array(8,50));
        }
        // Check if the Password and PasswordC match or not
        else if($password != $passwordc) {
            $errors[] = lang("ACCOUNT_PASS_MISMATCH");
        }

        // Hash the user's password and update
        $password_hash = passwordHashUF($password);
		if ($password_hash === null){
			$errors[] = lang("PASSWORD_HASH_FAILED");
		}		
		
        // Nab up the user_id from the users information to update the password
        $user_id = $userdetails["id"];

        if(count($errors) == 0){
            // Update password based on the user's id and the new password
            if (updateUserField($user_id, 'password', $password_hash)){
                // Password was updated
                $successes[] = lang("ACCOUNT_PASSWORD_UPDATED");

                // Reset the password flag
                if(!flagLostPasswordRequest($userdetails["user_name"],0))
                {
                    $errors[] = lang("SQL_ERROR");
                }
            } else {
                // Error happened couldn't update password
                $errors[] = lang("FORGOTPASS_COULD_NOT_UPDATE");
            }
        }
    }
}

// Regenerate the token we send to the user everytime this is called
// Forms posted
if(!empty($initial))
{
    $email = $validate->requiredPostVar('email');
    $username = $validate->requiredPostVar('username');

    //Perform some validation
    //Feel free to edit / change as required

    if(trim($email) == "")
    {
        $errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
    }
    //Check to ensure email is in the correct format / in the db
    else if(!isValidEmail($email) || !emailExists($email))
    {
        $errors[] = lang("ACCOUNT_INVALID_EMAIL");
    }

    if(trim($username) == "")
    {
        $errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
    }
    else if(!usernameExists($username))
    {
        $errors[] = lang("ACCOUNT_INVALID_USERNAME");
    }

    //For security create a new activation url;
    $new_activation_token = generateActivationToken();

    if(!updateLastActivationRequest($new_activation_token,$username,$email))
    {
        $errors[] = lang("SQL_ERROR");
    }else{

        if(count($errors) == 0)
        {

            //Check that the username / email are associated to the same account
            if(!emailUsernameLinked($email,$username))
            {
                $errors[] =  lang("ACCOUNT_USER_OR_EMAIL_INVALID");
            }
            else
            {
                //Check if the user has any outstanding lost password requests
                $userdetails = fetchUserAuthByUserName($username);
                if($userdetails["lost_password_request"] == 1)
                {
                    $errors[] = lang("FORGOTPASS_REQUEST_EXISTS");
                }else{
                    //Email the user asking to confirm this change password request
                    //We can use the template builder here

                    //We use the activation token again for the url key it gets regenerated everytime it's used.

                    $mail = new userCakeMail();
                    $confirm_url = lang("CONFIRM")."\n".SITE_ROOT."forgot_password.php?confirm=".$userdetails["activation_token"];
                    $deny_url = lang("DENY")."\n".SITE_ROOT."api/user_reset_password.php?deny=".$userdetails["activation_token"];

                    //Setup our custom hooks
                    $hooks = array(
                        "searchStrs" => array("#CONFIRM-URL#","#DENY-URL#","#USERNAME#"),
                        "subjectStrs" => array($confirm_url,$deny_url,$userdetails["user_name"])
                    );

                    if(!$mail->newTemplateMsg("lost-password-request.txt",$hooks))
                    {
                        $errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
                    }
                    else
                    {
                        if(!$mail->sendMail($userdetails["email"],"Lost password request"))
                        {
                            $errors[] = lang("MAIL_ERROR");
                        }
                        else
                        {
                            //Update the DB to show this account has an outstanding request
                            if(!flagLostPasswordRequest($userdetails["user_name"],1))
                            {
                                $errors[] = lang("SQL_ERROR");
                            }else{
                                $successes[] = lang("FORGOTPASS_REQUEST_SUCCESS");
                            }
                        }
                    }
                }
            }
        }
    }
}

$deny = $validate->optionalGetVar('deny');
// Code below should work on this page without any input and redirect the user back to login.php
// User has denied this request
if(!empty($deny))
{
    $token = trim($deny);

    if($token == "" || !validateLostPasswordToken($token))
    {
        $errors[] = lang("FORGOTPASS_INVALID_TOKEN");
    }
    else
    {

        $userdetails = fetchUserAuthByActivationToken($token);

        if(!flagLostPasswordRequest($userdetails["user_name"],0))
        {
            $errors[] = lang("SQL_ERROR");
        }
        else {
            $successes[] = lang("FORGOTPASS_REQUEST_CANNED");
        }
    }
}

restore_error_handler();

foreach ($errors as $error){
    addAlert("danger", $error);
}
foreach ($successes as $success){
    addAlert("success", $success);
}

if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
    echo json_encode(array(
        "errors" => count($errors),
        "successes" => count($successes)));
} else {
    // Send successes to the login page, while errors should return them to the forgot_password page.
    if(count($errors) == 0) {
        header('Location: ' . SITE_ROOT . 'login.php');
        exit();
    } else {
        header('Location: ' . SITE_ROOT . 'forgot_password.php');
        exit();
    }
}

?>
