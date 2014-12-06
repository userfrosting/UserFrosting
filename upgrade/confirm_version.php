<?php
/*
 * Here we check the version available against the currently installed version, if the versions are different then
 * we start the update based on the versions available moving from one version to the next until were at the newest
 * version. From here we will include a link to the newest available files that the user will need to finish the update.
 *
 * This could pose a problem if the password generation changes as it will make the users unable to login again.
 */
// Include the required files that we will need
require_once('../models/config.php');
require_once('../models/db-settings.php');

// Grab up the current changes from the master repo so that we can update (cache them to file if able to otherwise move on)
$versions = file_get_contents('versions.txt');

// Grab all versions from the update url and push the values to a array
$versionList = explode("\n", $versions);

// Remove new lines and carriage returns from the array
$versionList = str_replace(array("\n", "\r"), '', $versionList);

// Search the array to find out where the currently installed version falls
$nV = array_search($version, $versionList);

// Find out if the update is in the list or not
$newVersion = isset($versionList[$nV - 1]);

// Find out if we need to do the update or not based on the version information
// If update is found then forward to the installer to run the script else exit
if($newVersion == NULL){
    header('Location: ../index.php');
    exit();
} else {
    $newVersion = $versionList[$nV-1];
}

// Get the new install file if it's not already downloaded
if (!file_exists($newVersion.'.install.php')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://raw.githubusercontent.com/alexweissman/UserFrosting/master/upgrade/' . $newVersion . '.install.php');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);

    // Save the new data to the file
    if($data != NULL){
        // New update data
        $fileData = $data;

        // New filename
        $saveToFile = $newVersion.'.install.php';

        // Prep file for writing
        $fileOp = file_put_contents($saveToFile, $fileData);
    }
}

// Execute the new version upgrade
header('Location: '.$newVersion.'.install.php');
