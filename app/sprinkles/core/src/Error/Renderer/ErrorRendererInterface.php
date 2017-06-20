<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Error\Renderer;

interface ErrorRendererInterface
{
    /**
     * @param ServerRequestInterface     $request   The most recent Request object
     * @param ResponseInterface          $response  The most recent Response object
     * @param Exception                  $exception The caught Exception object
     * @param bool $displayErrorDetails
     */
    public function __construct($request, $response, $exception, $displayErrorDetails = false);
    
    /**
     * @return string
     */
    public function render();

    /**
     * @return \Slim\Http\Body
     */
    public function renderWithBody();
}
