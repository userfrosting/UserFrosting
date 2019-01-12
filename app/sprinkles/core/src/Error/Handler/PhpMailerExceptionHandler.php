<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Handler;

use UserFrosting\Support\Message\UserMessage;

/**
 * Handler for phpMailer exceptions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class PhpMailerExceptionHandler extends ExceptionHandler
{
    /**
     * Resolve a list of error messages to present to the end user.
     *
     * @return array
     */
    protected function determineUserMessages()
    {
        return [
            new UserMessage('ERROR.MAIL')
        ];
    }
}
