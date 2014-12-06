<?php

// This is the config file in the upgrade directory.
require_once("config.php");

// Try to connect to the database.  If failed, return error code
try{
    $db = pdoConnect();
} catch (PDOException $e) {
    addAlert("danger", "Could not connect to database.  Please check your database credentials in `models/db-settings.php`.");
    error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}

global $db_name;

$db_issue = false;
$errors = array();
$successes = array();

//--update config table with version information
$configuration_entry = "INSERT INTO `".$db_table_prefix."configuration` (`id`, `name`, `value`) VALUES
(13, 'software_version', '0.2.2');
";

//--update with new sql tables
$filelist_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."filelist` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`path` varchar(150) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `path` (`path`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;
";

$filelist_entry = "INSERT INTO `".$db_table_prefix."filelist` (`id`, `path`) VALUES
(1, 'account'),
(2, 'forms');
";

$stmt = $db->prepare($configuration_entry);
if($stmt->execute())
{
    $successes[] = "<p>Inserted new config settings into ".$db_table_prefix."configuration table.....</p>";
}
else
{
    $errors[] = "<p>Error inserting config settings access.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($filelist_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."filelist table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing file list table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($filelist_entry);
if($stmt->execute())
{
    $successes[] = "<p>Added default file list to the database</p>";
}
else
{
    $errors[] = "<p>Error adding file list to the database</p>";
    $db_issue = true;
}

$result = array();

if(!$db_issue) {
    $successes[] = "<p><strong>Database upgrade completed.</strong></p>";
}
else
    $errors[] = "<p><strong>There was a error with the database upon upgrade.</strong></p>";

$result['errors'] = $errors;
$result['successes'] = $successes;
foreach ($errors as $error){
    addAlert("danger", $error);
}
foreach ($successes as $success){
    addAlert("success", $success);
}

echo json_encode(array("errors" => count($errors), "successes" => count($successes)));

header('Location: 0.2.2.filechanges.php');
exit();