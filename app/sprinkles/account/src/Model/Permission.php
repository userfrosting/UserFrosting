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
 * Permission Class.
 *
 * Represents a permission for a role or user.
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/components/#authorization
 * @property string slug
 * @property string name
 * @property string conditions
 * @property string description
 */
class Permission extends UFModel
{
    /**
     * @var string The name of the table for the current model.
     */ 
    protected $table = "permissions";
    
    protected $fillable = [
        "slug",
        "name",
        "conditions",
        "description"
    ];    

    /**
     * Delete this permission from the database, removing associations with roles.
     *
     */
    public function delete()
    {
        // Remove all role associations
        $this->roles()->detach();
        
        // Delete the permission        
        $result = parent::delete();
        
        return $result;
    }
    
    /**
     * Get a list of roles to which this permission is assigned.
     */   
    public function roles()
    {
        return $this->belongsToMany('UserFrosting\Sprinkle\Account\Model\Role', 'permission_roles');
    }
}
