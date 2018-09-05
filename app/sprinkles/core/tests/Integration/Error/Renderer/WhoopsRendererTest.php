<?php

namespace UserFrosting\Tests\Integration\Error\Renderer;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use UserFrosting\Sprinkle\Core\Error\Renderer\WhoopsRenderer;
use UserFrosting\Tests\TestCase;

class WhoopsRendererTest extends TestCase
{
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
        $this->assertTrue(!!preg_match('/RuntimeException: This is my exception in file /', $renderBody));
        $this->assertTrue(!!preg_match('/<span>This is my exception<\/span>/', $renderBody));
    }
}