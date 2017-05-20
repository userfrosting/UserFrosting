<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2017 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Model\Relations;

use Illuminate\Database\Eloquent\Relations\HasMany;
use UserFrosting\Sprinkle\Core\Model\Relations\Concerns\Syncable;

/**
 * @link https://github.com/laravel/framework/blob/5.4/src/Illuminate/Database/Eloquent/Relations/HasMany.php
 */
class HasManySyncable extends HasMany
{
    use Syncable;
}
