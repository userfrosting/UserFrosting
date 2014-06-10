<?php

require_once("models/config.php");

define("PHP_BR", "<br>");

echo PHP_BR.PHP_BR.PHP_BR;
echo 'Site root: '.SITE_ROOT.PHP_BR;
echo '$websiteUrl: '.$websiteUrl.PHP_BR;
echo 'Local root: '.LOCAL_ROOT.PHP_BR;
echo 'file path: '.__FILE__.PHP_BR;
echo 'file pate with dirname: '.dirname(__FILE__).PHP_BR;
echo '$_SERVER servername: '.$_SERVER['SERVER_NAME'].PHP_BR;
echo '$_SERVER http host: '.$_SERVER['HTTP_HOST'].PHP_BR;
echo '$_SERVER php self: '.$_SERVER['PHP_SELF'].PHP_BR;
echo '$_SERVER document root: '.$_SERVER['DOCUMENT_ROOT'].PHP_BR;
echo 'get rel doc path: '.getRelativeDocumentPath(__FILE__).PHP_BR;
echo 'get abs doc path: '.getAbsoluteDocumentPath(__FILE__).PHP_BR;

echo PHP_BR.PHP_BR;

echo '$_SERVER[PHP_SELF]: ' . $_SERVER['PHP_SELF'] . '<br />';
echo 'Dirname($_SERVER[PHP_SELF]: ' . dirname($_SERVER['PHP_SELF']) . '<br>';

echo PHP_BR.PHP_BR;

$parentparentdir=basename(dirname(__FILE__));
echo $parentparentdir; //will output 'content'

echo PHP_BR.PHP_BR;

/**
 * @def (string) DS - Directory separator.
 */
define("DS","/",true);

/**
 * @def (resource) BASE_PATH - get a base path.
 */
define('BASE_PATH',basename(dirname(__FILE__)).DS,true);

echo BASE_PATH;

echo PHP_BR.PHP_BR;

// Is the user using HTTPS?
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';

// Complete the URL
$url .= $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

// echo the URL
echo $url;

?>