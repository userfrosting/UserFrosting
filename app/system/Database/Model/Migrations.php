<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Database\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;

/**
 * Migration Model
 *
 * Represents the model for the `migrations` table
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @property string sprinkle
 * @property string version
 */
class Migrations extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "migrations";

    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = true;

    /**
     * @var array List of fields that can be edited by this model
     */
    protected $fillable = [
        "sprinkle",
        "migration",
        "batch"
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
