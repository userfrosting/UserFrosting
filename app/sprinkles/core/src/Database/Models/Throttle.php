<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models;

/**
 * Throttle Class
 *
 * Represents a throttleable request from a user agent.
 * @author Alex Weissman (https://alexanderweissman.com)
 * @property string type
 * @property string ip
 * @property string request_data
 */
class Throttle extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'throttles';

    protected $fillable = [
        'type',
        'ip',
        'request_data'
    ];

    /**
     * @var bool Enable timestamps for Throttles.
     */
    public $timestamps = true;
}
