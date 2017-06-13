<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Illuminate\Database\Schema\Builder;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Abstract Migration class.
 *
 * @abstract
 * @author Alex Weissman (https://alexanderweissman.com)
 */
abstract class Migration
{
    /**
     * @var Illuminate\Database\Schema\Builder $schema
     */
    protected $schema;

    /**
     * @var @Composer\IO\IOInterface
     */
    protected $io;

    /**
     * List of dependencies for this migration.
     * Should return an array of class required to be run before this migration
     */
    public $dependencies = [];

    /**
     * __construct function.
     *
     * @access public
     * @param Illuminate\Database\Schema\Builder $schema
     * @return void
     */
    public function __construct(Builder $schema, SymfonyStyle $io)
    {
        $this->schema = $schema;
        $this->io = $io;
    }

    /**
     * Method to apply changes to the database
     */
    public function up() {}

    /**
     * Method to revert changes applied by the `up` method
     */
    public function down() {}

    /**
     * Method to seed new information to the database
     */
    public function seed() {}
}
