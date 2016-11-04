<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Repository;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use Interop\Container\ContainerInterface;
use UserFrosting\Sprinkle\Account\Model\User;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;

class VerificationRepository extends TokenRepository
{
    protected $modelIdentifier = 'verification';

    protected function updateUser($user, $args)
    {
        $user->flag_verified = 1;
        // TODO: generate user activity? or do this in controller?
        $user->save();    
    }    
}
