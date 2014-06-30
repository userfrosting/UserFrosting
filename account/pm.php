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
 */

require_once("../models/config.php");

if (!securePage(__FILE__)){
    // Forward to index page
    addAlert("danger", "Whoops, looks like you don't have permission to view that page.");
    header("Location: 404.php");
    exit();
}

if ($pmsystem_enabled != 1){
    // Forward to index page
    addAlert("danger", "Whoops, looks like the private message system is not enabled");
    header("Location: index.php");
    exit();
}

setReferralPage(getAbsoluteDocumentPath(__FILE__));

$validate = new Validator();
$action_var = $validate->optionalGetVar('action');
$msg_id = $validate->optionalGetVar('id');

//ChromePhp::log($action_var);
//ChromePhp::log($msg_id)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>UserFrosting Admin - Private Messages</title>

    <?php require_once("includes.php");  ?>

    <!-- Page Specific Plugins -->
    <link rel="stylesheet" href="../css/bootstrap-switch.min.css" type="text/css" />

    <script src="../js/date.min.js"></script>
    <script src="../js/handlebars-v1.2.0.js"></script>
    <script src="../js/bootstrap-switch.min.js"></script>
    <script src="../js/jquery.tablesorter.js"></script>
    <script src="../js/tables.js"></script>
    <script src="../js/widget-privatemessages.js"></script>
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
        $('.navbar').load('header.php', function() {
            $('.navitem-pms').addClass('active');
        });

        alertWidget('display-alerts');

        var action = '<?php echo $action_var; ?>';
        var msg_id = '<?php echo $msg_id; ?>';


        if(action == "read"){
            // Action = read we show the message to the user
            //console.log('read' + ' id=' + msg_id);
            messageDisplay('widget-privatemessages', msg_id);
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
            pmsWidget('widget-privatemessages',{});
        }
    });
</script>
</body>
</html>