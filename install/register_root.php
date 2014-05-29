<?php
/*

UserFrosting Version: 0.2
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
require_once('config.php');

// TODO: Client-side validation and AJAX submission

// To register the root account, two conditions apply:
// 1. the root config token (root_account_config_token) must exist
// 2. the uc_users table must not have a user with id=1

if (userIdExists('1')){
	addAlert("danger", lang("MASTER_ACCOUNT_EXISTS"));
	header('Location: index.php');
	exit();
}

if (!($root_account_config_token = fetchConfigParameter('root_account_config_token'))){
	addAlert("danger", lang("INSTALLER_INCOMPLETE"));
	header('Location: index.php');
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
    <link rel="shortcut icon" href="css/favicon.ico">

    <title>UserFrosting - Register Master Account</title>

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
        <h1>Master Account Setup</h1>
        <p class="lead">Please set up the master (root) account for UserFrosting.</p>
		<small>The configuration token can be found in the 'uc_configuration' table of your database, as the value for 'root_account_config_token'.</small>
		<form name='newUser' class='form-horizontal' role='form' action='create_root_user.php' method='post'>
		  <div class="row">
				<div id='display-alerts' class="col-lg-12">
		  
				</div>
		  </div>		
		  <div class="form-group">
			<label class="col-sm-4 control-label">User Name</label>
			<div class="col-sm-8">
			  <input type="text" class="form-control" placeholder="User Name" name = 'username' value=''>
			</div>
		  </div>
		  <div class="row form-group">
			<label class="col-sm-4 control-label">Display Name</label>
			<div class="col-sm-8">
			  <input type="text" class="form-control" placeholder="Display Name" name='displayname'>
			</div>
		  </div>
		  <div class="form-group">
			<label class="col-sm-4 control-label">Email</label>
			<div class="col-sm-8">
			  <input type="email" class="form-control" placeholder="Email" name='email'>
			</div>
		  </div>		  
		  <div class="form-group">
			<label class="col-sm-4 control-label">Password</label>
			<div class="col-sm-8">
			  <input type="password" class="form-control" placeholder="Password" name='password'>
			</div>
		  </div>
		  <div class="form-group">
			<label class="col-sm-4 control-label">Confirm Password</label>
			<div class="col-sm-8">
			  <input type="password" class="form-control" placeholder="Confirm Password" name='passwordc'>
			</div>
		  </div>
		  <div class="form-group">
			<label class="col-sm-4 control-label">Configuration Token</label>
			<div class="col-sm-8">
			  <input type="text" class="form-control" name='token'>
			</div>
		  </div>
		  <br>
		  <div class="form-group">
			<div class="col-sm-12">
			  <button type="submit" class="btn btn-danger submit" value='Register'>Register Master Account</button>
			</div>
		  </div>
		</form>
	  </div>	
      <div class="footer">
        <p>&copy; Your Website, 2014</p>
      </div>

    </div> <!-- /container -->

  </body>
</html>

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

