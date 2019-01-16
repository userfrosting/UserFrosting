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
 * Verification Class
 *
 * Represents a pending email verification for a new user account.
 * @author Alex Weissman (https://alexanderweissman.com)
 * @property int user_id
 * @property hash token
 * @property bool completed
 * @property datetime expires_at
 * @property datetime completed_at
 */
class Verification extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'verifications';

    protected $fillable = [
        'user_id',
        'hash',
        'completed',
        'expires_at',
        'completed_at'
    ];

    /**
     * @var bool Enable timestamps for Verifications.
     */
    public $timestamps = true;

    /**
     * @var string Stores the raw (unhashed) token when created, so that it can be emailed out to the user.  NOT persisted.
     */
    protected $token;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param  string $value
     * @return self
     */
    public function setToken($value)
    {
        $this->token = $value;

        return $this;
    }

    /**
     * Get the user associated with this verification request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsTo($classMapper->getClassMapping('user'), 'user_id');
    }
}
