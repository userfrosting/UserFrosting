<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Tests;

use UserFrosting\Sprinkle\Account\Database\Models\User;

/**
 * Helper trait to pose as user when running an integration test
 * @author Louis Charette
 */
trait withTestUser
{
    /**
     * @param User $user
     */
    protected function setCurrentUser(User $user)
    {
        $this->ci->currentUser = $user;
        $this->ci->authenticator->login($user);
    }

    /**
     * Logout
     */
    protected function logoutCurrentUser()
    {
        $this->ci->authenticator->logout();
    }

    /**
     * Create a test user with no settings/permissions for a controller test
     * @param bool $isAdmin
     * @return User
     */
    protected function createTestUser($isAdmin = false)
    {
        if ($isAdmin) {
            $user_id = $this->ci->config['reserved_user_ids.master'];
        } else {
            $user_id = rand(0, 1222);
        }

        $fm = $this->ci->factory;
        return $fm->create(User::class, ["id" => $user_id]);
    }
}
