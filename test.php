<?php

require_once("models/config.php");

// Just some tests, for now

/*
if (PermissionValidators::isUserPrimaryGroup(24,'3')){
    echo "user 24 has primary group 3";
} else {
    echo "no";
}
*/
checkActionPermission('updateUserEmail', array("user_id" => 1));
checkActionPermission('updateUserEmail', array("blah" => 1));

if (checkActionPermission('updateUserDisplayName', array("user_id" => 24))){
    echo "yessss";
}

echo var_dump(parsePermitString("isLoggedInUser(user_id,'3')&always()"));

?>