<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Repository;

use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Facades\Password;

/**
 * Token repository class for password reset requests.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see https://learn.userfrosting.com/users/user-accounts
 */
class PasswordResetRepository extends TokenRepository
{
    /**
     * {@inheritdoc}
     */
    protected $modelIdentifier = 'password_reset';

    /**
     * {@inheritdoc}
     */
    protected function updateUser(UserInterface $user, $args)
    {
        $user->password = Password::hash($args['password']);
        // TODO: generate user activity? or do this in controller?
        $user->save();
    }
}
