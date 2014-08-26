<?php
/*

UserFrosting Version: 0.2.0
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

// Request method: GET

include('../models/config.php');

// User must be logged in
if (!isUserLoggedIn()){
  addAlert("danger", "You must be logged in to access the account page.");
  header("Location: ../login.php");
  exit();
}

$hooks = array(
		  "#USERNAME#" => $loggedInUser->username,
		  "#WEBSITENAME#" => $websiteName
		  );

// Special case for root account
if ($loggedInUser->user_id == $master_account){
	$hooks['#HEADERMESSAGE#'] = "<span class='navbar-center navbar-brand'>YOU ARE CURRENTLY LOGGED IN AS ROOT USER</span>";
}

//echo fetchUserMenu($loggedInUser->user_id, $hooks)['value'];
$menu = fetchMenu($loggedInUser->user_id, $hooks);

echo '<!-- Brand and toggle get grouped for better mobile display -->

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

    <ul class="nav navbar-nav side-nav">';
foreach ($menu as $r => $v){
    if ($v['menu'] == 'left' AND $v['menu'] != 'left-sub'){
        echo "
        <li class='navitem-".$v['class_name']."'><a href='../".$v['page']."'><i class='".$v['icon']."'></i> ".$v['name']."</a></li>
        ";
    }
    if ($v['menu'] == 'left-sub' AND $v['parent_id'] == 0){
        echo "<li class='dropdown'>
                <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='".$v['icon']."'></i> ".$v['name']." <b class='caret'></b></a>
                <ul class='dropdown-menu'>";
        // Grab submenu items based on parent_id = $v['menu_id']
        $subs = gatherSubMenuItems($v['menu_id']);

        // If subs are found print them out to the parent element
        foreach ($subs as $s){
            echo "<li class='navitem-".$s['class_name']."'><a href='../".$s['page']."'><i class='".$s['icon']."'></i> ".$s['name']."</a></li>";
        }
        echo "</ul>
            </li>";
    }
}

echo '</ul>';

//top nav bar
echo '<ul class="nav navbar-master navbar-nav navbar-right">';
foreach ($menu as $r => $v){
    if ($v['menu'] == 'top-main' AND $v['menu'] != 'top-main-sub'){
        echo "
        <li class='navitem-".$v['class_name']."'><a href='../".$v['page']."'><i class='".$v['icon']."'></i> ".$v['name']."</a></li>
        ";
    }
    if ($v['menu'] == 'top-main-sub' AND $v['parent_id'] == 0){
        echo "
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
            </li>";
    }
}

echo '</ul></div>';