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

// This is the installer config file in the install directory.
require_once("config.php");

// To register the root account, two conditions apply:
// 1. the root config token (root_account_config_token) must exist
// 2. the uc_users table must not have a user with id=1

//if (userIdExists('1')){
//	addAlert("danger", lang("MASTER_ACCOUNT_EXISTS"));
//	header('Location: index.php');
//	exit();
//}

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
    <link rel="shortcut icon" href="../css/favicon.ico">

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
        <p class="lead">Please set up the master (root) account for UserFrosting.<br>
		<small>The configuration token can be found in the <code>uc_configuration</code> table of your database, as the value for <code>root_account_config_token</code>.</small></p>
		<form name='newUser' id='newUser' class='form-horizontal' role='form' action='install_root_user.php' method='post'>
		  <div class="row">
				<div id='display-alerts' class="col-lg-12">
		  
				</div>
		  </div>		
		  <div class="row form-group">
			<label class="col-sm-4 control-label">Username</label>
			<div class="col-sm-8">
			    <div class="input-group">
                    <span class='input-group-addon'><i class='fa fa-edit'></i></span>
					<input type="text" class="form-control" placeholder="Username" name = 'user_name' value='' data-validate='{"minLength": 1, "maxLength": 25, "label": "Username" }'>
				</div>
			</div>
		  </div>
		  <div class="row form-group">
			<label class="col-sm-4 control-label">Display Name</label>
			<div class="col-sm-8">
				<div class="input-group">
					<span class='input-group-addon'><i class='fa fa-edit'></i></span>
					<input type="text" class="form-control" placeholder="Display Name" name='display_name' data-validate='{"minLength": 1, "maxLength": 50, "label": "Display Name" }'>
				</div>
			</div>
		  </div>
		  <div class="row form-group">
			<label class="col-sm-4 control-label">Email</label>
			<div class="col-sm-8">
				<div class="input-group">
					<span class='input-group-addon'><i class='fa fa-envelope'></i></span>
					<input type="email" class="form-control" placeholder="Email" name='email' data-validate='{"email": true, "minLength": 1, "maxLength": 150, "label": "Email" }'>
				</div>
			</div>
		  </div>		  
		  <div class="row form-group">
			<label class="col-sm-4 control-label">Password</label>
			<div class="col-sm-8">
				<div class="input-group">
					<span class='input-group-addon'><i class='fa fa-lock'></i></span>
					<input type="password" class="form-control" placeholder="Password" name='password' data-validate='{"minLength": 8, "maxLength": 50, "passwordMatch": "passwordc", "label": "Password" }'>
				</div>
			</div>
		  </div>
		  <div class="row form-group">
			<label class="col-sm-4 control-label">Confirm Password</label>
			<div class="col-sm-8">
			  	<div class="input-group">
					<span class='input-group-addon'><i class='fa fa-lock'></i></span>
					<input type="password" class="form-control" placeholder="Confirm Password" name='passwordc' data-validate='{"minLength": 8, "maxLength": 50, "label": "Confirm Password" }'>
				</div>
			</div>
		  </div>
		  <div class="row form-group">
			<label class="col-sm-4 control-label">Configuration Token</label>
			<div class="col-sm-8">
			  	<div class="input-group">
					<span class='input-group-addon'><i class='fa fa-lock'></i></span>
					<input type="text" class="form-control" placeholder="Configuration Token" name='csrf_token' data-validate='{"minLength": 1, "maxLength": 100, "label": "Configuration Token" }'>
				</div>
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
        <p>&copy; <a href="http://www.userfrosting.com">UserFrosting</a> Installer, 2014</p>
      </div>

    </div> <!-- /container -->
  </body>
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
		
		// Process submission
        $("form[name='newUser']").submit(function(e){
			e.preventDefault();
            var form = $(this);
            var errorMessages = validateFormFields('newUser');
			if (errorMessages.length > 0) {
				$('#display-alerts').html("");
				$.each(errorMessages, function (idx, msg) {
					$('#display-alerts').append("<div class='alert alert-danger'>" + msg + "</div>");
				});	
			} else {
                var url = 'install_root_user.php';
                $.ajax({  
                  type: "POST",  
                  url: url,  
                  data: {
					user_name: 		form.find('input[name="user_name"]' ).val(),
					display_name: 	form.find('input[name="display_name"]' ).val(),
					email: 			form.find('input[name="email"]' ).val(),
					password: 		form.find('input[name="password"]' ).val(),
					passwordc: 		form.find('input[name="passwordc"]' ).val(),
					csrf_token: 	form.find('input[name="csrf_token"]' ).val(),
                    ajaxMode:		"true"
                  }		  
                }).done(function(result) {
                  var resultJSON = processJSONResult(result);
                  if (resultJSON['errors'] && resultJSON['errors'] > 0){
                        console.log("error");
                        alertWidget('display-alerts');
                        return;
                  } else {
                    window.location.replace('complete.php');
                  }
                });   
            }
		});
		
      });
  </script>
</html>