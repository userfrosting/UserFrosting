<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Error\Handler;

use UserFrosting\Sprinkle\Core\Error\Handler\HttpExceptionHandler;
use UserFrosting\Support\Exception\HttpException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Handler for NotFoundExceptions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class NotFoundExceptionHandler extends HttpExceptionHandler
{
    /**
     * Custom handling for NotFoundExceptions.  Always render a generic response!
     *
     * @return Response
     */
    public function handle()
    {
        // Render generic error page
        $response = $this->renderGenericResponse();

        // If this is an AJAX request and AJAX debugging is turned off, write messages to the alert stream
        if ($this->request->isXhr() && !$this->ci->config['site.debug.ajax']) {
            $this->writeAlerts();
        }

        return $response;
    }
}
