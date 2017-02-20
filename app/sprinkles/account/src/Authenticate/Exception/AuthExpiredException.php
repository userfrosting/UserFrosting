<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Authenticate\Exception;

use UserFrosting\Support\Exception\HttpException;

/**
 * Expired authentication exception.  Used when the user needs to authenticate/reauthenticate.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AuthExpiredException extends HttpException
{
    protected $default_message = 'ACCOUNT.SESSION_EXPIRED';
    protected $http_error_code = 401;
}
