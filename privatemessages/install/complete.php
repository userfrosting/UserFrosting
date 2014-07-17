<?php
/**
 * Installer for the private message system
 *
 * Tested with PHP version 5
 *
 * @author     Bryson Shepard <lilfade@fadedgaming.co>
 * @author     Project Manager: Alex Weissman
 * @copyright  2014 UserFrosting
 * @version    0.1
 * @link       http://www.userfrosting.com/
 * @link       http://www.github.com/lilfade/UF-PMSystem/
 */

require_once('config.php');
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Private Messenger Installer v0.1</title>

	<link rel="icon" type="image/x-icon" href="../../css/favicon.ico" />
	
    <!-- Bootstrap core CSS -->
    <link href="../../css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../../css/jumbotron-narrow.css" rel="stylesheet">
	
	<link rel="stylesheet" href="../../css/font-awesome.min.css">
	 
    <!-- JavaScript -->
    <script src="../../js/jquery-1.10.2.min.js"></script>
	<script src="../../js/bootstrap.js"></script>
	<script src="../../js/userfrosting.js"></script>

  </head>

  <body>
    <div class="container">
      <div class="header">
        <ul class="nav nav-pills navbar pull-right">
        </ul>
        <h3 class="text-muted">PM System v0.1</h3>
      </div>
      <div class="jumbotron">
        <h1>Installation Complete!</h1>
	  
		<div class="row">
			<div id='display-alerts' class="col-lg-12">

			</div>
            <p><a href="../../index.php">Back to homepage</a></p>
		</div>
	  </div>	
      <div class="footer">
        <p>
            <a href="http://github.com/lilfade/UF-PMSystem/">PM System Installer</a>
            <span class="pull-right">&copy;2014 <a href="http://github.com/lilfade/">@lilfade</a></span>
        </p>
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