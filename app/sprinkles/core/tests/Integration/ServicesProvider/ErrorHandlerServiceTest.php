<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use UserFrosting\Sprinkle\Core\Error\ExceptionHandlerManager;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `errorHandler` service.
 * Check to see if service returns what it's supposed to return
 */
class ErrorHandlerServiceTest extends TestCase
{
    public function testService()
    {
        $this->assertInstanceOf(ExceptionHandlerManager::class, $this->ci->errorHandler);
    }

    /**
     * @depends testService
     */
    public function testphpErrorHandlerService()
    {
        $this->assertInstanceOf(ExceptionHandlerManager::class, $this->ci->phpErrorHandler);
    }
}
