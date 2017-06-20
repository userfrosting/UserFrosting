<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Error\Handler;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * All exception handlers must implement this interface.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
interface ExceptionHandlerInterface
{
    public function __construct(ContainerInterface $ci, ServerRequestInterface $request, ResponseInterface $response, \Exception $exception, $displayErrorDetails = false);

    public function handle();

    public function renderDebugResponse();

    public function renderGenericResponse();

    public function writeToErrorLog();

    public function writeAlerts();
}
