<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database;

use Illuminate\Database\Schema\Builder;

/**
 * Abstract Migration class.
 *
 * @abstract
 * @author Louis Charette
 */
abstract class Migration
{
    /**
     * @var Illuminate\Database\Schema\Builder $schema
     */
    protected $schema;

    /**
     * List of dependencies for this migration.
     * Should return an array of class required to be run before this migration
     *
     * N.B.: Uncomment the next line when the static $dependencie deprecation is removed
     */
    //public static $dependencies = [];

    /**
     * __construct function.
     *
     * @access public
     * @param Illuminate\Database\Schema\Builder $schema
     * @return void
     */
    public function __construct(Builder $schema = null)
    {
        $this->schema = $schema;
    }

    /**
     * Method to apply changes to the database
     */
    public function up() {}

    /**
     * Method to revert changes applied by the `up` method
     */
    public function down() {}
}
