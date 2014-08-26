<?php
require_once("../models/config.php");

if(!empty($loggedInUser->username)){
    $username = $loggedInUser->username;
} else {
    $username = "Guest";
}

$hooks = array(
    "#USERNAME#" => $username,
    "#WEBSITENAME#" => $websiteName
);



// Special case for root account
if (!empty($loggedInUser->user_id) == $master_account){
    $user_id = $loggedInUser->user_id;
    $hooks['#HEADERMESSAGE#'] = "<span class='navbar-center navbar-brand'>YOU ARE CURRENTLY LOGGED IN AS ROOT USER</span>";
} elseif (empty($loggedInUser->user_id)) {
    $user_id = 0;
} else {

}

$menu = fetchMenu($user_id, $hooks);

echo '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../css/favicon.ico">

    <title>Welcome to UserFrosting!</title>

    <link rel="icon" type="image/x-icon" href="../css/favicon.ico" />

    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../css/jumbotron-narrow.css" rel="stylesheet">

	<link rel="stylesheet" href="../css/font-awesome.min.css">
	<link rel="stylesheet" href="../css/bootstrap-switch.min.css" type="text/css" />

    <!-- JavaScript -->
    <script src="../js/jquery-1.10.2.min.js"></script>
	<script src="../js/bootstrap.js"></script>
	<script src="../js/userfrosting.js"></script>
	<script src="../js/date.min.js"></script>
    <script src="../js/handlebars-v1.2.0.js"></script>

  </head>';


//Start menu output for testing starting with left nav bar
echo '<div class="collapse navbar-collapse navbar-ex1-collapse">
    <ul class="nav navbar-nav side-nav">';
foreach ($menu as $r => $v){
    if ($v['menu'] == 'left' AND $v['menu'] != 'left-sub'){
        echo "
        <li class='navitem-".$v['class_name']."'><a href='../".$v['page']."'><i class='".$v['icon']."'></i> ".$v['name']."</a></li>
        ";
    }
    if ($v['menu'] == 'left-sub' AND $v['parent_id'] == 0){
        echo "<ul class='nav navbar-nav side-nav'>
            <li class='dropdown'>
            <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='".$v['icon']."'></i> ".$v['name']." <b class='caret'></b></a>
                <ul class='dropdown-menu'>";
                    // Grab submenu items based on parent_id = $v['menu_id']
                    $subs = gatherSubMenuItems($v['menu_id']);

                    // If subs are found print them out to the parent element
                    foreach ($subs as $s){
                        echo "<li class='navitem-".$s['class_name']."'><a href='../".$s['page']."'><i class='".$s['icon']."'></i> ".$s['name']."</a></li>";
                    }
                echo "</ul>
            </li>
        </ul>";
    }
}

echo '</ul>';

//top nav bar
echo '<ul class="nav navbar-master navbar-nav navbar-right">';
foreach ($menu as $r => $v){
    //ChromePhp::log($v['id']);
    if ($v['menu'] == 'top-main' AND $v['menu'] != 'top-main-sub'){
        echo "
        <li class='navitem-".$v['class_name']."'><a href='../".$v['page']."'><i class='".$v['icon']."'></i> ".$v['name']."</a></li>
        ";
    }
    if ($v['menu'] == 'top-main-sub' AND $v['parent_id'] == 0){
        echo "<ul class='nav navbar-nav side-nav'>
            <li class='dropdown'>
            <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='".$v['icon']."'></i> ".$v['name']." <b class='caret'></b></a>
                <ul class='dropdown-menu'>";
                    // Grab submenu items based on parent_id = $v['menu_id']
                    $subs = gatherSubMenuItems($v['menu_id']);

                    // If subs are found print them out to the parent element
                    foreach ($subs as $s){
                        echo "<li class='navitem-".$s['class_name']."'><a href='../".$s['page']."'><i class='".$s['icon']."'></i> ".$s['name']."</a></li>";
                    }
        echo "</ul>
            </li>
        </ul>";
    }
}

echo '</ul></div>';

restore_error_handler();
//echo json_encode($menu);

/*
<!-- Brand and toggle get grouped for better mobile display -->

<div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </button>
    <a class="navbar-brand" href="../account/index.php">#WEBSITENAME#</a>
    #HEADERMESSAGE#
</div>

<div class="collapse navbar-collapse navbar-ex1-collapse">
    <!-- Collect the nav links, forms, and other content for toggling -->

    <ul class="nav navbar-nav side-nav">
        <li class='navitem-dashboard-admin'><a href='../account/dashboard_admin.php'><i class='fa fa-dashboard'></i> Admin Dashboard</a></li>
        <li class='navitem-users'><a href='../account/users.php'><i class='fa fa-users'></i> Users</a></li>
        <li class="navitem-dashboard"><a href="../account/dashboard.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class='navitem-settings'><a href="../account/account_settings.php"><i class="fa fa-gear"></i> Account Settings</a></li>
        <li class='dropdown'>
            <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='fa fa-wrench'></i> Site Settings <b class='caret'></b></a>
            <ul class='dropdown-menu'>
                <li class='navitem-site-settings'><a href='../account/site_settings.php'><i class='fa fa-globe'></i> Site Configuration</a></li>
                <li class='navitem-groups'><a href='../account/groups.php'><i class='fa fa-users'></i> Groups</a></li>
                <li class='navitem-site-pages'><a href='../account/site_authorization.php'><i class='fa fa-key'></i> Authorization</a></li>
            </ul>
        </li>
    </ul>
    <ul class="nav navbar-master navbar-nav navbar-right">
        <li class='navitem-pms'><a href="../privatemessages/pm.php"><i class="fa fa-envelope"></i> Private Messages</a></li>
        <li class="dropdown user-dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> #USERNAME# <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li><a href="../account/account_settings.php"><i class="fa fa-gear"></i> Account Settings</a></li>
                <li class="divider"></li>
                <li><a href="../account/logout.php"><i class="fa fa-power-off"></i> Log Out</a></li>
            </ul>
        </li>
    </ul>
</div><!-- /.navbar-collapse -->*/

