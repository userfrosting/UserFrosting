<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Error\Handler;

use UserFrosting\Sprinkle\Core\Error\Handler\HttpExceptionHandler;

/**
 * Handler for AuthCompromisedExceptions.
 *
 * Warns the user that their account may have been compromised due to a stolen "remember me" cookie.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AuthCompromisedExceptionHandler extends HttpExceptionHandler
{
    /**
     * Render a generic, user-friendly response without sensitive debugging information.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function renderGenericResponse()
    {
        $template = $this->ci->view->getEnvironment()->loadTemplate('pages/error/compromised.html.twig');

        return $this->response
            ->withStatus($this->statusCode)
            ->withHeader('Content-type', $this->contentType)
            ->write($template->render([]));
    }
}
