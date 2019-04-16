<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models;

/**
 * Session Class
 *
 * Represents a session object as stored in the database.
 */
class Session extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'sessions';
}
