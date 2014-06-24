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
		<h1>Account Settings</h1>
		<div class="row">
		  <div class="col-lg-6">
		  <form class="form-horizontal" role="form" name="updateAccount" action="update_user.php" method="post">
		  <div class="form-group">
			<label class="col-sm-4 control-label">Email</label>
			<div class="col-sm-8">
			  <input type="email" class="form-control" placeholder="Email" name='email' value=''>
			</div>
		  </div>
		  <div class="form-group">
			<label class="col-sm-4 control-label">Current Password</label>
			<div class="col-sm-8">
			  <input type="password" class="form-control" placeholder="Current Password" name='passwordcheck'>
			</div>
		  </div>
		  <div class="form-group">
			<label class="col-sm-4 control-label">New Password</label>
			<div class="col-sm-8">
			  <input type="password" class="form-control" placeholder="New Password" name='password'>
			</div>
		  </div>
		  <div class="form-group">
			<label class="col-sm-4 control-label">Confirm New Password</label>
			<div class="col-sm-8">
			  <input type="password" class="form-control" placeholder="Confirm New Password" name='passwordc'>
			</div>
		  </div>
		  
		  <div class="form-group">
			<div class="col-sm-offset-4 col-sm-8">
			  <button type="submit" class="btn btn-success submit" value='Update'>Update</button>
			</div>
		  </div>
		  <input type="hidden" name="csrf_token" value="<?php echo $loggedInUser->csrf_token; ?>" />
		  <input type="hidden" name="user_id" value="0" />
		  </form>
		  </div>
		</div>
	  </div>
	</div>
	
	<script>
        $(document).ready(function() {
          // Get id of the logged in user to determine how to render this page.
          var user = loadCurrentUser();
          var user_id = user['user_id'];
          
		  alertWidget('display-alerts');
		  
          // Load the header
          $('.navbar').load('header.php', function() {
			$('.navitem-settings').addClass('active');
          });

		  // Set default form field values
		  $('form[name="updateAccount"] input[name="email"]').val(user['email']);

		  var request;
		  $("form[name='updateAccount']").submit(function(event){
			var url = APIPATH + 'update_user.php';
			// abort any pending request
			if (request) {
				request.abort();
			}
			var $form = $(this);
			var $inputs = $form.find("input");
			// post to the backend script in ajax mode
			var serializedData = $form.serialize() + '&ajaxMode=true';
			// Disable the inputs for the duration of the ajax request
			$inputs.prop("disabled", true);
		
			// fire off the request
			request = $.ajax({
				url: url,
				type: "post",
				data: serializedData
			})
			.done(function (result, textStatus, jqXHR){
				var resultJSON = processJSONResult(result);
				// Render alerts
				alertWidget('display-alerts');
				
				// Clear password input fields on success
				if (resultJSON['successes'] > 0) {
				  $form.find("input[name='password']").val("");
				  $form.find("input[name='passwordc']").val("");
				  $form.find("input[name='passwordcheck']").val("");
				}
			}).fail(function (jqXHR, textStatus, errorThrown){
				// log the error to the console
				console.error(
					"The following error occured: "+
					textStatus, errorThrown
				);
			}).always(function () {
				// reenable the inputs
				$inputs.prop("disabled", false);
			});
		
			// prevent default posting of form
			event.preventDefault();  
		  });

		});
	</script>
  </body>
</html>
