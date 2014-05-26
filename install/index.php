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

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Welcome to UserFrosting!</title>

	<link rel="icon" type="image/x-icon" href="../css/favicon.ico" />
	
    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../css/jumbotron-narrow.css" rel="stylesheet">
	
	<link rel="stylesheet" href="../css/font-awesome.min.css">
	 
    <!-- JavaScript -->
    <script src="../js/jquery-1.10.2.min.js"></script>
	<script src="../js/bootstrap.js"></script>
	<script src="../js/userfrosting.js"></script>

  </head>

  <body>
    <div class="container">
      <div class="header">
        <ul class="nav nav-pills navbar pull-right">
        </ul>
        <h3 class="text-muted">UserFrosting</h3>
      </div>
      <div class="jumbotron">
        <h1>Welcome to UserFrosting!</h1>
        <p class="lead">A secure, modern user management system based on UserCake, jQuery, and Bootstrap.</p>
	  
		<div class="row">
			<div id='display-alerts' class="col-lg-12">

			</div>
		</div>
	      </div>	
		<div class="row">
			<div class="col-sm-12">
			<h2>To install, follow these instructions:</h2>
			<ul class="list-group">
				<li class="list-group-item">1. Create a database on your server / web hosting package. UserFrosting supports MySQLi and requires MySQL server version 4.1.3 or newer, as well as PHP 5.4 or later with PDO database connections enabled.</li>
				<li class="list-group-item">2. Open up models/db-settings.php and fill out the connection details for your database.</li>
				<li class="list-group-item">3. Click <a href="install.php">here</a> to run the UserFrosting installer.  UserFrosting will attempt to build the database for you, and generate a registration code to create the master (root) account.</li>
				<li class="list-group-item">4. After the installer successfully completes, delete the install folder.</li>
			</ul>
			</div>
        </div>	
      <div class="footer">
        <p>&copy; Your Website, 2014</p>
      </div>

    </div> <!-- /container -->

	<script>
        $(document).ready(function() {
		  var widget_id = 'display-alerts';
		  var url = 'install_alerts.php';
		  $.getJSON( url, {})
		  .done(function( data ) {	
			  var alertHTML = "";
			  jQuery.each(data, function(alert_idx, alert_message) {
				  if (alert_message['type'] == "success"){
					  alertHTML += "<div class='alert alert-success'>" + alert_message['message'] + "</div>";
				  } else if (alert_message['type'] == "warning"){
					  alertHTML += "<div class='alert alert-warning'>" + alert_message['message'] + "</div>";
				  } else 	if (alert_message['type'] == "info"){
					  alertHTML += "<div class='alert alert-info'>" + alert_message['message'] + "</div>";
				  } else if (alert_message['type'] == "danger"){
					  alertHTML += "<div class='alert alert-danger'>" + alert_message['message'] + "</div>";
				  }
			  });	
			  $('#' + widget_id).html(alertHTML);
			  return false;
		  });
		});
	</script>
  </body>
</html>