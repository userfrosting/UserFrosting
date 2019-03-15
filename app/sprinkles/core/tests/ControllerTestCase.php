<?php
/**
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
use UserFrosting\Tests\TestCase;

/**
 * Special TestCase for Controller Tests
 *
 * @see https://akrabat.com/testing-slim-framework-actions/
 * @author Louis Charette
 */
class ControllerTestCase extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;

    /**
     * Setup test database for controller tests
     */
    public function setUp()
    {
        parent::setUp();
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    /**
     * @param  array   $args Request arguments
     * @return Request
     */
    protected function getRequest($args = [])
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
