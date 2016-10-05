<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Model;

use UserFrosting\Sprinkle\Core\Model\UFModel;

/**
 * Throttle Class
 *
 * Represents a throttleable request from a user agent.
 * @author Alex Weissman (https://alexanderweissman.com)
 * @property string type
 * @property string ip
 * @property string request_data
 */
class Throttle extends UFModel
{    
    /**
     * @var string The name of the table for the current model.
     */ 
    protected $table = "throttles";

    protected $fillable = [
        "type",
        "ip",
        "request_data"
    ];

    /**
     * @var bool Enable timestamps for Throttles.
     */ 
    public $timestamps = true;    
}
