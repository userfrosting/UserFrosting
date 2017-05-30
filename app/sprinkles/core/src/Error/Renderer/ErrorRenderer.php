<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Error\Renderer;

use Slim\Http\Body;

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
     * @var Exception
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
     * @param ServerRequestInterface     $request   The most recent Request object
     * @param ResponseInterface          $response  The most recent Response object
     * @param Exception                  $exception The caught Exception object
     * @param bool                       $displayErrorDetails
     */
    public function __construct($request, $response, $exception, $displayErrorDetails = false)
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
