<?php
require_once("../models/config.php");
require_once("pm_functions.php");

set_error_handler('logAllErrors');

// User must be logged in
if (!isUserLoggedIn()){
    addAlert("danger", "You must be logged in to access this resource.");
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}

/*
 msg_id: msg_id,
 sender_id: $('#' + dialog_id + ' input[name="sender_id"]' ).val(),
 title: $('#' + dialog_id + ' input[name="title"]' ).val(),
 receiver_name: $('#' + dialog_id + ' input[name="receiver_name"]' ).val(),
 message: $('#' + dialog_id + ' input[name="message"]' ).val(),
 csrf_token: $('#' + dialog_id + ' input[name="csrf_token"]' ).val(),
 */

$validate = new Validator();

// Add alerts for any failed input validation
foreach ($validate->errors as $error){
    addAlert("danger", $error);
}

$msg_id = $validate->requiredPostVar("msg_id");
$sender_id = $validate->requiredPostVar("sender_id");
$title = $validate->requiredPostVar("title");
$receiver_name = $validate->requiredPostVar("receiver_name");

//$receiver_info = fetchUserIdByUsername($receiver_name);
//ChromePhp::log($receiver_info);
//$receiver_id = $receiver_info['id'];

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

// if the msg_id is not null then set this as a reply to the message the parent_id is = msg_id and isreply = true
if($msg_id != ""){
    $isreply = true;
    $parent_id = $msg_id;
}

// Call the function to create a message with the required data
if($msg_id){
    if (!createMessage($sender_id, $receiver_name, $title, $message, true, $parent_id)){
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