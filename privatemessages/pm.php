<?php
/**
 * Main page of the private message system
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

require_once("../models/config.php");
require_once("models/pm_functions.php");

if (!securePage(__FILE__)){
    // Forward to index page
    addAlert("danger", "Whoops, looks like you don't have permission to view that page.");
    header("Location: 404.php");
    exit();
}

if ($pmsystem_enabled != 1){
    // Forward to index page
    addAlert("danger", "Whoops, looks like the private message system is not enabled");
    header("Location: ".SITE_ROOT."account/index.php");
    exit();
}

setReferralPage(getAbsoluteDocumentPath(__FILE__));

$validate = new Validator();

// This is used to get the right page view
$action_var = $validate->optionalGetVar('action');
$msg_id = $validate->optionalGetVar('id');

// This is for use when deleting messages
$table_name = $validate->optionalGetVar('a_id'); // Field not table xD
$table_delete = $validate->optionalGetVar('a_d'); //receiver_id or sender_id depending on inbox or outbox
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>UserFrosting Admin - Private Messages</title>

    <?php require_once("../account/includes.php");  ?>

    <!-- Page Specific Plugins -->
    <link rel="stylesheet" href="../css/bootstrap-switch.min.css" type="text/css" />

    <script src="../js/date.min.js"></script>
    <script src="../js/handlebars-v1.2.0.js"></script>
    <script src="../js/bootstrap-switch.min.js"></script>
    <script src="../js/typeahead.js"></script>
    <script src="../js/jquery.tablesorter.js"></script>
    <script src="../js/tables.js"></script>
    <script src="js/widget-privatemessages.js"></script>
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
        <div class="row">
            <div id='widget-privatemessages' class="col-lg-12">

            </div>
        </div><!-- /.row -->

    </div><!-- /#page-wrapper -->

</div><!-- /#wrapper -->

<script>
    $(document).ready(function() {
        // Load the header
        $('.navbar').load('../account/header.php', function() {
            $('.navitem-pms').addClass('active');
        });

        alertWidget('display-alerts');

        var action = '<?php echo $action_var; ?>';
        var msg_id = '<?php echo $msg_id; ?>';

        var a_id = '<?php echo $table_name; ?>';
        var a_d = '<?php echo $table_delete; ?>';

        if(action == "read"){
            // Action = read we show the message to the user and pass which tables we need
            //console.log('read' + ' id=' + msg_id + ' a_id=' + a_id + ' a_d=' + a_d);
            messageDisplay('widget-privatemessages', msg_id, a_id, a_d);
        }else if(action == 'reply'){
            // action = reply - this may not need to be shown
            //console.log('reply' + ' id=' + msg_id);

        }else if(action == 'outbox'){
            pmsWidget('widget-privatemessages',{
                title_page: 'Outbox',
                action_id: 'sender_id',
                action_deleted: 'sender_deleted'
            });
        }else{
            // Action is unset show the list of messages eg. inbox
            pmsWidget('widget-privatemessages',{
                title_page: 'Inbox',
                action_id: 'receiver_id',
                action_deleted: 'receiver_deleted'
            });
        }
    });
</script>
</body>
</html>