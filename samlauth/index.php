<?php

require_once '_toolkit_loader.php';
require_once 'settings.php';


$auth = new OneLogin_Saml2_Auth($settingsInfo);

if (isset($_GET['sso'])) {
    $auth->login();
} else if (isset($_GET['slo'])) {
    $auth->logout();
} else if (isset($_GET['acs'])) {
    require_once("../models/config.php");
    if (isUserLoggedIn()) {
        echo "You're already logged in!";
        exit();
    }

    $auth->processResponse();
    $errors = $auth->getErrors();
    if (!empty($errors)) {
        print_r('<p>'.implode(', ', $errors).'</p>');
    }
    if (!$auth->isAuthenticated()) {
        echo "<p>Not authenticated</p>";
        exit();
    }
    $attrs = $auth->getAttributes();

    if (empty($attrs)) {
        $username = $auth->getNameId();
        $email = $username;
    } else {
        $usernameMapping = 'username';
        $mailMapping =  'email';
        if (!empty($usernameMapping) && isset($attrs[$usernameMapping]) && !empty($attrs[$usernameMapping][0])){
            $username = $attrs[$usernameMapping][0];
        }
        if (!empty($mailMapping) && isset($attrs[$mailMapping])  && !empty($attrs[$mailMapping][0])){
            $email = $attrs[$mailMapping][0];
        }
/*
        $nameMapping = 'name';
        $roleMapping = 'groups';
        if (!empty($nameMapping) && isset($attrs[$nameMapping]) && !empty($attrs[$nameMapping][0])){
            $name = $attrs[$nameMapping][0];
        }
        if (!empty($roleMapping) && isset($attrs[$roleMapping])  && !empty($attrs[$roleMapping][0])){
            $role = $attrs[$roleMapping][0];
        }
*/        
    }

    global $email_login;

    if ($email_login == 1) {
        $exists = emailExists($email);
        if ($exists) {
            $userdetails = fetchUserAuthByEmail($email);
        }
    } else {
        $exists = usernameExists($username);
        if ($exists) {
            $userdetails = fetchUserAuthByUserName($username);
        }
    }

    // Just-in-time provisioning not supported

    if (isset($userdetails)) {
        if ($userdetails["active"] == 0) {
            echo lang("ACCOUNT_INACTIVE");
            exit();
        } else if ($userdetails["enabled"] == 0) {
            echo lang("ACCOUNT_DISABLED");
            exit();
        } else {
            $loggedInUser = new loggedInUser();
            $loggedInUser->email = $userdetails["email"];
            $loggedInUser->user_id = $userdetails["id"];
            $loggedInUser->hash_pw = $userdetails["password"];
            $loggedInUser->title = $userdetails["title"];
            $loggedInUser->displayname = $userdetails["display_name"];
            $loggedInUser->username = $userdetails["user_name"];
            $loggedInUser->alerts = array();

            //Update last sign in
            $loggedInUser->updateLastSignIn();

            // Create the user's CSRF token
            $loggedInUser->csrf_token(true);

            $_SESSION["userCakeUser"] = $loggedInUser;
            $_SESSION["samlauth"] = 1;
            header('Location: '.SITE_ROOT.'account');
        }
    } else {
        echo "<p>The user does not exist.</p>";
        exit();
    }

} else if (isset($_GET['sls'])) {
    $auth->processSLO();
    $errors = $auth->getErrors();
    if (!empty($errors)) {
        print_r('<p>'.implode(', ', $errors).'</p>');
        exit();
    } else {
        require_once("../models/config.php");
        //Log the user out
        if(isUserLoggedIn())
        {
            $loggedInUser->userLogOut();
        }
        // Forward to index root page
        header("Location: " . SITE_ROOT);
        die();
    }
}

?>