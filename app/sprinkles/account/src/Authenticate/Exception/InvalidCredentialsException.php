<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Authenticate\Exception;

use UserFrosting\Support\Exception\HttpException;

/**
 * Invalid credentials exception.  Used when an account fails authentication for some reason.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class InvalidCredentialsException extends HttpException
{
    protected $defaultMessage = 'USER_OR_PASS_INVALID';
    protected $httpErrorCode = 403;
}
