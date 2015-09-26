<?php

namespace UserFrosting;

use \Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Ok.  This class can extend UFModel, which in turn can extend Model.  UFModel can continue to provide the registry of database information.
 * @see DatabaseInterface
 */ 
class Group extends UFModel {

    /**
     * @var string The id of the table for the current model.
     */ 
    protected static $_table_id = "group";
    
    /**
     * Create a new Group object.
     *
     */
    public function __construct($properties = [], $id = null) {
        parent::__construct($properties);
    }
    
    /**
     * @see DatabaseInterface
     */ 
    public function users(){
        $link_table = Database::getSchemaTable('group_user')->name;
        return $this->belongsToMany('UserFrosting\User', $link_table);
    }
    
    public function save(){
        // If this is being set as the default primary group, then any other group must be demoted to default group
        if ($this->is_default == GROUP_DEFAULT_PRIMARY){
            $current_default_primary = static::where('is_default', GROUP_DEFAULT_PRIMARY);
            // Exclude this object, if it exists in DB
            if ($this->id)
                $current_default_primary = $current_default_primary->where('id', '!=', $this->id);
            $current_default_primary->update(['is_default' => GROUP_DEFAULT]);
        }
        
        return parent::save();
    }
    
    /**
     * Delete this group from the database, along with any linked user and authorization rules
     *
     * @see DatabaseInterface
     */
    public function delete(){        
        // Remove all user associations
        $this->users()->detach();
        
        // Remove all group auth rules
        $auth_table = Database::getSchemaTable('authorize_group')->name;
        Capsule::table($auth_table)->where("group_id", $this->id)->delete();
         
        // Reassign any primary users to the current default primary group
        $default_primary_group = Group::where('is_default', GROUP_DEFAULT_PRIMARY)->first();
        
        $user_table = Database::getSchemaTable('user')->name;
        Capsule::table($user_table)->where('primary_group_id', $this->id)->update(["primary_group_id" => $default_primary_group->id]);
        
        // TODO: assign user to the default primary group as well?
        
        // Delete the group        
        $result = parent::delete();        
        
        return $result;
    }
}
