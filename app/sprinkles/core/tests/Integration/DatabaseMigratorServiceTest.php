<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Integration;

use UserFrosting\Tests\TestCase;

/**
 *    Tests for the Migrator Service
 *
 *    @author Louis Charette
 */
class DatabaseMigratorServiceTest extends TestCase
{
    public function testMigratorService()
    {
        $this->assertInstanceOf('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator', $this->ci->migrator);
    }
}
