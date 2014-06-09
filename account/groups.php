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

    <title>UserFrosting Admin - Groups</title>
		
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
		<h1>Group Settings</h1>
		<div class='row'>
		<div class='col-lg-6'>
		<div class='panel panel-primary'>
		  <div class='panel-heading'>
		  <h3 class='panel-title'>Permission Groups</h3>
		  </div>
		  <div class='panel-body'>
			<ul id='permission-groups' class="list-group">
			</ul>
		  <button type='button' class='btn btn-primary addPermission'><i class='fa fa-plus-square'></i>  Add Permission Group</button>
		  </div>
		  </div>
		</div>
            <div id='info' class='col-lg-6'>
                <p>{information for groups here}</p>
            </div>
		</div>
		</div>
        </div>
	<script>
        $(document).ready(function() {
          // Get id of the logged in user to determine how to render this page.
          var user = loadCurrentUser();
          var user_id = user['user_id'];

          // Load the header
          $('.navbar').load('header.php', function() {
			$('.navitem-groups').addClass('active');
          });
		  // Load permissions
		  loadPermissions('permission-groups');
		  
		  // Bind permission delete and add buttons
		  $('.addPermission').on('click', function(){
			if ($('#permission-groups').has("input").length == 0) {
			  $("<li class='list-group-item'><div class='row'><div class='col-lg-6'><input autofocus class='form-control' name='group_name'/></div></div></li>")
			  .appendTo('#permission-groups');
			}
			$('#permission-groups input').focus();
			
			// Bind entering a value
			$('#permission-groups input').blur(function(){
			  // Submit to processing form
			  addNewPermission($('#permission-groups input').val());
			});
		  });
		  
		  //alertWidget('display-alerts');
		  
		});
	</script>
  </body>
</html>

