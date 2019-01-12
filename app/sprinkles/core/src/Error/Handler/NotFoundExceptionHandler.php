<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Handler;

use Psr\Http\Message\ResponseInterface;

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
     * @return ResponseInterface
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
