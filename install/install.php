<?php 
/*
UserCake Version: 2.0.2
http://usercake.com
*/
require_once("../models/db-settings.php");
require_once("../models/funcs.php");
require_once("../models/languages/en.php");
require_once("../models/class.mail.php");
require_once("../models/class.user.php");
require_once("../models/class.newuser.php");

session_start();

if (fetchUserAuthById('1')){
	addAlert("danger", lang("MASTER_ACCOUNT_EXISTS"));
	header('Location: complete.php');
	exit();
}

$db_issue = false;
$errors = array();
$successes = array();

$permissions_sql = "
CREATE TABLE IF NOT EXISTS `".$db_table_prefix."permissions` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(150) NOT NULL,
`is_default` tinyint(1) NOT NULL,
`can_delete` tinyint(1) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
";

$permissions_entry = "
INSERT INTO `".$db_table_prefix."permissions` (`id`, `name`, `is_default`, `can_delete`) VALUES
(1, 'User', 1, 0),
(2, 'Administrator', 0, 0);
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
`active` tinyint(1) NOT NULL,
`title` varchar(150) NOT NULL,
`sign_up_stamp` int(11) NOT NULL,
`last_sign_in_stamp` int(11) NOT NULL,
`enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Specifies if the account is enabled.  Disabled accounts cannot be logged in to, but they retain all of their data and settings.',
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$user_permission_matches_sql = "
CREATE TABLE IF NOT EXISTS `".$db_table_prefix."user_permission_matches` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`user_id` int(11) NOT NULL,
`permission_id` int(11) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
";

// Add admin as a user and administrator
$user_permission_matches_entry = "
INSERT INTO `".$db_table_prefix."user_permission_matches` (`id`, `user_id`, `permission_id`) VALUES
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
(1, 'website_name', 'UserFrosting'),
(2, 'website_url', 'localhost/'),
(3, 'email', 'noreply@myfrosting.com'),
(4, 'activation', '0'),
(5, 'resend_activation_threshold', '0'),
(6, 'language', 'models/languages/en.php'),
(7, 'template', 'models/site-templates/default.css'),
(8, 'can_register', '1'),
(9, 'new_user_title', 'New Member'),
(10, 'root_account_config_token', '" . md5(uniqid(mt_rand(), false)) . "');
";

$pages_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."pages` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`page` varchar(150) NOT NULL,
`private` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=47 ;
";

$pages_entry = "INSERT INTO `".$db_table_prefix."pages` (`id`, `page`, `private`) VALUES
(1, 'index.php', 0),
(2, 'register.php', 0),
(3, 'login.php', 0),
(4, 'process_login.php', 0),
(5, 'user_create_user.php', 0),
(7, 'forgot_password.php', 0),
(8, 'resend_activation.php', 0),
(9, 'user_resend_activation.php', 0),
(10, 'user_reset_password.php', 0),
(11, 'header-loggedout.php', 0),
(12, 'jumbotron_links.php', 0),
(13, 'account.php', 1),
(14, 'logout.php', 1),
(15, 'dashboard.php', 1),
(16, 'user_update_account_settings.php', 1),
(17, 'load_form_user.php', 1),
(18, 'user_alerts.php', 1),
(19, 'header.php', 1),
(20, 'account_settings.php', 1),
(21, 'load_current_user.php', 1),
(23, 'load_permissions.php', 1),
(24, 'load_site_pages.php', 1),
(25, 'load_site_settings.php', 1),
(26, 'dashboard_admin.php', 1),
(27, 'site_pages.php', 1),
(28, 'site_settings.php', 1),
(29, 'update_site_settings.php', 1),
(30, 'update_user.php', 1),
(31, 'create_permission.php', 1),
(32, 'update_permission.php', 1),
(33, 'create_user.php', 1),
(34, 'update_page_permission.php', 1),
(35, 'delete_permission.php', 1),
(36, 'load_users.php', 1),
(37, 'admin_activate_user.php', 1),
(38, 'users.php', 1),
(39, 'user_details.php', 1),
(40, 'includes.php', 0),
(41, 'update_user_enabled.php', 1),
(42, 'admin_load_permissions.php', 1),
(43, '404.php', 0),
(44, 'delete_user_dialog.php', 1),
(45, 'load_user.php', 1),
(46, 'delete_user.php', 1);
";

