<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests;

use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Trait used to run test against the `test_integration` db connection
 *
 * @author Louis Charette
 */
trait withController
{
    /**
     * @param  array   $args Request arguments
     * @return Request
     */
    protected function getRequest(array $args = [])
    {
        $env = Environment::mock($args);

        return Request::createFromEnvironment($env);
    }

    /**
     * @return Response
     */
    protected function getResponse()
    {
        return new Response();
    }
}
