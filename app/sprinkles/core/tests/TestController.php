<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Tests;

use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;

/**
 * Helper trait to handle Controller Test
 *
 * @see https://akrabat.com/testing-slim-framework-actions/
 * @author Louis Charette
 */
trait TestController
{
    use TestDatabase;
    use RefreshDatabase;

    /**
     * Force session to start to avoid PHPUnit headers already sent error
     * @see https://stackoverflow.com/a/23400885/445757
     */
    public function setUp()
    {
        @session_start();
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    /**
     * @param  array $args Request arguments
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
