<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
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
    /**
     * @param ContainerInterface     $ci
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param \Throwable             $exception
     * @param bool                   $displayErrorDetails
     */
    public function __construct(ContainerInterface $ci, ServerRequestInterface $request, ResponseInterface $response, $exception, $displayErrorDetails = false);

    /**
     * @return ResponseInterface
     */
    public function handle();

    /**
     * @return ResponseInterface
     */
    public function renderDebugResponse();

    /**
     * @return ResponseInterface
     */
    public function renderGenericResponse();

    /**
     * Write to the error log
     */
    public function writeToErrorLog();

    /**
     * Write user-friendly error messages to the alert message stream.
     */
    public function writeAlerts();
}
