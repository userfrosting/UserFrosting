<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Integration\Error\Renderer;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use UserFrosting\Sprinkle\Core\Error\Renderer\WhoopsRenderer;
use UserFrosting\Tests\TestCase;
use Whoops\Exception\Inspector;

class WhoopsRendererTest extends TestCase
{
    /**
     * @covers Whoops\Exception\Inspector::getPreviousExceptionMessages
     */
    public function testGetPreviousExceptionMessages()
    {
        $exception1 = $this->getException('My first exception');
        $exception2 = $this->getException('My second exception', 0, $exception1);
        $exception3 = $this->getException('And the third one', 0, $exception2);

        $inspector = new Inspector($exception3);

        $previousExceptions = $inspector->getPreviousExceptionMessages();

        $this->assertEquals($exception2->getMessage(), $previousExceptions[0]);
        $this->assertEquals($exception1->getMessage(), $previousExceptions[1]);
    }

    /**
     * @depends testGetPreviousExceptionMessages
     */
    public function testRenderWhoopsPage()
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exception = new \RuntimeException('This is my exception');

        $whoopsRenderer = new WhoopsRenderer($request, $response, $exception, true);

        // Avoid handle cli SAPI
        $whoopsRenderer->handleUnconditionally(true);

        $renderBody = $whoopsRenderer->render();
        $this->assertTrue((bool) preg_match('/RuntimeException: This is my exception in file /', $renderBody));
        $this->assertTrue((bool) preg_match('/<span>This is my exception<\/span>/', $renderBody));
    }

    /**
     * @param  string     $message
     * @param  int        $code
     * @param  \Exception $previous
     * @return \Exception
     */
    protected function getException($message = null, $code = 0, $previous = null)
    {
        return new \Exception($message, $code, $previous);
    }
}
