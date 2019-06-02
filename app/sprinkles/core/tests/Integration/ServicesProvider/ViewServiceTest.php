<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Slim\Views\Twig;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `view` service.
 * Check to see if service returns what it's supposed to return
 */
class ViewServiceTest extends TestCase
{
    public function testService()
    {
        $this->assertInstanceOf(Twig::class, $this->ci->view);
    }

    /**
     * @depends testService
     */
    public function testServiceWithCache()
    {
        $this->ci->config['cache.twig'] = true;
        $this->assertInstanceOf(Twig::class, $this->ci->view);
    }

    /**
     * @depends testService
     */
    public function testServiceWithDebug()
    {
        $this->ci->config['debug.twig'] = true;
        $this->assertInstanceOf(Twig::class, $this->ci->view);
    }
}
