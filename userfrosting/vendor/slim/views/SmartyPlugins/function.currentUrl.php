<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.currentUrl.php
 * Type:     function
 * Name:     currentUrl
 * Purpose:  outputs url for a function with the defined name method
 * version   0.1.3
 * package   SlimViews
 * -------------------------------------------------------------
 */
function smarty_function_currentUrl($params, $template)
{
    $appName = isset($params['appname']) ? $params['appname'] : 'default';
    $withQueryString = isset($params['queryString']) ? $params['queryString'] : true;

    $app = \Slim\Slim::getInstance($appName);
    $req = $app->request();
    $uri = $req->getUrl() . $req->getPath();

    if ($withQueryString) {
        $env = $app->environment();

        if ($env['QUERY_STRING']) {
            $uri .= '?' . $env['QUERY_STRING'];
        }
    }

    return $uri;
}
