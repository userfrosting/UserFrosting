<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Welcome to UserFrosting!</title>

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
        <h1>Welcome to UserFrosting!</h1>
        <p class="lead">Upgrade Script</p>
		<div class='alert alert-warning'>Before you begin, please make sure you have UserFrosting installed or this upgrade script will not work.</div>
		<div id='display-alerts'>
		</div>        
		<a class="btn btn-primary" href='get_versions.php'>Start Upgrade</a>
	  </div>
      <div class="footer">
        <p>&copy; UserFrosting Installer, 2014</p>
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