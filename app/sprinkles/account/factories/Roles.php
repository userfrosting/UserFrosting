<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

use League\FactoryMuffin\Faker\Facade as Faker;
use UserFrosting\Sprinkle\Account\Database\Models\Role;

/*
 * General factory for the Role Model
 */
$fm->define(Role::class)->setDefinitions([
    'name'        => Faker::word(),
    'description' => Faker::paragraph(),
    'slug'        => function ($object, $saved) {
        return uniqid();
    },
]);
