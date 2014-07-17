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
        <h1>Welcome to the PM System Installer</h1>
		<div class='alert alert-warning'><small>
                This installer is fully automated and and requires no input, this will make the reuqired changes to enable the pm system on your site.
                <br />
                <em>To start click the button below.</em></small></div>
		<div id='display-alerts'>
		</div>
          <form name='newInstall' class='form-horizontal' role='form' action='install_db.php' method='post'>
            <div class="form-group">
    		  <button type="submit" class="btn btn-primary submit" value='doInstall'>Start Installation</button>
            </div>
          </form>
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
            // Process submission
            $("form[name='newInstall']").submit(function(e){
                e.preventDefault();
                var form = $(this);
                var errorMessages = validateFormFields('newInstall');
                if (errorMessages.length > 0) {
                    $('#display-alerts').html("");
                    $.each(errorMessages, function (idx, msg) {
                        $('#display-alerts').append("<div class='alert alert-danger'>" + msg + "</div>");
                    });
                } else {
                    var url = 'pm_system_install.php';
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {
                            ajaxMode:   "true"
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
  </body>
</html>