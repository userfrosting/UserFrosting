<?php

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
    ChromePhp::log($newSettings);

    //validate new settings to make sure its all on the up and up

    // Check to see if this should be a binary or string value, update accordingly
    if ($results = checkBinaryConfig($name)){
        // Assume binary data type, hack to simply change to new value rather then using value
        if ($results == 1){
            ChromePhp::log($results);
        }else{
            ChromePhp::log('0');
        }
    }else{
        // Assume non binary data type
        if (updatePluginConfig($name, $value)){
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