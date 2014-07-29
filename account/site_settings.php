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

require_once("../models/config.php");

if (!securePage(__FILE__)){
  // Forward to index page
  addAlert("danger", "Whoops, looks like you don't have permission to view that page.");
  header("Location: index.php");
  exit();
}

setReferralPage(getAbsoluteDocumentPath(__FILE__));
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>UserFrosting Admin - Settings</title>
		
	<?php require_once("includes.php");  ?>
	
	<!-- Page Specific Plugins -->
	<link rel="stylesheet" href="../css/bootstrap-switch.min.css" type="text/css" />

	<script src="../js/bootstrap-switch.min.js"></script>
	<script src="../js/jquery.tablesorter.js"></script>
	<script src="../js/tables.js"></script>

  </head>

  <body>

    <div id="wrapper">

      <!-- Sidebar -->
      <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      </nav>

      <div id="page-wrapper">
	  	<div class="row">
          <div id='display-alerts' class="col-lg-12">

          </div>
        </div>
		<h1>Site Settings</h1>
		<div class='row'>
		<div id='regbox' class='col-lg-6'>
		<div class='panel panel-primary'>
		  <div class='panel-heading'>
			<h3 class='panel-title'>Configuration</h3>
		  </div>
		  <div class='panel-body'>
			<form class='form-horizontal' role='form' name='adminConfiguration' action='../api/update_site_settings.php' method='post'>
			<div class="form-group">
			  <label for="inputWebsiteName" class="col-sm-4 control-label">Site Name</label>
			  <div class="col-sm-8">
				<input type='text' id="inputWebsiteName" class="form-control" name='website_name'/>
			  </div>
			</div>
			<div class="form-group">
			  <label for="inputWebsiteURL" class="col-sm-4 control-label">Admin Root URL</label>
			  <div class="col-sm-8">
				<input type='text' id="inputWebsiteURL" class="form-control" name='website_url'/>
			  </div>
			</div>
			<div class="form-group">
			  <label for="inputEmail" class="col-sm-4 control-label">Account Management Email</label>
			  <div class="col-sm-8">
				<input type='text' id="inputEmail" class="form-control" name='email'/>
			  </div>
			</div>
			<div class="form-group">
			  <label for="userRegistration" class="col-sm-4 control-label">User Registration</label>
			  <div class="col-sm-8">
				<input type="checkbox" id ="userRegistration" name='can_register' value='1'/>
				<br><small>Specify whether users can create new accounts by themselves.</small>
			  </div>
			</div>
            <div class="form-group">
              <label for="emailLogin" class="col-sm-4 control-label">Email Login</label>
                <div class="col-sm-8">
                  <input type="checkbox" id ="emailLogin" name='email_login' value='0'/>
                    <br><small>Specify whether users can login via email address or username instead of just username.</small>
                </div>
            </div>
			<div class="form-group">
			  <label for="newUserTitle" class="col-sm-4 control-label">Default New User Title</label>
			  <div class="col-sm-8">
				<input type='text' id="newUserTitle" class="form-control" name='new_user_title'/>
			  </div>
			</div>
			<div class="form-group">
			  <label for="checkEmailActivation" class="col-sm-4 control-label">Email Activation</label>
			  <div class="col-sm-8">
				<input type="checkbox" id ="checkEmailActivation" name='activation' value='1'/>
				<br><small>Specify whether email activation is required for newly registered accounts.</small>
			  </div>
			</div>
			<div class="form-group">
			  <label for="inputThreshold" class="col-sm-4 control-label">Account Activation Threshold</label>
			  <div class="col-sm-8">
				<input type='text' id="inputThreshold" class="form-control" name='resend_activation_threshold'/>
			  </div>
			</div>
            <div class="form-group">
                <label for="inputTimeoutToken" class="col-sm-4 control-label">Password Reset Token Timeout value (in hours Max: 27 Hours)</label>
                <div class="col-sm-8">
                    <input type='text' id="inputTimeoutToken" class="form-control" name='token_timeout'/>
                </div>
            </div>
			<div class="form-group">
			  <label for="selectLanguage" class="col-sm-4 control-label">Site Language</label>
			  <div class="col-sm-8">
				<select id="selectLanguage" name='language'></select>
			  </div>
			</div>
			<div class="form-group">
			  <div class="col-sm-offset-4 col-sm-8">
				<button type="submit" class="btn btn-success submit" value='Update'>Update</button>
			  </div>
			</div>
			</form>
		  </div>
		</div>
		</div>
		
		<div class='col-lg-6'>
		<p>{Information for settings here}</p>
	  </div>
	<script>
        $(document).ready(function() {
          // Get id of the logged in user to determine how to render this page.
          var user = loadCurrentUser();
          var user_id = user['user_id'];

          // Load the header
          $('.navbar').load('header.php', function() {
			$('.navitem-site-settings').addClass('active');
          });
		  
		  $("form[name='adminConfiguration']").submit(function(e){
			var form = $(this);
			var url = '../api/update_site_settings.php';
			$.ajax({  
			  type: "POST",  
			  url: url,  
			  data: {
				website_name:					form.find('input[name="website_name"]').val(),
				website_url:					form.find('input[name="website_url"]').val(),
				email:							form.find('input[name="email"]').val(),
				resend_activation_threshold:	form.find('input[name="resend_activation_threshold"]').val(),
				new_user_title:					form.find('input[name="new_user_title"]').val(),
				can_register: 					form.find('input[name="can_register"]:checked').val(),
                email_login: 					form.find('input[name="email_login"]:checked').val(),
				activation: 					form.find('input[name="activation"]:checked').val(),
                token_timeout:                  form.find('input[name="token_timeout"]').val(),
				language:						form.find('select[name="language"] option:selected').val(),
				ajaxMode:						"true"
			  }		  
			}).done(function(result) {
			  var resultJSON = processJSONResult(result);
			  alertWidget('display-alerts');
			});
			return false;
		  });
		  
		  // Load and initialize fields
		  $('#regbox input[type="checkbox"]').bootstrapSwitch();
		  var url = "../api/load_site_settings.php";
		  $.getJSON( url, {})
		  .fail(function(result) {
			addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
			alertWidget('display-alerts');
		  })
		  .done(function( result ) {
			var data = processJSONResult(result);
			alertWidget('display-alerts');
			if (Object.keys(data).length > 0) { // Don't bother unless there are some records found
			  $('#regbox input[name="website_name"]').val(data['website_name']);
			  $('#regbox input[name="website_url"]').val(data['website_url']);
			  $('#regbox input[name="email"]').val(data['email']);
			  $('#regbox input[name="new_user_title"]').val(data['new_user_title']);
			  $('#regbox input[name="resend_activation_threshold"]').val(data['resend_activation_threshold']);
			  if (data['can_register'] == "1")  {
				$('#regbox input[name="can_register"]').bootstrapSwitch('setState', true);
			  } else {
				$('#regbox input[name="can_register"]').bootstrapSwitch('setState', false);
			  }
              if (data['email_login'] == "1")  {
                  $('#regbox input[name="email_login"]').bootstrapSwitch('setState', true);
              } else {
                  $('#regbox input[name="email_login"]').bootstrapSwitch('setState', false);
              }
			  if (data['activation'] == "1")  {
				$('#regbox input[name="activation"]').bootstrapSwitch('setState', true);
			  } else {
				$('#regbox input[name="activation"]').bootstrapSwitch('setState', false);
			  }
                $('#regbox input[name="token_timeout"]').val(data['token_timeout']/60/60);
			  // Load the language and template options
			  var language_options = data['language_options'];
			  if (Object.keys(language_options).length > 0) { // Don't bother unless there are some options found
				jQuery.each(language_options, function(idx, record) {
				  if (record == data['language']) {
					$('<option></option>').val(record).html(record).prop('selected', true).appendTo('#regbox select[name="language"]');
				  } else {
					$('<option></option>').val(record).html(record).prop('selected', false).appendTo('#regbox select[name="language"]');
				  }
				});
			  }
			}
		  });
		  //alertWidget('display-alerts');
		  
		});
	</script>
  </body>
</html>

