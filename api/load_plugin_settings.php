<?php
/**
 * Load Plugin settings API page
 *
 * Tested with PHP version 5
 *
 * @author     Bryson Shepard <lilfade@fadedgaming.co>
 * @author     Project Manager: Alex Weissman
 * @copyright  2014 UserFrosting
 * @version    0.2.0
 * @link       http://www.userfrosting.com/
 */

require_once("../models/config.php");

set_error_handler('logAllErrors');

// User must be logged in
if (!isUserLoggedIn()){
    addAlert("danger", "You must be logged in to access this resource.");
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}
$form = "";
//Retrieve settings
if ($result = loadSitePluginSettings()){
    //echo json_encode(array("errors" => 1, "successes" => 0));
    //exit();
}

restore_error_handler();

echo json_encode($result, JSON_FORCE_OBJECT);