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
 * General factory for the User Model
 */
$fm->define('UserFrosting\Sprinkle\Account\Database\Models\User')->setDefinitions([
    'user_name' => Faker::unique()->userName(),
    'first_name' => Faker::firstName(),
    'last_name' => Faker::lastName(),
    'email' => Faker::unique()->email(),
    'locale' => 'en_US',
    'flag_verified' => 1,
    'flag_enabled' => 1,
    'password' => Faker::password()
]);
