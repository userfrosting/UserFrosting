<?php
/*
Create Character Version: 0.1
By Lilfade (Bryson Shepard)
Copyright (c) 2014

Based on the UserFrosting User Script v0.1.
Copyright (c) 2014

Licensed Under the MIT License : http://www.opensource.org/licenses/mit-license.php
Removing this copyright notice is a violation of the license.
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){
  // Forward to 404 page
  addAlert("danger", "Whoops, looks like you don't have permission to view that page.");
  header("Location: 404.php");
  exit();
}

// Look up specified user
$selected_character_id = $_GET['id'];

if (!is_numeric($selected_character_id) || !characterIdExists($selected_character_id)){
	addAlert("danger", "I'm sorry, the character id you specified is invalid!");
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

    <title>FadedGaming - Character Details</title>

	<?php require_once("includes.php");  ?>

    <!-- Page Specific Plugins -->
	<link rel="stylesheet" href="css/bootstrap-switch.min.css" type="text/css" />

	<script src="js/date.min.js"></script>
    <script src="js/handlebars-v1.2.0.js"></script> 
    <script src="js/bootstrap-switch.min.js"></script>
	<script src="js/widget-characters.js"></script>
  </head>
<body>
  
<?php
echo "<script>selected_character_id = $selected_character_id;</script>";
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
			<div id='widget-character-info' class="col-lg-6">          

			</div>
		</div>
  </div><!-- /#page-wrapper -->

</div><!-- /#wrapper -->
    
    <script>
		$(document).ready(function() {
			// Get id of the logged in user
			var user = loadCurrentUser();

			// Load the header
			$('.navbar').load('header.php', function() {
				$('#user_logged_in_name').html('<i class="fa fa-user"></i> ' + user['user_name'] + ' <b class="caret"></b>');          
			});
			
			characterDisplay('widget-character-info', selected_character_id);
			
			alertWidget('display-alerts');

    });

    </script>
  </body>
</html>

