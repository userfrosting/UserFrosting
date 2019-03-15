<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database;

use Illuminate\Database\Schema\Builder;

/**
 * Abstract Migration class.
 *
 * @author Louis Charette
 */
abstract class Migration implements MigrationInterface
{
    /**
     * @var \Illuminate\Database\Schema\Builder $schema
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
     * Create a new migration instance
     *
     * @param \Illuminate\Database\Schema\Builder|null $schema
     */
    public function __construct(Builder $schema = null)
    {
        $this->schema = $schema;
    }
}
