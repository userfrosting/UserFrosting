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

// Simple function to see if the update file exists on the remote host, for checking for a upgrade script
function is_url_exist($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if($code == 200){
        $status = true;
    }else{
        $status = false;
    }
    curl_close($ch);
    return $status;
}

// This will be set when requesting file changes from github always from the same location
//$upgrade_version = '0.2.2';
$upgrade_version = '';

// Grab up the current changes from the master repo so that we can update (cache them to file if able to otherwise move on)
//$update_url = 'https://raw.githubusercontent.com/alexweissman/UserFrosting/master/update.md';
$updateUrl = file_get_contents('versions.txt');
// This will be where the change log is stored with version info and changes associated with them

// Grab all versions from the update url and push the values to a array
$versionList = explode("\n", $updateUrl);

// Remove new lines and carriage returns from the array
$versionList = str_replace(array("\n", "\r"), '', $versionList);

// Search the array to find out where the currently installed version falls
$nV = array_search($version, $versionList);

// Find out if the update is in the list or not
$newVersion = isset($versionList[$nV - 1]);

// Find out if we need to do the update or not based on the version information
// If update is found then forward to the installer to run the script else exit
if($newVersion == NULL){
    $sql = '';
    $newVersion = 'already at latest version';
} else {
    $sql = '';
    $newVersion = $versionList[$nV-1];}

//simply some output for reference
echo 'key is '.$nV.' - Current version = '.$version.' - New version = '.$newVersion.'<br />';
var_dump($nV);
echo '<br />';
$doesExist = is_url_exist('http://www.userfrosting.com/about.html');

var_dump($doesExist);

echo '<br />';

echo 'forward to: '.SITE_ROOT.'update/'.$newVersion.'.install.php';
