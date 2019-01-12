<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models;

use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Group Class
 *
 * Represents a group object as stored in the database.
 *
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/
 *
 * @property string slug
 * @property string name
 * @property string description
 * @property string icon
 */
class Group extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'groups';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon'
    ];

    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = true;

    /**
     * Delete this group from the database, along with any user associations
     *
     * @todo What do we do with users when their group is deleted?  Reassign them?  Or, can a user be "groupless"?
     */
    public function delete()
    {
        // Delete the group
        $result = parent::delete();

        return $result;
    }

    /**
     * Lazily load a collection of Users which belong to this group.
     */
    public function users()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->hasMany($classMapper->getClassMapping('user'), 'group_id');
    }
}
