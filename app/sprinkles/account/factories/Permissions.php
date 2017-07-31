<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

use League\FactoryMuffin\Faker\Facade as Faker;

/**
 * General factory for the Permission Model
 */
$fm->define('UserFrosting\Sprinkle\Account\Database\Models\Permission')->setDefinitions([
    'slug' => Faker::word(),
    'name' => Faker::word(),
    'description' => Faker::paragraph(),
    'conditions' => Faker::word()
]);