$permission_page_matches_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."permission_page_matches` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`permission_id` int(11) NOT NULL,
`page_id` int(11) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=76 ;
";

$permission_page_matches_entry = "INSERT INTO `".$db_table_prefix."permission_page_matches` (`id`, `permission_id`, `page_id`) VALUES
(1, 1, 13),
(2, 1, 14),
(3, 2, 13),
(4, 2, 17),
(12, 2, 14),
(23, 2, 34),
(24, 2, 29),
(25, 2, 27),
(26, 2, 28),
(27, 2, 25),
(28, 2, 24),
(29, 2, 23),
(30, 2, 35),
(31, 2, 31),
(32, 2, 19),
(33, 1, 19),
(34, 2, 26),
(37, 1, 21),
(38, 2, 21),
(40, 2, 30),
(43, 2, 36),
(45, 2, 38),
(47, 1, 18),
(48, 2, 18),
(57, 2, 45),
(58, 1, 22),
(59, 2, 22),
(60, 2, 42),
(63, 2, 44),
(64, 2, 41),
(65, 2, 46),
(66, 1, 20),
(67, 2, 20),
(68, 1, 16),
(69, 2, 16),
(70, 2, 39),
(71, 1, 15),
(72, 2, 15),
(73, 2, 37),
(74, 2, 32),
(75, 2, 33);
";

$stmt = $mysqli->prepare($configuration_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."configuration table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing ".$db_table_prefix."configuration table.</p>";
    $db_issue = true;
}

$stmt = $mysqli->prepare($configuration_entry);
if($stmt->execute())
{
    $successes[] = "<p>Inserted basic config settings into ".$db_table_prefix."configuration table.....</p>";
}
else
{
    $errors[] = "<p>Error inserting config settings access.</p>";
    $db_issue = true;
}

$stmt = $mysqli->prepare($permissions_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."permissions table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing ".$db_table_prefix."permissions table.</p>";
    $db_issue = true;
}

$stmt = $mysqli->prepare($permissions_entry);
if($stmt->execute())
{
    $successes[] = "<p>Inserted 'User' and 'Admin' groups into ".$db_table_prefix."permissions table.....</p>";
}
else
{
    $errors[] = "<p>Error inserting permissions.</p>";
    $db_issue = true;
}

$stmt = $mysqli->prepare($user_permission_matches_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."user_permission_matches table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing ".$db_table_prefix."user_permission_matches table.</p>";
    $db_issue = true;
}

$stmt = $mysqli->prepare($user_permission_matches_entry);
if($stmt->execute())
{
    $successes[] = "<p>Added 'Admin' entry for first user in ".$db_table_prefix."user_permission_matches table.....</p>";
}
else
{
    $errors[] = "<p>Error inserting admin into ".$db_table_prefix."user_permission_matches.</p>";
    $db_issue = true;
}

$stmt = $mysqli->prepare($pages_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."pages table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing ".$db_table_prefix."pages table.</p>";
    $db_issue = true;
}

$stmt = $mysqli->prepare($pages_entry);
if($stmt->execute())
{
    $successes[] = "<p>Added default pages to ".$db_table_prefix."pages table.....</p>";
}
else
{
    $errors[] = "<p>Error inserting pages into ".$db_table_prefix."pages.</p>";
    $db_issue = true;
}

$stmt = $mysqli->prepare($permission_page_matches_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."permission_page_matches table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing ".$db_table_prefix."permission_page_matches table.</p>";
    $db_issue = true;
}

$stmt = $mysqli->prepare($permission_page_matches_entry);
if($stmt->execute())
{
    $successes[] = "<p>Added default access to ".$db_table_prefix."permission_page_matches table.....</p>";
}
else
{
    $errors[] = "<p>Error adding default access to ".$db_table_prefix."user_permission_matches.</p>";
    $db_issue = true;
}

$stmt = $mysqli->prepare($users_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."users table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing users table.</p>";
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

if (count($errors) == 0)
    header('Location: register_root.php');
else
    header('Location: index.php');
exit();	

?>