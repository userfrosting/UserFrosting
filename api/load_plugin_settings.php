<?php
/**
 * Load Plugin settings API page
 *
 * Tested with PHP version 5
 *
 * @author     Bryson Shepard <lilfade@fadedgaming.co>
 * @author     Project Manager: Alex Weissman
 * @copyright  2014 UserFrosting
 * @version    0.2.0
 * @link       http://www.userfrosting.com/
 */

require_once("../models/config.php");

set_error_handler('logAllErrors');

// Request method: GET
$ajax = checkRequestMode("get");

// User must be logged in
checkLoggedInUser($ajax);

//Retrieve settings
$result = loadSitePluginSettings();

restore_error_handler();

echo json_encode($result, JSON_FORCE_OBJECT);