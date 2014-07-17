<?php
/**
 * Send PM API page for the private message system
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
require_once("../../models/config.php");
require_once("../models/pm_functions.php");

set_error_handler('logAllErrors');

// User must be logged in
if (!isUserLoggedIn()){
    addAlert("danger", "You must be logged in to access this resource.");
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}

$validate = new Validator();

// Add alerts for any failed input validation
foreach ($validate->errors as $error){
    addAlert("danger", $error);
}

$msg_id = $validate->optionalPostVar("msg_id");
$sender_id = $validate->requiredPostVar("sender_id");
$title = $validate->requiredPostVar("title");

if(!$msg_id){
    $receiver_name = $validate->requiredPostVar("receiver_name");
    $receiver_info = fetchUserIdByDisplayname($receiver_name);
    $receiver_id = $receiver_info['id'];
} else {
    $receiver_id = $validate->requiredPostVar("receiver_name");
}

$message = $validate->requiredPostVar("message");
$csrf_token = $validate->requiredPostVar("csrf_token");

// Validate csrf token
if (!$csrf_token or !$loggedInUser->csrf_validate(trim($csrf_token))){
    addAlert("danger", lang("ACCESS_DENIED"));
    if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
        echo json_encode(array("errors" => 1, "successes" => 0));
    } else {
        header('Location: ../pm.php');
    }
    exit();
}

if(isset($msg_id) && $msg_id >= 1){
    $parent_id = $msg_id;
}else{
    $parent_id = NULL;
}

// Call the function to create a message with the required data
if($isreply = '0' OR '1'){
    if (!createMessage($sender_id, $receiver_id, $title, $message, $parent_id)){
        echo json_encode(array("errors" => 1, "successes" => 0));
        exit();
    }
} else {
    addAlert("danger", "some bad data here");
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}

restore_error_handler();

if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
    echo json_encode(array(
        "errors" => 0,
        "successes" => 1));
} else {
    header('Location: ' . getReferralPage());
    exit();
}