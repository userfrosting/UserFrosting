<?php

require_once("models/config.php");

// Just some tests, for now

checkActionPermission('updateUserEmail', array("user_id" => 1));
checkActionPermission('updateUserEmail', array("blah" => 1));
checkActionPermission('updateUserDisplay', array("user_id" => 2));

updateUserEmail(1, "yo");
updateUserEmail(2, "yo");

?>