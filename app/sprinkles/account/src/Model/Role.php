<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Model\UFModel;

/**
 * Role Class
 *
 * Represents a role, which aggregates permissions and to which a user can be assigned.
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/components/#authorization
 * @property string slug
 * @property string name
 * @property string description
 */
class Role extends UFModel
{
   
    /**
     * @var string The name of the table for the current model.
     */ 
    protected $table = "roles";
    
    protected $fillable = [
        "slug",
        "name",
        "description"
    ];    

    /**
     * Delete this role from the database, removing associations with permissions and users.
     *
     */
    public function delete()
    {        
        // Remove all permission associations
        $this->permissions()->detach();
        
        // Remove all user associations
        $this->users()->detach();
            
        // Delete the role        
        $result = parent::delete();        
        
        return $result;
    }    
    
    /**
     * Get a list of permissions assigned to this role.
     */
    public function permissions()
    {
        return $this->belongsToMany('UserFrosting\Sprinkle\Account\Model\Permission', 'permission_roles');
    }
    
    /**
     * Get a list of users who have this role.
     */
    public function users()
    {
        return $this->belongsToMany('UserFrosting\Sprinkle\Account\Model\User', 'role_users');
    }
}
