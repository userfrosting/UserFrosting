<?php

require_once("../models/config.php");

// User must be logged in
if (!isUserLoggedIn()){
    addAlert("danger", "You must be logged in to access the pm system.");
    header("Location: ../login.php");
    exit();
}

setReferralPage(getAbsoluteDocumentPath(__FILE__));

// Automatically forward to the user's default home page
//$home_page = SITE_ROOT . fetchUserHomePage($loggedInUser->user_id);

header( "Location: pm.php" ) ;
exit();