<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Integration\Error\Handler;

use RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use UserFrosting\Sprinkle\Core\Error\Handler\ExceptionHandler;
use UserFrosting\Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    /**
     * Test ExceptionHandler constructor
     */
    public function testConstructor()
    {
        $handler = new ExceptionHandler($this->ci, $this->getRequest(), $this->getResponse(), $this->getException(), false);
        $this->assertInstanceOf(ExceptionHandler::class, $handler);

        return $handler;
    }

    /**
     * @return ServerRequestInterface
     */
    private function getRequest()
    {
        return $this->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return ResponseInterface
     */
    private function getResponse()
    {
        return $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return RuntimeException
     */
    private function getException()
    {
        return new RuntimeException('This is my exception');
    }
}
