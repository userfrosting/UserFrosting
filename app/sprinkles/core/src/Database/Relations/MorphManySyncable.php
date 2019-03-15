<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Relations;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use UserFrosting\Sprinkle\Core\Database\Relations\Concerns\Syncable;

/**
 * A MorphMany relationship that constrains on the value of an additional foreign key in the pivot table.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see https://github.com/laravel/framework/blob/5.4/src/Illuminate/Database/Eloquent/Relations/MorphMany.php
 */
class MorphManySyncable extends MorphMany
{
    use Syncable;
}
