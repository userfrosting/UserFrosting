<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery\Migrations;

use Illuminate\Database\Schema\Builder;
use Composer\IO\IOInterface;

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
    public function __construct(Builder $schema, IOInterface $io)
    {
        $this->schema = $schema;
        $this->io = $io;
   }

    abstract public function up();
    abstract public function down();
}