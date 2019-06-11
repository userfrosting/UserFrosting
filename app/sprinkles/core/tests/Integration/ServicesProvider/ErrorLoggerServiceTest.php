<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Monolog\Logger;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `errorLogger` service.
 * Check to see if service returns what it's supposed to return
 */
class ErrorLoggerServiceTest extends TestCase
{
    public function testService()
    {
        $this->assertInstanceOf(Logger::class, $this->ci->errorLogger);
    }
}
