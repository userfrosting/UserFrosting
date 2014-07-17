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

// Public page

setReferralPage(getAbsoluteDocumentPath(__FILE__));

//Forward the user to their default page if he/she is already logged in
if(isUserLoggedIn()) {
	addAlert("warning", "You're already logged in!");
	header("Location: account");
	exit();
}
global $email_login;

if ($email_login == 1) {
    $user_email_placeholder = 'Username or Email';
}else{
    $user_email_placeholder = 'Username';
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

    <title>Welcome to UserFrosting!</title>

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
        <h1>Welcome to UserFrosting!</h1>
        <p class="lead">A secure, modern user management system based on UserCake, jQuery, and Bootstrap.</p>
		<small>Please sign in here:</small>
		<form class='form-horizontal' role='form' name='login' action='api/process_login.php' method='post'>
		  <div class="row">
			<div id='display-alerts' class="col-lg-12">
  
			</div>
		  </div>
		  <div class="form-group">
			<div class="col-md-offset-3 col-md-6">
			  <input type="text" class="form-control" id="inputUserName" placeholder="<?php echo $user_email_placeholder; ?>" name = 'username' value=''>
			</div>
		  </div>
		  <div class="form-group">
			<div class="col-md-offset-3 col-md-6">
			  <input type="password" class="form-control" id="inputPassword" placeholder="Password" name='password'>
			</div>
		  </div>
		  <div class="form-group">
			<div class="col-md-12">
			  <button type="submit" class="btn btn-success submit" value='Login'>Login</button>
			</div>
		  </div>
		  <div class="jumbotron-links">
		  </div>		  
		</form>
      </div>	
      <div class="footer">
        <p>&copy; <a href='http://www.userfrosting.com'>UserFrosting</a>, 2014</p>
      </div>

    </div> <!-- /container -->

	<script>
        $(document).ready(function() {          
		  // Load navigation bar
		  $(".navbar").load("header-loggedout.php", function() {
			  $(".navbar .navitem-login").addClass('active');
		  });
		  // Load jumbotron links
		  $(".jumbotron-links").load("jumbotron_links.php");
	  
		  alertWidget('display-alerts');
			  
		  $("form[name='login']").submit(function(e){
			var form = $(this);
			var url = 'api/process_login.php';
			$.ajax({  
			  type: "POST",  
			  url: url,  
			  data: {
				username:	form.find('input[name="username"]').val(),
				password:	form.find('input[name="password"]').val(),
				ajaxMode:	"true"
			  },		  
			  success: function(result) {
				var resultJSON = processJSONResult(result);
				if (resultJSON['errors'] && resultJSON['errors'] > 0){
				  alertWidget('display-alerts');
				} else {
				  window.location.replace("account");
				}
			  }
			});
			// Prevent form from submitting twice
			e.preventDefault();
		  });
		  
		});
	</script>
  </body>
</html>