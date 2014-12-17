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

// Request method: POST
$ajax = checkRequestMode("post");

// User must be logged in
checkLoggedInUser($ajax);

$validator = new Validator();

$name = $validator->requiredPostVar('name');
$value = $validator->requiredPostVar('value');

// Add alerts for any failed input validation
foreach ($validator->errors as $error){
  addAlert("danger", $error);
}

if (count($validator->errors) > 0){
    apiReturnError($ajax, getReferralPage());
}

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

restore_error_handler();

foreach ($errors as $error){
    addAlert("danger", $error);
}
foreach ($successes as $success){
    addAlert("success", $success);
}

if (count($errors) > 0){
    apiReturnError($ajax, getReferralPage());
} else {
    apiReturnSuccess($ajax, getReferralPage());
}

?>
