<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery\Migrations;

use Illuminate\Database\Schema\MySqlBuilder;

/**
 * MigrationInterface interface.
 *
 * @extends Debug
 * @author Alex Weissman (https://alexanderweissman.com)
 */
interface MigrationInterface
{
    public function up(MySqlBuilder $schema);
    public function down(MySqlBuilder $schema);
}