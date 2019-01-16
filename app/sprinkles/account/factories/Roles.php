<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

use League\FactoryMuffin\Faker\Facade as Faker;

/*
 * General factory for the Role Model
 */
$fm->define('UserFrosting\Sprinkle\Account\Database\Models\Role')->setDefinitions([
    'slug'        => Faker::unique()->word(),
    'name'        => Faker::word(),
    'description' => Faker::paragraph()
]);
