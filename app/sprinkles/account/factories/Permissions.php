<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

use League\FactoryMuffin\Faker\Facade as Faker;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;

/*
 * General factory for the Permission Model
 */
$fm->define(Permission::class)->setDefinitions([
    'name'        => Faker::word(),
    'description' => Faker::paragraph(),
    'conditions'  => Faker::word(),
    'slug'        => function ($object, $saved) {
        return uniqid();
    },
]);
