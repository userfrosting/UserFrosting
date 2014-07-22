<?php
// This is the installer config file in the install directory.
require_once("config.php");

// Check system requirements
// 1. Check PHP version

// PHP_VERSION_ID is available as of PHP 5.2.7, if our version is lower than that, then emulate it
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

if (PHP_VERSION_ID < 50307){
    addAlert("danger", "I'm sorry, due to a security flaw in older versions of PHP, UserFrosting requires PHP 5.3.7 or later.  For more information, please see <a href='http://www.userfrosting.com/security.html'>http://www.userfrosting.com/security.html</a>.");
    header("Location: ./");
    exit();
} else if (PHP_VERSION_ID >= 50307 && PHP_VERSION_ID < 50500){
    addAlert("warning", "You currently have version " . PHP_VERSION . " of PHP installed.  We recommend version 5.5 or later.  UserFrosting can still be installed, but we highly recommend you upgrade soon.");    
}

// 2. Check that PDO is installed and enabled
if (!class_exists('PDO')){
    addAlert("danger", "I'm sorry, you must have PDO installed and enabled in order for UserFrosting to access the database.  If you don't know what PDO is, please see <a href='http://php.net/manual/en/book.pdo.php'>http://php.net/manual/en/book.pdo.php</a>.  You must also have MySQL version 4.1 or higher installed, since UserFrosting relies on native prepared statements.");
    header("Location: ./");
    exit();    
}

// 3. Try to connect to the database.  If failed, return to index.php
try{
    $db = pdoConnect();
} catch (PDOException $e) {
    addAlert("danger", "Could not connect to database.  Please check your database credentials in `models/db-settings.php`.");
    error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
    header("Location: ./");
    exit();
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

    <!-- Page Specific Plugins -->
    <link rel="stylesheet" href="../css/bootstrap-switch.min.css" type="text/css" />

    <script src="../js/bootstrap-switch.min.js"></script>

</head>

<body>
<div class="container">
    <div class="header">
        <ul class="nav nav-pills navbar pull-right">
        </ul>
        <h3 class="text-muted">UserFrosting</h3>
    </div>
    <!-- Start Form Input -->
    <form name='newInstall' class='form-horizontal' role='form' action='install_db.php' method='post'>
        <div id='display-alerts'>

        </div>
        <div class="alert alert-success">
        Great news, we're able to connect to your database!  Let's set up the rest of the site.
        </div>
        <div class="alert alert-info">
            <div class="h4">Installation consists of three easy steps:</div>
            <ol>
                <li>Enter some basic configuration information for your site.  You can change this at any time after installation from the "site settings" page.</li>
                <li>Create the root user account.  A configuration token will be placed in your database, which you will be asked to verify.</li>
                <li>Delete the <code>install</code> folder.  You will be able to login to your new site by navigating to the root directory.</li>
            </ol>
        </div><!-- Info popup -->            
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h1 class="panel-title">Site Settings</h1>
            </div>
            <div id='newInstall' class="panel-body">
                <div class="form-group">
                    <label class="col-sm-4 control-label">Site Root URL <br /> <small>(Ensure this is correct <br />with a single trailing /)</small></label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class='input-group-addon'>http(s)://</span>
                            <input type="text" class="form-control" name='site_url' value='<?php global $url; echo $url; ?>' data-validate='{"minLength": 1, "maxLength": 150, "label": "Site Root URL"}'>
                        </div>
                        <small>This is the root URL for UserFrosting.  We attempted to detect it automatically; please check to make sure that it is correct.  All other pages are located relative to this URL.</small>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Website Name</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class='input-group-addon'><i class='fa fa-edit'></i></span>
                            <input type="text" class="form-control" name='site_name' value='UserFrosting' data-validate='{"minLength": 1, "maxLength": 150, "label": "Website Name"}'>         
                        </div>
                        <small>This is the name of the website, which will be shown in the upper left of the menu bar.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label">Default New User Title</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class='input-group-addon'><i class='fa fa-edit'></i></span>
                            <input type="text" class="form-control" name='user_title' value='New Member' data-validate='{"minLength": 1, "maxLength": 150, "label": "Default New User Title"}'>
                        </div>
                        <small>This is the default title given to newly registered users.</small>
                    </div>
                </div>
                <div class="form-group">
                
                    <label class="col-sm-4 control-label">Site Email Address</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class='input-group-addon'><i class='fa fa-envelope'></i></span>
                            <input type="text" class="form-control" name='site_email' placeholder='admin email address' value="" data-validate='{"minLength": 1, "maxLength": 150, "email": true, "label": "Site Email Address"}'>
                        </div>
                        <small>This is the 'from' address that users will see when they get site emails, such as activation or lost password emails.</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="col-sm-4 control-label">Enable User Registration</label>
                    <div class="col-sm-8">
                        <input type="checkbox" name="can_register" checked />
                        <br><small>Specify whether users can create new accounts by themselves.  Enable if you have a service that users can sign up for, disable if you only want accounts to be created by you or an admin.</small>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Require Email Activation</label>
                    <div class="col-sm-8">
                        <input type="checkbox" name="email_activation" checked />
                        <br><small>Specify whether email activation is required for newly registered accounts.</small>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Enable Email Login</label>
                    <div class="col-sm-8">
                        <input type="checkbox" name="select_email" />
                        <br><small>Specify whether users can login via email address or username instead of just username.</small>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="form-group">
            <div class="col-sm-4 pull-right">
                <button type="submit" class="form-control btn btn-primary submit" value='doInstall'>Continue Install...</button>
            </div>
        </div>
    </form>
    <!-- End Form Input -->



    <div class="footer">
        <p>&copy; <a href="http://www.userfrosting.com">UserFrosting</a> Installer, 2014</p>
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
                $("[name='can_register']").bootstrapSwitch();
                $("[name='email_activation']").bootstrapSwitch();
                $("[name='select_email']").bootstrapSwitch();
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
                var url = 'install_db.php';
                $.ajax({  
                  type: "POST",  
                  url: url,  
                  data: {
                    site_name:					form.find('input[name="site_name"]').val(),
                    site_url:					form.find('input[name="site_url"]').val(),
                    site_email:					form.find('input[name="site_email"]').val(),
                    user_title:					form.find('input[name="user_title"]').val(),
                    can_register: 				form.find('input[name="can_register"]:checked').val(),
                    select_email: 				form.find('input[name="select_email"]:checked').val(),
                    email_activation: 			form.find('input[name="email_activation"]:checked').val(),
                    ajaxMode:					"true"
                  }		  
                }).done(function(result) {
                  var resultJSON = processJSONResult(result);
                  if (resultJSON['errors'] && resultJSON['errors'] > 0){
                        console.log("error");
                        alertWidget('display-alerts');
                        return;
                  } else {
                    window.location.replace('wizard_root_user.php');
                  }
                });   
            }
		});
            
    });
</script>
</body>
</html>