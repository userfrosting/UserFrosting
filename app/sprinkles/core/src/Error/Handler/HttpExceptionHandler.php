<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Handler;

use UserFrosting\Support\Exception\HttpException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Handler for HttpExceptions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class HttpExceptionHandler extends ExceptionHandler
{
    /**
     * For HttpExceptions, only write to the error log if the status code is 500
     */
    public function writeToErrorLog()
    {
        if ($this->statusCode != 500) {
            return;
        }

        parent::writeToErrorLog();
    }

    /**
     * Resolve the status code to return in the response from this handler.
     *
     * @return int
     */
    protected function determineStatusCode()
    {
        if ($this->request->getMethod() === 'OPTIONS') {
            return 200;
        } elseif ($this->exception instanceof HttpException) {
            return $this->exception->getHttpErrorCode();
        }

        return 500;
    }

    /**
     * Resolve a list of error messages to present to the end user.
     *
     * @return array
     */
    protected function determineUserMessages()
    {
        if ($this->exception instanceof HttpException) {
            return $this->exception->getUserMessages();
        }

        // Fallback
        return [
            new UserMessage('ERROR.SERVER')
        ];
    }
}
