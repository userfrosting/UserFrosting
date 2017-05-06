<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Model\UFModel;

/**
 * Version Class
 *
 * Represents a Version object as stored in the database.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @property string sprinkle
 * @property string version
 *
 */
class Version extends UFModel {

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "version";

    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = true;

    /**
     * @var array List of fields that can be edited by this model
     */
    protected $fillable = [
        "sprinkle",
        "version"
    ];

    /**
     * scopeForSprinkle function.
     *
     * @access protected
     * @param mixed $query
     * @param string $sprinkleName
     * @return void
     */
    protected function scopeForSprinkle($query, $sprinkleName)
    {
        return $query->where('sprinkle', $sprinkleName);
    }
}
