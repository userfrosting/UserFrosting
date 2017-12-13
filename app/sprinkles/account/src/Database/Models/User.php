<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Database\Models;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\SoftDeletes;
use UserFrosting\Sprinkle\Account\Facades\Password;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * User Class
 *
 * Represents a User object as stored in the database.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @property int id
 * @property string user_name
 * @property string first_name
 * @property string last_name
 * @property string email
 * @property string locale
 * @property string theme
 * @property int group_id
 * @property bool flag_verified
 * @property bool flag_enabled
 * @property int last_activity_id
 * @property timestamp created_at
 * @property timestamp updated_at
 * @property string password
 * @property timestamp deleted_at
 */
class User extends Model
{
    use SoftDeletes;

    /**
     * The name of the table for the current model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Fields that should be mass-assignable when creating a new User.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_name',
        'first_name',
        'last_name',
        'email',
        'locale',
        'theme',
        'group_id',
        'flag_verified',
        'flag_enabled',
        'last_activity_id',
        'password',
        'deleted_at'
    ];

    /**
     * A list of attributes to hide by default when using toArray() and toJson().
     *
     * @link https://laravel.com/docs/5.4/eloquent-serialization#hiding-attributes-from-json
     * @var string[]
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var string[]
     */
    protected $dates = [
        'deleted_at'
    ];

    protected $appends = [
        'full_name'
    ];

    /**
     * Cached dictionary of permissions for the user.
     *
     * @var array
     */
    protected $cachedPermissions;

    /**
     * Enable timestamps for Users.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Determine if the property for this object exists.
     *
     * We add relations here so that Twig will be able to find them.
     * See http://stackoverflow.com/questions/29514081/cannot-access-eloquent-attributes-on-twig/35908957#35908957
     * Every property in __get must also be implemented here for Twig to recognize it.
     * @param string $name the name of the property to check.
     * @return bool true if the property is defined, false otherwise.
     */
    public function __isset($name)
    {
        if (in_array($name, [
                'group',
                'last_sign_in_time',
                'avatar'
            ])) {
            return true;
        } else {
            return parent::__isset($name);
        }
    }

    /**
     * Get a property for this object.
     *
     * @param string $name the name of the property to retrieve.
     * @throws Exception the property does not exist for this object.
     * @return string the associated property.
     */
    public function __get($name)
    {
        if ($name == 'last_sign_in_time') {
            return $this->lastActivityTime('sign_in');
        } elseif ($name == 'avatar') {
            // Use Gravatar as the user avatar
            $hash = md5(strtolower(trim( $this->email)));
            return 'https://www.gravatar.com/avatar/' . $hash . '?d=mm';
        } else {
            return parent::__get($name);
        }
    }

    /**
     * Get all activities for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->hasMany($classMapper->getClassMapping('activity'), 'user_id');
    }

    /**
     * Delete this user from the database, along with any linked roles and activities.
     *
     * @param bool $hardDelete Set to true to completely remove the user and all associated objects.
     * @return bool true if the deletion was successful, false otherwise.
     */
    public function delete($hardDelete = false)
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        if ($hardDelete) {
            // Remove all role associations
            $this->roles()->detach();

            // Remove all user activities
            $classMapper->staticMethod('activity', 'where', 'user_id', $this->id)->delete();

            // Remove all user tokens
            $classMapper->staticMethod('password_reset', 'where', 'user_id', $this->id)->delete();
            $classMapper->staticMethod('verification', 'where', 'user_id', $this->id)->delete();

            // TODO: remove any persistences

            // Delete the user
            $result = parent::forceDelete();
        } else {
            // Soft delete the user, leaving all associated records alone
            $result = parent::delete();
        }

