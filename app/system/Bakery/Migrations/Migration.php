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
     * __construct function.
     *
     * @access public
     * @param Illuminate\Database\Schema\Builder $schema
     * @return void
     */
    public function __construct(Builder $schema)
    {
        $this->schema = $schema;
    }

    abstract public function up();
    abstract public function down();
}