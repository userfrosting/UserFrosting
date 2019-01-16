<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authenticate\Exception;

use UserFrosting\Support\Exception\ForbiddenException;

/**
 * Compromised authentication exception.  Used when we suspect theft of the rememberMe cookie.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AuthCompromisedException extends ForbiddenException
{
    protected $defaultMessage = 'ACCOUNT.SESSION_COMPROMISED';
}
