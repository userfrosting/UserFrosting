<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>0.2.2 Manual File changes</title>

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
        <h1>0.2.2 Upgrade</h1>
        <p class="lead">Manual file changes that need to be done before upgrade is complete, if you fail to do this some stuff may not function as intended!</p>
		<!--<div class='alert alert-warning'>Before you begin, please make sure you have UserFrosting installed or this upgrade script will not work.</div>-->
		<div id='display-alerts'>
		</div>
      </div>
        <p>If you haven't made any code changes to Userfrosting core files the below information can be skipped by uploading the changed files, if you have made changes these are the changes you will need to make in order for userfrosting to function as before.</p>
        <p>Changes files: /models/config.php, /models/db-settings.php, /models/db_functions.php</p>

        <pre>
        <strong>/models/config.php</strong>

        <strong><u>Find:</u></strong>

            function logAllErrors($errno, $errstr, $errfile, $errline, array $errcontext) {
            ini_set("log_errors", 1);
            ini_set("display_errors", 0);

            error_log("Error ($errno): $errstr in $errfile on line $errline");
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }

        <strong><u>Add after:</u></strong>

            // This will stop the installer / upgrader from running as it normally would and should always be set to false
            // Options TRUE | FALSE bool
            $dev_env = FALSE;

        <strong><u>Find:</u></strong>
            $token_timeout = $settings['token_timeout'];

        <strong><u>Add after:</u></strong>
            $version = $settings['version'];

        <strong><u>Find:</u></strong>
            // Include paths for pages to add to site page management
            $page_include_paths = array(
	            "account",
	            "forms"
                //"privatemessages",
                //"privatemessages/forms",
                // Define more include paths here
            );

        <strong><u>Replace with:</u></strong>
            // Include paths for pages to add to site page management
            $page_include_paths = fetchFileList();
        </pre>
        <br />
        <pre>
        <strong>/models/db-settings.php</strong>

        <strong><u>Find:</u></strong>
            //Direct to install directory, if it exists
            if(is_dir("install/"))
            {
	            header("Location: install/");
	            die();
            }

        <strong><u>Replace with:</u></strong>
            //Direct to install directory, if it exists and if $dev_env is not set to True in config.php
            if(is_dir("install/") && $dev_env != TRUE)
            {
	            header("Location: install/");
	            die();
            }

            if(is_dir("upgrade/") && $dev_env != TRUE)
            {
                header("Location: upgrade/");
                die();
            }
        </pre>

        <pre>
        <strong>/models/db_functions.php</strong>

        <strong><u>Find:</u></strong>
            function pageIdExists($page_id) {
                return valueExists('pages', 'id', $page_id);
            }

        <strong><u>Add after:</u></strong>
            //List of pages to be loaded, non hard-coded anymore
            function fetchFileList() {
                try {
                    global $db_table_prefix;

                    $results = array();

                    $db = pdoConnect();

                    $query = "SELECT
                        id,
                        path
                        FROM ".$db_table_prefix."filelist";

                    $stmt = $db->prepare($query);

                    if (!$stmt->execute()){
                        // Error
                        return false;
                    }

                    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $i = $r['id'];
                        $results[$i] = $r['path'];
                    }
                    $stmt = null;

                    return $results;
                } catch (PDOException $e) {
                    addAlert("danger", "Oops, looks like our database encountered an error.");
                    error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
                    return false;
                } catch (ErrorException $e) {
                    addAlert("danger", "Oops, looks like our server might have goofed.  If you're an admin, please check the PHP error logs.");
                return false;
                }
            }
        </pre>
        <a class="btn btn-primary" href='confirm_version.php'>Continue...</a>
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