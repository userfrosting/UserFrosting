<?php
/**
 * Delete PM API page for the private message system
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
include('../../models/db-settings.php');
include('../../models/config.php');
require_once("../models/pm_functions.php");

set_error_handler('logAllErrors');

// User must be logged in
if (!isUserLoggedIn()){
  addAlert("danger", "You must be logged in to access this resource.");
  echo json_encode(array("errors" => 1, "successes" => 0));
  exit();
}

$validator = new Validator();
$msg_id = $validator->requiredPostVar('msg_id');
$user_id = $loggedInUser->user_id;

$field = $validator->optionalPostVar('table'); // receiver_deleted or sender_deleted depending on inbox or outbox
$uid = $validator->optionalPostVar('action'); //receiver_id or sender_id depending on inbox or outbox

// Add alerts for any failed input validation
foreach ($validator->errors as $error){
  addAlert("danger", $error);
}

// Delete the pm from the user's view but not from the database entirely. This is not a true delete
if (!removePM($msg_id, $user_id, $field, $uid)) {
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}else{
    addAlert("success", lang("PM_RECEIVER_DELETION_SUCCESSFUL", array('1')));
}

restore_error_handler();

// Allows for functioning in either ajax mode or graceful degradation to PHP/HTML only  
if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
  echo json_encode(array("errors" => 0, "successes" => 1));
  header('Location: ' . getReferralPage());
  exit();
} else {
  header('Location: ' . getReferralPage());
  exit();
}