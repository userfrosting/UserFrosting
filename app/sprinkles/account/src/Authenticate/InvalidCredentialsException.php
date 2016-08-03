<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Authenticate;

use UserFrosting\Support\Exception\ForbiddenException;

/**
 * Invalid credentials exception.  Used when an account fails authentication for some reason.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class InvalidCredentialsException extends ForbiddenException
{
    protected $default_message = 'ACCOUNT_USER_OR_PASS_INVALID';
}
