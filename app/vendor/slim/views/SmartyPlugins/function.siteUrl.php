<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.siteUrl.php
 * Type:     function
 * Name:     siteUrl
 * Purpose:  outputs url for a function with the defined name method
 * version   0.1.3
 * package   SlimViews
 * -------------------------------------------------------------
 */
function smarty_function_siteUrl($params, $template)
{
    $withUri = isset($params['withUri']) ? $params['withUri'] : true;
    $appName = isset($params['appname']) ? $params['appname'] : 'default';
    $url = isset($params['url']) ? $params['url'] : '';

    $req = \Slim\Slim::getInstance($appName)->request();
    $uri = $req->getUrl();

    if ($withUri) {
        $uri .= $req->getRootUri();
    }

    return $uri . '/' . ltrim($url, '/');
}
