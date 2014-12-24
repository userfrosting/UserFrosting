<?php 
/*

UserFrosting Version: 0.2.2
By Alex Weissman
Copyright (c) 2014

Based on the UserCake user management system, v2.0.2.
Copyright (c) 2009-2012

UserFrosting, like UserCake, is 100% free and open-source.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the 'Software'), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

// This is the config file in the install directory.
require_once("config.php");

// Process POSTed site settings
$validator = new Validator();
$site_url_root = $validator->requiredPostVar('site_url');
$site_name = $validator->requiredPostVar('site_name');
$site_email = $validator->requiredPostVar('site_email');
$user_title = $validator->requiredPostVar('user_title');

// Check and see if email login should be enabled or disabled by default
if($validator->optionalPostVar('select_email') == 'on' ){
    $selected_email = 1;
}else{
    $selected_email = 0;
}

// Check and see if general registration should be enabled or disabled by default
if($validator->optionalPostVar('can_register') == 'on' ){
    $selected_register = 1;
}else{
    $selected_register = 0;
}

// Check and see if email activation should be enabled or disabled by default
if($validator->optionalPostVar('email_activation') == 'on' ){
    $selected_activation = 1;
}else{
    $selected_activation = 0;
}

// If any errors or missing values, send us back
// Add alerts for any failed input validation
foreach ($validator->errors as $error){
  addAlert("danger", $error);
}

if (count($validator->errors) > 0){
    echo json_encode(array("errors" => count($validator->errors), "successes" => 0));
    exit();
}

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
    $db_table_prefix."groups",
    $db_table_prefix."users",
    $db_table_prefix."pages",
    $db_table_prefix."user_group_matches",
    $db_table_prefix."group_page_matches",
    $db_table_prefix."user_action_permits",
    $db_table_prefix."group_action_permits",
    $db_table_prefix."configuration",
    $db_table_prefix."nav",
    $db_table_prefix."nav_group_matches",
    $db_table_prefix."plugin_configuration",
    $db_table_prefix."uf_filelist"
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

$groups_sql = "
CREATE TABLE IF NOT EXISTS `".$db_table_prefix."groups` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(150) NOT NULL,
`is_default` tinyint(1) NOT NULL,
`can_delete` tinyint(1) NOT NULL,
`home_page_id` int(11) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
";

$groups_entry = "
INSERT INTO `".$db_table_prefix."groups` (`id`, `name`, `is_default`, `can_delete`, `home_page_id`) VALUES
(1, 'User', 2, 0, 4),
(2, 'Administrator', 0, 0, 5);
";

$users_sql = "
CREATE TABLE IF NOT EXISTS `".$db_table_prefix."users` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`user_name` varchar(50) NOT NULL,
`display_name` varchar(50) NOT NULL,
`password` varchar(255) NOT NULL,
`email` varchar(150) NOT NULL,
`activation_token` varchar(225) NOT NULL,
`last_activation_request` int(11) NOT NULL,
`lost_password_request` tinyint(1) NOT NULL,
`lost_password_timestamp` int(11) NULL,
`active` tinyint(1) NOT NULL,
`title` varchar(150) NOT NULL,
`sign_up_stamp` int(11) NOT NULL,
`last_sign_in_stamp` int(11) NOT NULL,
`enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Specifies if the account is enabled.  Disabled accounts cannot be logged in to, but they retain all of their data and settings.',
`primary_group_id` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Specifies the primary group for the user.',
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$user_group_matches_sql = "
CREATE TABLE IF NOT EXISTS `".$db_table_prefix."user_group_matches` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`user_id` int(11) NOT NULL,
`group_id` int(11) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
";

// Add root account as a user and administrator
$user_group_matches_entry = "
INSERT INTO `".$db_table_prefix."user_group_matches` (`id`, `user_id`, `group_id`) VALUES
(1, 1, 1),
(2, 1, 2);
";

$configuration_sql = "
CREATE TABLE IF NOT EXISTS `".$db_table_prefix."configuration` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(150) NOT NULL,
`value` varchar(150) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;
";

$configuration_entry = "
INSERT INTO `".$db_table_prefix."configuration` (`id`, `name`, `value`) VALUES
(1, 'website_name', '".$site_name."'),
(2, 'website_url', '".$site_url_root."'),
(3, 'email', '".$site_email."'),
(4, 'activation', ".$selected_activation."),
(5, 'resend_activation_threshold', '0'),
(6, 'language', 'models/languages/en.php'),
(8, 'can_register', ".$selected_register."),
(9, 'new_user_title', '".$user_title."'),
(10, 'root_account_config_token', '" . md5(uniqid(mt_rand(), false)) . "'),
(11, 'email_login', '".$selected_email."'),
(12, 'token_timeout', '10800'),
(13, 'version', '0.2.2');
";

$pages_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."pages` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`page` varchar(150) NOT NULL,
`private` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;
";

$pages_entry = "INSERT INTO `".$db_table_prefix."pages` (`id`, `page`, `private`) VALUES
(1, 'forms/table_users.php', 1),
(3, 'account/logout.php', 1),
(4, 'account/dashboard.php', 1),
(5, 'account/dashboard_admin.php', 1),
(6, 'account/account_settings.php', 1),
(7, 'account/site_authorization.php', 1),
(8, 'account/site_settings.php', 1),
(9, 'account/users.php', 1),
(10, 'account/user_details.php', 1),
(11, 'account/index.php', 0),
(12, 'account/groups.php', 1),
(13, 'forms/form_user.php', 1),
(14, 'forms/form_group.php', 1),
(15, 'forms/form_confirm_delete.php', 1),
(16, 'forms/form_action_permits.php', 1);
";

$group_page_matches_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."group_page_matches` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`group_id` int(11) NOT NULL,
`page_id` int(11) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24;
";

$group_page_matches_entry = "INSERT INTO `".$db_table_prefix."group_page_matches` (`id`, `group_id`, `page_id`) VALUES
(1, 1, 1),
(3, 2, 3),
(4, 2, 4),
(5, 2, 5),
(6, 2, 6),
(7, 2, 7),
(8, 2, 8),
(9, 2, 9),
(10, 2, 10),
(11, 2, 11),
(12, 2, 12),
(13, 2, 13),
(14, 2, 14),
(15, 2, 15),
(16, 2, 16),
(19, 1, 3),
(20, 1, 4),
(21, 1, 6),
(22, 1, 13),
(23, 1, 15);
";

// Group-level permits
$group_action_permits_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."group_action_permits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `permits` varchar(400) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;
";

$group_action_permits_entry = "INSERT INTO `".$db_table_prefix."group_action_permits` (`id`, `group_id`, `action`, `permits`) VALUES
(1, 1, 'updateUserEmail', 'isLoggedInUser(user_id)'),
(2, 1, 'updateUserPassword', 'isLoggedInUser(user_id)'),
(3, 1, 'loadUser', 'isLoggedInUser(user_id)'),
(4, 1, 'loadUserGroups', 'isLoggedInUser(user_id)'),
(5, 2, 'updateUserEmail', 'always()'),
(6, 2, 'updateUserPassword', 'always()'),
(7, 2, 'updateUser', 'always()'),
(8, 2, 'updateUserDisplayName', 'always()'),
(9, 2, 'updateUserTitle', 'always()'),
(10, 2, 'updateUserEnabled', 'always()'),
(11, 2, 'loadUser', 'always()'),
(12, 2, 'loadUserGroups', 'always()'),
(13, 2, 'loadUsers', 'always()'),
(14, 2, 'deleteUser', 'always()'),
(15, 2, 'activateUser', 'always()'),
(16, 2, 'loadGroups', 'always()');
";

// User-level permits
$user_action_permits_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."user_action_permits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `permits` varchar(400) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$plugin_configuration_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."plugin_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `value` varchar(150) NOT NULL,
  `binary` int(1) NOT NULL,
  `variable` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$nav_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."nav` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `menu` varchar(75) NOT NULL,
  `page` varchar(175) NOT NULL,
  `name` varchar(150) NOT NULL,
  `position` int(11) NOT NULL,
  `class_name` varchar(150) NOT NULL,
  `icon` varchar(150) NOT NULL,
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;
";


$nav_entry = "INSERT INTO `".$db_table_prefix."nav` (`id`, `menu`, `page`, `name`, `position`, `class_name`, `icon`, `parent_id`) VALUES
(1, 'left', 'account/dashboard_admin.php', 'Admin Dashboard', 1, 'dashboard-admin', 'fa fa-dashboard', 0),
(2, 'left', 'account/users.php', 'Users', 2, 'users', 'fa fa-users', 0),
(3, 'left', 'account/dashboard.php', 'Dashboard', 3, 'dashboard', 'fa fa-dashboard', 0),
(4, 'left', 'account/account_settings.php', 'Account Settings', 4, 'settings', 'fa fa-gear', 0),
(5, 'left-sub', '#', 'Site Settings', 5, '', 'fa fa-wrench', 0),
(6, 'left-sub', 'account/site_settings.php', 'Site Configuration', 6, 'site-settings', 'fa fa-globe', 5),
(7, 'left-sub', 'account/groups.php', 'Groups', 7, 'groups', 'fa fa-users', 5),
(8, 'left-sub', 'account/site_authorization.php', 'Authorization', 8, 'site-pages', 'fa fa-key', 5),
(9, 'top-main-sub', '#', '#USERNAME#', 1, 'site-settings', 'fa fa-user', 0),
(10, 'top-main-sub', 'account/account_settings.php', 'Account Settings', 1, '', 'fa fa-gear', 9),
(11, 'top-main-sub', 'account/logout.php', 'Log Out', 2, '', 'fa fa-power-off', 9);
";

$nav_group_matches_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."nav_group_matches` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;
";

$nav_group_matches_entry = "INSERT INTO `".$db_table_prefix."nav_group_matches` (`id`, `menu_id`, `group_id`) VALUES
(1, 3, 1),
(2, 4, 1),
(3, 9, 1),
(4, 10, 1),
(5, 11, 1),
(6, 1, 2),
(7, 2, 2),
(8, 5, 2),
(9, 6, 2),
(10, 7, 2),
(11, 8, 2);
";

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

$stmt = $db->prepare($configuration_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."configuration table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing ".$db_table_prefix."configuration table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($configuration_entry);
if($stmt->execute())
{
    $successes[] = "<p>Inserted basic config settings into ".$db_table_prefix."configuration table.....</p>";
}
else
{
    $errors[] = "<p>Error inserting config settings access.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($groups_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."groups table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing ".$db_table_prefix."groups table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($groups_entry);
if($stmt->execute())
{
    $successes[] = "<p>Inserted 'User' and 'Admin' groups into ".$db_table_prefix."groups table.....</p>";
}
else
{
    $errors[] = "<p>Error inserting groups.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($user_group_matches_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."user_group_matches table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing ".$db_table_prefix."user_group_matches table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($user_group_matches_entry);
if($stmt->execute())
{
    $successes[] = "<p>Added 'Admin' entry for first user in ".$db_table_prefix."user_group_matches table.....</p>";
}
else
{
    $errors[] = "<p>Error inserting admin into ".$db_table_prefix."user_group_matches.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($pages_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."pages table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing ".$db_table_prefix."pages table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($pages_entry);
if($stmt->execute())
{
    $successes[] = "<p>Added default pages to ".$db_table_prefix."pages table.....</p>";
}
else
{
    $errors[] = "<p>Error inserting pages into ".$db_table_prefix."pages.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($group_page_matches_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."group_page_matches table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing ".$db_table_prefix."group_page_matches table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($group_page_matches_entry);
if($stmt->execute())
{
    $successes[] = "<p>Added default access to ".$db_table_prefix."group_page_matches table.....</p>";
}
else
{
    $errors[] = "<p>Error adding default access to ".$db_table_prefix."user_group_matches.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($users_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."users table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing users table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($group_action_permits_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."group_action_permits table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing group_action_permits table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($group_action_permits_entry);
if($stmt->execute())
{
    $successes[] = "<p>Added default access to ".$db_table_prefix."group_action_permits table.....</p>";
}
else
{
    $errors[] = "<p>Error adding default access to ".$db_table_prefix."group_action_permits.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($user_action_permits_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."user_action_permits table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing user_action_permits table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($plugin_configuration_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."plugin_configuration table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing plugin_configuration table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($nav_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."nav_sql table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing nav table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($nav_entry);
if($stmt->execute())
{
    $successes[] = "<p>Added default navigation items to the database</p>";
}
else
{
    $errors[] = "<p>Error adding default navigation items to the database</p>";
    $db_issue = true;
}

$stmt = $db->prepare($nav_group_matches_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."nav_group_matches table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing nav_group_matches table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($nav_group_matches_entry);
if($stmt->execute())
{
    $successes[] = "<p>Added default navigation group matches to the database</p>";
}
else
{
    $errors[] = "<p>Error adding default navigation group matches to the database</p>";
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
    $successes[] = "<p><strong>Database setup complete, please create the master (root) account.  The configuration token can be found in the 'uc_configuration' table of your database, as the value for 'root_account_config_token'.</strong></p>";
}
else
    $errors[] = "<p><strong>Database setup did not complete successfully.  Please delete all tables and try again.</strong></p>";

$result['errors'] = $errors;
$result['successes'] = $successes;
foreach ($errors as $error){
    addAlert("danger", $error);
}
foreach ($successes as $success){
    addAlert("success", $success);
}

echo json_encode(array("errors" => count($errors), "successes" => count($successes)));
  