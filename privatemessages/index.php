<?php
/**
 * Router for the private message system
 *
 * Tested with PHP version 5
 *
 * @author     Bryson Shepard <lilfade@fadedgaming.co>
 * @author     Project Manager: Alex Weissman
 * @copyright  2014 UserFrosting
 * @version    0.1
 * @link       http://www.userfrosting.com/
 * @link       http://www.github.com/lilfade/UF-PMSystem/
 */

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