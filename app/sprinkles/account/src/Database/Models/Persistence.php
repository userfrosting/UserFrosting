<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models;

use Carbon\Carbon;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Persistence db Model.
 *
 * Represents the persistences table.
 * @author Louis Charette
 * @property string user_id
 * @property string token
 * @property string persistent_token
 * @property string expires_at
 */
class Persistence extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'persistences';

    protected $fillable = [
        'user_id',
        'token',
        'persistent_token',
        'expires_at'
    ];

    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = true;

    /**
     * Relation with the user table
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->hasOne(
            $classMapper->getClassMapping('user')
        );
    }

    /**
     * Scope a query to only include not expired entries
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', Carbon::now());
    }
}
