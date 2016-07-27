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
 * Expired authentication exception.  Used when the user's session has expired due to an expired rememberMe cookie.
 *
 * @author Alexander Weissman
 */
class AuthExpiredException extends ForbiddenException
{
    protected $default_message = 'Your session has expired.  Please sign in again.';
}
