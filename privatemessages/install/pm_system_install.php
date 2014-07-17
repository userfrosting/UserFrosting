<?php
/**
 * Installer for the private message system
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

// This is the config file in the install directory.
require_once('config.php');
require_once("../../models/db-settings.php");
require_once("../../models/funcs.php");

// Check that database exists, we can connect to it, and that none of the tables already exist.

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

$tables = array(
    $db_table_prefix."plugin_pm"
);

$table_exists_sql = "
SELECT *
FROM information_schema.tables
WHERE table_schema = '$db_name'
    AND table_name = :table
LIMIT 1;";

$stmt = $db->prepare($table_exists_sql);

$tables_found = 0;
foreach ($tables as $table){
    try {
        $stmt->execute(array(":table" => $table));
        if ($r = $stmt->fetch(PDO::FETCH_ASSOC)){
            addAlert("danger", "The database '$db_name' already contains the table '$table'.  Please delete or rename it, then try again.");
            $tables_found++;
        }
    } catch (PDOException $e) {
        addAlert("danger", "Oops, looks like our database encountered an error.");
        error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
        echo json_encode(array("errors" => 1, "successes" => 0));
        exit();
    }
}

if ($tables_found > 0){
    echo json_encode(array("errors" => $tables_found, "successes" => 0));
    exit();
}

$db_issue = false;
$errors = array();
$successes = array();


$pm_sql = "
CREATE TABLE IF NOT EXISTS `".$db_table_prefix."plugin_pm` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`sender_id` int(11) NOT NULL,
`receiver_id` int(11) NOT NULL,
`title` varchar(255) NOT NULL,
`message` text NOT NULL,
`time_sent` int(11) NOT NULL,
`time_read` int(11) NOT NULL DEFAULT '0',
`receiver_read` tinyint(1) NULL DEFAULT '0',
`sender_deleted` tinyint(1) NULL DEFAULT '0',
`receiver_deleted` tinyint(1) NULL DEFAULT '0',
`parent_id` int(11) NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$stmt = $db->prepare($pm_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."plugin_pm table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing plugin_pm table.</p>";
    $db_issue = true;
}


$result = array();

if(!$db_issue) {
    $successes[] = "<p><strong>Install complete, please remove the install directory for safety.</strong></p>";
}
else
    $errors[] = "<p><strong>Database setup did not complete successfully. Table may already exist.</strong></p>";

$result['errors'] = $errors;
$result['successes'] = $successes;
foreach ($errors as $error){
    addAlert("danger", $error);
}
foreach ($successes as $success){
    addAlert("success", $success);
}

echo json_encode(array("errors" => count($errors), "successes" => count($successes)));