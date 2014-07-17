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

require_once("../../models/funcs.php");

session_start();

// Always a publically accessible script
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    addAlert($_POST['type'], $_POST['message']);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET'){
    echo json_encode($_SESSION["userAlerts"]);
    
    // Reset alerts after they have been delivered
    $_SESSION["userAlerts"] = array();
}

?>