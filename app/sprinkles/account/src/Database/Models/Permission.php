<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models;

use Illuminate\Database\Eloquent\Builder;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Permission Class.
 *
 * Represents a permission for a role or user.
 * @author Alex Weissman (https://alexanderweissman.com)
 * @property string slug
 * @property string name
 * @property string conditions
 * @property string description
 */
class Permission extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'permissions';

    protected $fillable = [
        'slug',
        'name',
        'conditions',
        'description'
    ];

    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = true;

    /**
     * Delete this permission from the database, removing associations with roles.
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsToMany($classMapper->getClassMapping('role'), 'permission_roles', 'permission_id', 'role_id')->withTimestamps();
    }

    /**
     * Query scope to get all permissions assigned to a specific role.
     *
     * @param  Builder $query
     * @param  int     $roleId
     * @return Builder
     */
    public function scopeForRole($query, $roleId)
    {
        return $query->join('permission_roles', function ($join) use ($roleId) {
            $join->on('permission_roles.permission_id', 'permissions.id')
                 ->where('role_id', $roleId);
        });
    }

    /**
     * Query scope to get all permissions NOT associated with a specific role.
     *
     * @param  Builder $query
     * @param  int     $roleId
     * @return Builder
     */
    public function scopeNotForRole($query, $roleId)
    {
        return $query->join('permission_roles', function ($join) use ($roleId) {
            $join->on('permission_roles.permission_id', 'permissions.id')
                 ->where('role_id', '!=', $roleId);
        });
    }

    /**
     * Get a list of users who have this permission, along with a list of roles through which each user has the permission.
     *
     * @return \UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyThrough
     */
    public function users()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsToManyThrough(
            $classMapper->getClassMapping('user'),
            $classMapper->getClassMapping('role'),
            'permission_roles',
            'permission_id',
            'role_id',
            'role_users',
            'role_id',
            'user_id'
        );
    }
}
