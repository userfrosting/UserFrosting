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

// Recommended admin-only access
if (!securePage(__FILE__)){
    if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
        echo json_encode(array("errors" => 1, "successes" => 0));
    } else {
        header("Location: " . getReferralPage());
    }
    exit();
}

$validator = new Validator();

// Look up specified user
$selected_user_id = $validator->requiredGetVar('id');

if (!is_numeric($selected_user_id) || !userIdExists($selected_user_id)){
	addAlert("danger", "I'm sorry, the user id you specified is invalid!");
	header("Location: " . getReferralPage());
	exit();
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>UserFrosting Admin - User Details</title>

	<?php require_once("includes.php");  ?>

    <!-- Page Specific Plugins -->
	<link rel="stylesheet" href="../css/bootstrap-switch.min.css" type="text/css" />

	<script src="../js/date.min.js"></script>
    <script src="../js/handlebars-v1.2.0.js"></script> 
    <script src="../js/bootstrap-switch.min.js"></script>
	<script src="../js/widget-users.js"></script>
  </head>
<body>
  
<?php
echo "<script>selected_user_id = $selected_user_id;</script>";
?>
  
<!-- Begin page contents here -->
<div id="wrapper">

<!-- Sidebar -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
</nav>

	<div id="page-wrapper">
		<div class="row">
		  <div id='display-alerts' class="col-lg-12">
  
		  </div>
		</div>
		<div class="row">
			<div id='widget-user-info' class="col-lg-6">          

			</div>
		</div>
  </div><!-- /#page-wrapper -->

</div><!-- /#wrapper -->
    
    <script>
		$(document).ready(function() {
			// Load the header
			$('.navbar').load('header.php', function() {
			    $('.navitem-users').addClass('active');
			});
			
			userDisplay('widget-user-info', selected_user_id);
			
			alertWidget('display-alerts');

    });

    </script>
  </body>
</html>

