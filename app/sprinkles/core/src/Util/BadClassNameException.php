<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

/**
 * Bad class name exception.  Used when a class name is dynamically invoked, but the class does not exist.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class BadClassNameException extends \LogicException
{
    //
}
