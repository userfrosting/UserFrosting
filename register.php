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
require_once("models/config.php");

setReferralPage(getAbsoluteDocumentPath(__FILE__));

if (!userIdExists('1')){
	addAlert("danger", lang("MASTER_ACCOUNT_NOT_EXISTS"));
	header("Location: install/wizard_root_user.php");
	exit();
}

// If registration is disabled, send them back to the home page with an error message
if (!$can_register){
	addAlert("danger", lang("ACCOUNT_REGISTRATION_DISABLED"));
	header("Location: login.php");
	exit();
}

//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) {
	addAlert("danger", "I'm sorry, you cannot register for an account while logged in.  Please log out first.");
	apiReturnError(false, SITE_ROOT);
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

    <title>UserFrosting - Register</title>

	<link rel="icon" type="image/x-icon" href="css/favicon.ico" />
	
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/jumbotron-narrow.css" rel="stylesheet">
	
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/bootstrap-switch.min.css" type="text/css" />
	 
    <!-- JavaScript -->
    <script src="js/jquery-1.10.2.min.js"></script>
	<script src="js/bootstrap.js"></script>
	<script src="js/userfrosting.js"></script>
	<script src="js/date.min.js"></script>
    <script src="js/handlebars-v1.2.0.js"></script> 

  </head>

  <body>
    <div class="container">
      <div class="header">
        <ul class="nav nav-pills navbar pull-right">
        </ul>
        <h3 class="text-muted">UserFrosting</h3>
      </div>
      <div class="jumbotron">
        <h1>Let's get started!</h1>
        <p class="lead">Registration is fast and simple.</p>
		<form name='newUser' id='newUser' class='form-horizontal' role='form' action='api/create_user.php' method='post'>
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
		  <div class="form-group">
			<label class="col-sm-4 control-label">Confirm Security Code</label>
			<div class="col-sm-4">
				<div class="input-group">
					<span class='input-group-addon'><i class='fa fa-eye'></i></span>
					<input type="text" class="form-control" name='captcha' data-validate='{"minLength": 1, "maxLength": 50, "label": "Confirm Security Code" }'>
				</div>
			</div>
			<div class="col-sm-4">
			  <img src='models/captcha.php' id="captcha">
			</div>
		  </div>
		  <br>
		  <div class="form-group">
			<div class="col-sm-12">
			  <button type="submit" class="btn btn-success submit" value='Register'>Register</button>
			</div>
		  </div>
		</form>
	  </div>	
      <div class="footer">
        <p>&copy; <a href='http://www.userfrosting.com'>UserFrosting</a>, 2014</p>
      </div>

    </div> <!-- /container -->

  </body>
</html>

<script>
	$(document).ready(function() {
		// Load navigation bar
		$(".navbar").load("header-loggedout.php", function() {
            $(".navbar .navitem-register").addClass('active');
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
                var url = APIPATH + 'create_user.php';
                $.ajax({  
                  type: "POST",  
                  url: url,  
                  data: {
					user_name: 		form.find('input[name="user_name"]' ).val(),
					display_name: 	form.find('input[name="display_name"]' ).val(),
					email: 			form.find('input[name="email"]' ).val(),
					password: 		form.find('input[name="password"]' ).val(),
					passwordc: 		form.find('input[name="passwordc"]' ).val(),
					captcha: 		form.find('input[name="captcha"]' ).val(),
                    ajaxMode:		"true"
                  }		  
                }).done(function(result) {
                  var resultJSON = processJSONResult(result);
                  if (resultJSON['errors'] && resultJSON['errors'] > 0){
                        console.log("error");
						// Reload captcha
						var img_src = 'models/captcha.php?' + new Date().getTime();
						$('#captcha').attr('src', img_src);
						form.find('input[name="captcha"]' ).val("");
                        alertWidget('display-alerts');
                        return;
                  } else {
                    window.location.replace('login.php');
                  }
                });   
            }
		});
	});
</script>
