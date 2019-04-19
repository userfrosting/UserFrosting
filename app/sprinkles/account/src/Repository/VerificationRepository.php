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

/**
 * Token repository class for new account verifications.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see https://learn.userfrosting.com/users/user-accounts
 */
class VerificationRepository extends TokenRepository
{
    /**
     * {@inheritdoc}
     */
    protected $modelIdentifier = 'verification';

    /**
     * {@inheritdoc}
     */
    protected function updateUser(UserInterface $user, $args)
    {
        $user->flag_verified = 1;
        // TODO: generate user activity? or do this in controller?
        $user->save();
    }
}
