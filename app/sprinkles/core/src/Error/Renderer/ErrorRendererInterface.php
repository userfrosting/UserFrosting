<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Renderer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ErrorRendererInterface
 */
interface ErrorRendererInterface
{
    /**
     * @param ServerRequestInterface $request             The most recent Request object
     * @param ResponseInterface      $response            The most recent Response object
     * @param \Throwable             $exception           The caught Exception object
     * @param bool                   $displayErrorDetails
     */
    public function __construct(ServerRequestInterface $request, ResponseInterface $response, $exception, $displayErrorDetails = false);

    /**
     * @return string
     */
    public function render();

    /**
     * @return \Slim\Http\Body
     */
    public function renderWithBody();
}
