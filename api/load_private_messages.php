<?php

include('../models/config.php');

set_error_handler('logAllErrors');

// User must be logged in
if (!isUserLoggedIn()){
    addAlert("danger", "You must be logged in to access this resource.");
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}

// Load all pm's for this user based on the user_id
$validator = new Validator();
$limit = $validator->optionalGetVar('limit');
$user_id = $loggedInUser->user_id;

// Add alerts for any failed input validation
foreach ($validator->errors as $error){
    addAlert("danger", $error);
}

if ($user_id != $loggedInUser->user_id){
    // Special case where something funky is going on ...
    addAlert("danger", "Something when wrong. Wrong user id specified.");
} else {
    /*if (!$results = loadPMS($limit, $user_id)) {
    //    echo json_encode(array("errors" => 1, "successes" => 0));
    //    exit();
   }*/
   $results = loadPMS($limit, $user_id);
}

restore_error_handler();

echo json_encode($results);