<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models\Events;

use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\Facades\Cache;
use UserFrosting\Sprinkle\Core\Facades\Config;

/**
 * Event for global cache object deletion on model update
 * @author Louis Charette
 */
class DeleteUserCacheEvent
{
    /**
     * @param Model $user
     */
    public function __construct(Model $user)
    {
        $key = Config::get('cache.user.key');
        Cache::forget($key . $user->id);
    }
}
