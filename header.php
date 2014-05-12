<?php
/*

UserFrosting Version: 0.1
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

include('models/db-settings.php');
include('models/config.php');

if (!securePage($_SERVER['PHP_SELF'])){
    // Generate AJAX error
    addAlert("danger", "Whoops, looks like you don't have permission to access this component.");
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}

?>

<!-- Brand and toggle get grouped for better mobile display -->

<div class="navbar-header">
  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
	<span class="sr-only">Toggle navigation</span>
	<span class="icon-bar"></span>
	<span class="icon-bar"></span>
	<span class="icon-bar"></span>
  </button>
  <a class="navbar-brand" href="account.php">
  <?php
	echo $websiteName;
  ?>
  </a>
  <?php
  if ($loggedInUser->user_id == $master_account){
	  echo  '<span class="navbar-center navbar-brand">YOU ARE CURRENTLY LOGGED IN AS ROOT USER</span>';
  }
  ?>
</div>

<div class="collapse navbar-collapse navbar-ex1-collapse">
<!-- Collect the nav links, forms, and other content for toggling -->

  <ul class="nav navbar-nav side-nav">
	  

	  <li class="navitem-dashboard"><a href="dashboard.php"><i class="fa fa-dashboard"></i> User Dashboard</a></li>
	  
	  <li class='dropdown'>
	  <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='fa fa-users'></i> Guild Members <b class='caret'></b></a>
		  <ul class='dropdown-menu'>
	  <li class="navitem-character"><a href="character.php"><i class="fa fa-user"></i> Characters</a></li>
	  <li class="navitem-calender"><i class="fa fa-calendar"></i> Calendar</li>
	  <li class="navitem-roster"><a href="roster.php"><i class="fa fa-list"></i> Roster</a></li>
	  </ul>
	  </li>
	  
	  <li class='dropdown'>
	  <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='fa fa-users'></i> Raid Members <b class='caret'></b></a>
	  <ul class='dropdown-menu'>
	  <li class="navitem-character"><i class="fa fa-user"></i> Raid Characters</li>
	  <li class="navitem-calender"><i class="fa fa-calendar"></i> Attendance Tracker</li>
	  <li class="navitem-roster"><i class="fa fa-list"></i> Loot Wish list</li>
	  </ul>
	  </li>
	  
	  <li class='navitem-settings'><a href="account_settings.php"><i class="fa fa-gear"></i> Account Settings</a></li>
  <hr />
    <?php
	  //Links for permission level 2 (default admin)
	  if ($loggedInUser->checkPermission(array(2))){
	  echo "
	  <li class='dropdown'>
	  <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='fa fa-wrench'></i> Administrator Settings <b class='caret'></b></a>
		  <ul class='dropdown-menu'>
			  <li class='navitem-dashboard-admin'><a href='dashboard_admin.php'><i class='fa fa-dashboard'></i> Admin Dashboard</a></li>
        	  <li class='navitem-users'><a href='users.php'><i class='fa fa-users'></i> Users</a></li>
			  <li class='navitem-site-settings'><a href='site_settings.php'><i class='fa fa-globe'></i> Site Configuration</a></li>
			  <li class='navitem-site-pages'><a href='site_pages.php'><i class='fa fa-files-o'></i> Site Pages</a></li>
		  </ul>
	  </li>";
	}
	echo '<hr />';
	//Links for permission level 3 (default officer)
	if ($loggedInUser->checkPermission(array(3))){
		echo "
		<li class='dropdown'>
		<a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='fa fa-wrench'></i> Officer Settings <b class='caret'></b></a>
		<ul class='dropdown-menu'>
			<li class='navitem-place-holder-officer'><a href='#'><i class='fa fa-files-o'></i> Placeholder</a></li>
		</ul>
	</li>";
	}
	echo '<hr />';
	  //Links for permission level 4 (default guild master)
	  if ($loggedInUser->checkPermission(array(4))){
	  echo "
	  <li class='dropdown'>
	  <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='fa fa-wrench'></i> Guild Master Settings <b class='caret'></b></a>
		  <ul class='dropdown-menu'>
			  <li class='navitem-place-holder-gm'><a href='#'><i class='fa fa-files-o'></i> Placeholder</a></li>
		  </ul>
	  </li>";
	}
	?>
	
	
  </ul>
  <ul class="nav navbar-master navbar-nav navbar-right">
	<li class="dropdown user-dropdown">
	  <a href="#" class="dropdown-toggle" data-toggle="dropdown" id="user_logged_in_name"></a>
	  <ul class="dropdown-menu">
		<li><a href="account_settings.php"><i class="fa fa-gear"></i> Account Settings</a></li>
		<li class="divider"></li>
		<li><a href="logout.php"><i class="fa fa-power-off"></i> Log Out</a></li>
	  </ul>
	</li>
  </ul>
</div><!-- /.navbar-collapse -->
