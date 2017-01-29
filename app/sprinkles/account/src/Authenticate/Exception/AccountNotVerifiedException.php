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
 * Unverified account exception.  Used when an account is required to complete email verification, but hasn't done so yet.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AccountNotVerifiedException extends HttpException
{
    protected $default_message = 'ACCOUNT.UNVERIFIED';
    protected $http_error_code = 403;
}
