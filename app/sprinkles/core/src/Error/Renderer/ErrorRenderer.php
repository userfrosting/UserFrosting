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
use Slim\Http\Body;

/**
 * [abstract description]
 * @var [type]
 */
abstract class ErrorRenderer implements ErrorRendererInterface
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var \Throwable
     */
    protected $exception;

    /**
     * Tells the renderer whether or not to output detailed error information to the client.
     * Each renderer may choose if and how to implement this.
     *
     * @var bool
     */
    protected $displayErrorDetails;

    /**
     * Create a new ErrorRenderer object.
     *
     * @param ServerRequestInterface $request             The most recent Request object
     * @param ResponseInterface      $response            The most recent Response object
     * @param \Throwable             $exception           The caught Exception object
     * @param bool                   $displayErrorDetails
     */
    public function __construct(ServerRequestInterface $request, ResponseInterface $response, $exception, $displayErrorDetails = false)
    {
        $this->request = $request;
        $this->response = $response;
        $this->exception = $exception;
        $this->displayErrorDetails = $displayErrorDetails;
    }

    abstract public function render();

    /**
     * @return Body
     */
    public function renderWithBody()
    {
        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($this->render());

        return $body;
    }
}
