<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Util;

use UserFrosting\Support\Exception\HttpException;

/**
 * Password hash failure exception.  Used when the supplied password could not be hashed for some reason.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class HashFailedException extends HttpException
{
    protected $defaultMessage = 'PASSWORD.HASH_FAILED';
    protected $httpErrorCode = 500;
}
