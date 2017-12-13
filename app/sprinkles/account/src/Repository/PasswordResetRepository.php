<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Repository;

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
     * {@inheritDoc}
     */
    protected $modelIdentifier = 'password_reset';

    /**
     * {@inheritDoc}
     */
    protected function updateUser($user, $args)
    {
        $user->password = Password::hash($args['password']);
        // TODO: generate user activity? or do this in controller?
        $user->save();
    }
}
