<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.baseUrl.php
 * Type:     function
 * Name:     baseUrl
 * Purpose:  outputs url for a function with the defined name method
 * version   0.1.3
 * package   SlimViews
 * -------------------------------------------------------------
 */
function smarty_function_baseUrl($params, $template)
{
    $withUri = isset($params['withUri']) ? $params['withUri'] : true;
    $appName = isset($params['appname']) ? $params['appname'] : 'default';

    $req = \Slim\Slim::getInstance($appName)->request();
    $uri = $req->getUrl();

    if ($withUri) {
        $uri .= $req->getRootUri();
    }

    return $uri;
}
