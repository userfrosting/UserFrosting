<?php

// Get the new versions file
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://raw.githubusercontent.com/alexweissman/UserFrosting/master/upgrade/versions.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);

// Save the new versions file to the local host
if($data != NULL){
    // New update data
    $fileData = $data;

    // New filename
    $saveToFile = 'versions.txt';

    // Prep file for writing
    $fileOp = file_put_contents($saveToFile, $fileData);
}


header('Location: confirm_version.php');
