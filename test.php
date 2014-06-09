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

echo var_dump(parsePermitString("isLoggedInUser(user_id,'3')&always()")).PHP_EOL;

define("PHP_BR", "<br>");

echo SITE_ROOT.PHP_BR;
echo $websiteUrl.PHP_BR;
echo LOCAL_ROOT.PHP_BR;
echo __FILE__.PHP_BR;
echo dirname(__FILE__).PHP_BR;
echo $_SERVER['SERVER_NAME'].PHP_BR;
echo $_SERVER['HTTP_HOST'].PHP_BR;
echo $_SERVER['PHP_SELF'].PHP_BR;
echo $_SERVER['DOCUMENT_ROOT'].PHP_BR;
echo getRelativeDocumentPath(__FILE__).PHP_BR;
echo getAbsoluteDocumentPath(__FILE__).PHP_BR;
?>