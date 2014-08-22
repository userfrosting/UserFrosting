<?php
/**
 * Update Plugin settings API page
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

$validator = new Validator();
//Forms posted
if (isset($_POST)){
    $name = $validator->requiredPostVar('name');
    $value = $validator->requiredPostVar('value');
    $newSettings = $_POST;
}

if(!empty($newSettings)) {
    // Check to see if this should be a binary or string value, update accordingly
    if ($results = checkBinaryConfig($name)){
        // Assume binary data type, hack to simply change to new value rather then using value
        if ($results[1] == 1){
            if (updateSitePluginSettings($name, 0)){
                $successes[] = lang("CONFIG_UPDATE_SUCCESSFUL");
            }
        }else/*if ($results[1] == 0)*/{
            if (updateSitePluginSettings($name, 1)){
                $successes[] = lang("CONFIG_UPDATE_SUCCESSFUL");
            }
        }/*else{
            $errors[] = lang("NO_DATA");
        }*/
    }else{
        // Assume non binary data type
        if (updateSitePluginSettings($name, $value)){
            $successes[] = lang("CONFIG_UPDATE_SUCCESSFUL");
        }
    }
} else {
    $errors[] = lang("NO_DATA");
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
    header('Location: ' . getReferralPage());
    exit();
}