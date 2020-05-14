<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

use League\FactoryMuffin\Faker\Facade as Faker;
use UserFrosting\Sprinkle\Account\Database\Models\User;

/*
 * General factory for the User Model
 */
$fm->define(User::class)->setDefinitions([
    'first_name'    => Faker::firstNameMale(),
    'last_name'     => Faker::firstNameMale(),
    'user_name'     => function ($object, $saved) {
        return $object->first_name . '_' . $object->last_name;
    },
    'email'         => Faker::unique()->email(),
    'locale'        => 'en_US',
    'flag_verified' => 1,
    'flag_enabled'  => 1,
    'password'      => Faker::password(),
]);
