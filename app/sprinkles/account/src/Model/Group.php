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
use UserFrosting\Sprinkle\Account\Model\Collection\UserCollection;

/**
 * Group Class
 *
 * Represents a group object as stored in the database.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/
 *
 * @property string name
 * @property string theme
 * @property string landing_page
 * @property string icon
 */
class Group extends UFModel {

    /**
     * @var string The name of the table for the current model.
     */ 
    protected $table = "groups";
    
    protected $fillable = [
        "name",
        "description",
        "theme",
        "landing_page",
        "icon"
    ];
    
    /**
     * Lazily load a collection of Users which belong to this group.
     */ 
    public function users()
    {
        return $this->hasMany('\UserFrosting\Sprinkles\Account\Model\User');
    }
    
    public function save(array $options = []){
        // If this is being set as the default primary group, then any other group must be demoted to default group
        if ($this->is_default == GROUP_DEFAULT_PRIMARY){
            $current_default_primary = static::where('is_default', GROUP_DEFAULT_PRIMARY);
            // Exclude this object, if it exists in DB
            if ($this->id)
                $current_default_primary = $current_default_primary->where('id', '!=', $this->id);
            $current_default_primary->update(['is_default' => GROUP_DEFAULT]);
        }
        
        return parent::save($options);
    }
    
    /**
     * Delete this group from the database, along with any linked user and authorization rules
     *
     */
    public function delete(){        
        // Remove all user associations
        $this->users()->detach();
        
        // Remove all group auth rules
        Capsule::table('authorize_group')->where("group_id", $this->id)->delete();
         
        // Reassign any primary users to the current default primary group
        $default_primary_group = Group::where('is_default', GROUP_DEFAULT_PRIMARY)->first();
        
        Capsule::table('user')->where('primary_group_id', $this->id)->update(["primary_group_id" => $default_primary_group->id]);
        
        // TODO: assign user to the default primary group as well?
        
        // Delete the group        
        $result = parent::delete();        
        
        return $result;
    }
}
