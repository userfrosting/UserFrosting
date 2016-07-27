<?php
 
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Model;

use UserFrosting\Support\Exception\ForbiddenException;

/**
 * Invalid database exception.  Used when the database cannot be accessed.
 *
 * @author Alexander Weissman
 */
class DatabaseInvalidException extends ForbiddenException
{
    protected $default_message = 'Cannot connect to the database.  If you are an administrator, please check your error log.';  
}
