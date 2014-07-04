<?php
require_once("../models/config.php");
/*
 msg_id: msg_id,
 sender_id: $('#' + dialog_id + ' input[name="sender_id"]' ).val(),
 title: $('#' + dialog_id + ' input[name="title"]' ).val(),
 receiver_name: $('#' + dialog_id + ' input[name="receiver_name"]' ).val(),
 message: $('#' + dialog_id + ' input[name="message"]' ).val(),
 csrf_token: $('#' + dialog_id + ' input[name="csrf_token"]' ).val(),
 */

$validate = new Validator();

$msg_id = $validate->requiredPostVar("msg_id");
$sender_id = $validate->requiredPostVar("sender_id");
$title = $validate->requiredPostVar("title");
$receiver_name = $validate->requiredPostVar("receiver_name");
$message = $validate->requiredPostVar("message");
$csrf_token = $validate->requiredPostVar("csrf_token");

// Check the data if all good then send the message