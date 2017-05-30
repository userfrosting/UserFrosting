<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Error\Handler;

use UserFrosting\Support\Exception\HttpException;

/**
 * Handler for HttpExceptions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class HttpExceptionHandler extends ExceptionHandler
{
    /**
     * For HttpExceptions, only write to the error log if the status code is 500
     *
     * @return void
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
        // Grab messages from the exception
        return $this->exception->getUserMessages();
    }
}