        return $result;
    }

    /**
     * Determines whether a user exists, including checking soft-deleted records
     *
     * @deprecated since 4.1.7 This method conflicts with and overrides the Builder::exists() method.  Use Model::findUnique instead.
     * @param mixed $value
     * @param string $identifier
     * @param bool $checkDeleted set to true to include soft-deleted records
     * @return User|null
     */
    public static function exists($value, $identifier = 'user_name', $checkDeleted = true)
    {
        return static::findUnique($value, $identifier, $checkDeleted);
    }

    /**
     * Return a cache instance specific to that user
     *
     * @return Illuminate\Contracts\Cache\Store
     */
    public function getCache()
    {
        return static::$ci->cache->tags([static::$ci->config['cache.prefix'], '_u'.$this->id]);
    }

    /**
     * Allows you to get the full name of the user using `$user->full_name`
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Retrieve the cached permissions dictionary for this user.
     *
     * @return array
     */
    public function getCachedPermissions()
    {
        if (!isset($this->cachedPermissions)) {
            $this->reloadCachedPermissions();
        }

        return $this->cachedPermissions;
    }

    /**
     * Retrieve the cached permissions dictionary for this user.
     *
     * @return User
     */
    public function reloadCachedPermissions()
    {
        $this->cachedPermissions = $this->buildPermissionsDictionary();

        return $this;
    }

    /**
     * Get the amount of time, in seconds, that has elapsed since the last activity of a certain time for this user.
     *
     * @param string $type The type of activity to search for.
     * @return int
     */
    public function getSecondsSinceLastActivity($type)
    {
        $time = $this->lastActivityTime($type);
        $time = $time ? $time : '0000-00-00 00:00:00';
        $time = new Carbon($time);

        return $time->diffInSeconds();
    }

    /**
     * Return this user's group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsTo($classMapper->getClassMapping('group'), 'group_id');
    }

    /**
     * Returns whether or not this user is the master user.
     *
     * @return bool
     */
    public function isMaster()
    {
        $masterId = static::$ci->config['reserved_user_ids.master'];

        // Need to use loose comparison for now, because some DBs return `id` as a string
        return ($this->id == $masterId);
    }

    /**
     * Get the most recent activity for this user, based on the user's last_activity_id.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lastActivity()
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsTo($classMapper->getClassMapping('activity'), 'last_activity_id');
    }

    /**
     * Find the most recent activity for this user of a particular type.
     *
     * @param  string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function lastActivityOfType($type = null)
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        $query = $this->hasOne($classMapper->getClassMapping('activity'), 'user_id');

        if ($type) {
            $query = $query->where('type', $type);
        }

        return $query->latest('occurred_at');
    }

    /**
     * Get the most recent time for a specified activity type for this user.
     *
     * @param  string $type
     * @return string|null The last activity time, as a SQL formatted time (YYYY-MM-DD HH:MM:SS), or null if an activity of this type doesn't exist.
     */
    public function lastActivityTime($type)
    {
        $result = $this->activities()
            ->where('type', $type)
            ->max('occurred_at');
        return $result ? $result : null;
    }

    /**
     * Performs tasks to be done after this user has been successfully authenticated.
     *
     * By default, adds a new sign-in activity and updates any legacy hash.
     * @param mixed[] $params Optional array of parameters used for this event handler.
     * @todo Transition to Laravel Event dispatcher to handle this
     */
    public function onLogin($params = [])
    {
        // Add a sign in activity (time is automatically set by database)
        static::$ci->userActivityLogger->info("User {$this->user_name} signed in.", [
            'type' => 'sign_in'
        ]);

        // Update password if we had encountered an outdated hash
        $passwordType = Password::getHashType($this->password);

        if ($passwordType != 'modern') {
            if (!isset($params['password'])) {
                Debug::debug('Notice: Unhashed password must be supplied to update to modern password hashing.');
            } else {
                // Hash the user's password and update
                $passwordHash = Password::hash($params['password']);
                if ($passwordHash === null) {
                    Debug::debug('Notice: outdated password hash could not be updated because the new hashing algorithm is not supported.  Are you running PHP >= 5.3.7?');
                } else {
                    $this->password = $passwordHash;
                    Debug::debug('Notice: outdated password hash has been automatically updated to modern hashing.');
                }
            }
        }

        // Save changes
        $this->save();

        return $this;
    }

    /**
     * Performs tasks to be done after this user has been logged out.
     *
     * By default, adds a new sign-out activity.
     * @param mixed[] $params Optional array of parameters used for this event handler.
     * @todo Transition to Laravel Event dispatcher to handle this
     */
    public function onLogout($params = [])
    {
        static::$ci->userActivityLogger->info("User {$this->user_name} signed out.", [
            'type' => 'sign_out'
        ]);

        return $this;
    }

    /**
     * Get all password reset requests for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function passwordResets()
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->hasMany($classMapper->getClassMapping('password_reset'), 'user_id');
    }

    /**
     * Get all of the permissions this user has, via its roles.
     *
     * @return \UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyThrough
     */
    public function permissions()
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsToManyThrough(
            $classMapper->getClassMapping('permission'),
            $classMapper->getClassMapping('role'),
            'role_users',
            'user_id',
            'role_id',
            'permission_roles',
            'role_id',
            'permission_id'
        );
    }

    /**
     * Get all roles to which this user belongs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsToMany($classMapper->getClassMapping('role'), 'role_users', 'user_id', 'role_id')->withTimestamps();
    }

    /**
     * Query scope to get all users who have a specific role.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $roleId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForRole($query, $roleId)
    {
        return $query->join('role_users', function ($join) use ($roleId) {
            $join->on('role_users.user_id', 'users.id')
                 ->where('role_id', $roleId);
        });
    }

    /**
     * Joins the user's most recent activity directly, so we can do things like sort, search, paginate, etc.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinLastActivity($query)
    {
        $query = $query->select('users.*');

        $query = $query->leftJoin('activities', 'activities.id', '=', 'users.last_activity_id');

        return $query;
    }

    /**
     * Loads permissions for this user into a cached dictionary of slugs -> arrays of permissions,
     * so we don't need to keep requerying the DB for every call of checkAccess.
     *
     * @return array
     */
    protected function buildPermissionsDictionary()
    {
        $permissions = $this->permissions()->get();
        $cachedPermissions = [];

        foreach ($permissions as $permission) {
            $cachedPermissions[$permission->slug][] = $permission;
        }

        return $cachedPermissions;
    }
}
