<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Error;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use UserFrosting\Sprinkle\Core\Handler\ExceptionHandlerInterface;

/**
 * Default UserFrosting application error handler
 *
 * It outputs the error message and diagnostic information in either JSON, XML, or HTML based on the Accept header.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ExceptionHandlerManager extends \Slim\Handlers\Error
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var array[string] An array that maps Exception types to callbacks, for special processing of certain types of errors.
     */
    protected $exceptionHandlers = [];

    /**
     * Constructor
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     * @param boolean $displayErrorDetails Set to true to display full details
     */
    public function __construct(ContainerInterface $ci, $displayErrorDetails = false)
    {
        $this->ci = $ci;
        $this->displayErrorDetails = (bool)$displayErrorDetails;
    }

    /**
     * Invoke error handler
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param Exception              $exception The caught Exception object
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception)
    {
        // Default exception handler class
        $handlerClass = '\UserFrosting\Sprinkle\Core\Error\Handler\ExceptionHandler';

        // Get the last matching registered handler class, and instantiate it
        foreach ($this->exceptionHandlers as $exceptionClass => $matchedHandlerClass) {
            if ($exception instanceof $exceptionClass) {
                $handlerClass = $matchedHandlerClass;
            }
        }

        $handler = new $handlerClass($this->ci, $request, $response, $exception, $this->displayErrorDetails);

        return $handler->handle();
    }

    /**
     * Register an exception handler for a specified exception class.
     *
     * The exception handler must implement \UserFrosting\Sprinkle\Core\Handler\ExceptionHandlerInterface.
     *
     * @param string $exceptionClass The fully qualified class name of the exception to handle.
     * @param string $handlerClass The fully qualified class name of the assigned handler.
     * @throws InvalidArgumentException If the registered handler fails to implement ExceptionHandlerInterface
     */
    public function registerHandler($exceptionClass, $handlerClass)
    {
        if (!is_a($handlerClass, '\UserFrosting\Sprinkle\Core\Error\Handler\ExceptionHandlerInterface', true)) {
            throw new \InvalidArgumentException("Registered exception handler must implement ExceptionHandlerInterface!");
        }

        $this->exceptionHandlers[$exceptionClass] = $handlerClass;
    }
}
