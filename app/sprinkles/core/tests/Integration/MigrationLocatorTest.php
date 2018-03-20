<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Integration;

use \SplFileInfo;
use Mockery as m;
use UserFrosting\Tests\TestCase;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocator;
use Illuminate\Filesystem\Filesystem;

class MigrationLocatorTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     *    Make sure migrations can be returned for all sprinkles
     */
    public function testGetMigrations()
    {
        // Simulate the SprinkleManager
        $sprinkleManager = m::mock('UserFrosting\System\Sprinkle\SprinkleManager');

        // The locator will ask sprinkleManager for `getSprinkleNames`. When it does, we'll return this fake data
        $sprinkleManager->shouldReceive('getSprinkleNames')->once()->andReturn([
            'core',
            'Account'
        ]);

        // Simulate the Filesystem.
        $filesystem = m::mock('Illuminate\Filesystem\Filesystem');

        // When `MigrationLocator` will ask the filesystem for `glob` and `core` sprinkle,
        // filesystem will return fake test path.
        $filesystem->shouldReceive('exists')->with(\UserFrosting\ROOT_DIR . "/app/sprinkles/core/src/Database/Migrations")->andReturn(true);
        $filesystem->shouldReceive('allFiles')->with(\UserFrosting\ROOT_DIR . "/app/sprinkles/core/src/Database/Migrations")->once()->andReturn([
            new SplFileInfo(\UserFrosting\ROOT_DIR . '/app/sprinkles/core/src/Database/Migrations/one/CreateUsersTable.php'),
            new SplFileInfo(\UserFrosting\ROOT_DIR . '/app/sprinkles/core/src/Database/Migrations/one/CreatePasswordResetsTable.php'),
            new SplFileInfo(\UserFrosting\ROOT_DIR . '/app/sprinkles/core/src/Database/Migrations/two/CreateFlightsTable.php'),
            new SplFileInfo(\UserFrosting\ROOT_DIR . '/app/sprinkles/core/src/Database/Migrations/CreateMainTable.php')
        ]);

        // When `MigrationLocator` will also ask the filesystem the same thing, but for the `account` sprinkle
        $filesystem->shouldReceive('exists')->with(\UserFrosting\ROOT_DIR . "/app/sprinkles/Account/src/Database/Migrations")->andReturn(true);
        $filesystem->shouldReceive('allFiles')->with(\UserFrosting\ROOT_DIR . "/app/sprinkles/Account/src/Database/Migrations")->once()->andReturn([
            new SplFileInfo(\UserFrosting\ROOT_DIR . '/app/sprinkles/Account/src/Database/Migrations/one/CreateUsersTable.php'),
            new SplFileInfo(\UserFrosting\ROOT_DIR . '/app/sprinkles/Account/src/Database/Migrations/one/CreatePasswordResetsTable.php'),
            new SplFileInfo(\UserFrosting\ROOT_DIR . '/app/sprinkles/Account/src/Database/Migrations/two/CreateFlightsTable.php'),
            new SplFileInfo(\UserFrosting\ROOT_DIR . '/app/sprinkles/Account/src/Database/Migrations/CreateMainTable.php')
        ]);

        // Create a new MigrationLocator instance with our simulated SprinkleManager and filesystem
        // and ask to find core sprinkle migration files
        $locator = new MigrationLocator($sprinkleManager, $filesystem);
        $results = $locator->getMigrations();

        // The `getMigrationForSprinkle` method should return this
        $expected = [
            "\\UserFrosting\\Sprinkle\\Account\\Database\\Migrations\\one\\CreateUsersTable",
            "\\UserFrosting\\Sprinkle\\Account\\Database\\Migrations\\one\\CreatePasswordResetsTable",
            "\\UserFrosting\\Sprinkle\\Account\\Database\\Migrations\\two\\CreateFlightsTable",
            "\\UserFrosting\\Sprinkle\\Account\\Database\\Migrations\\CreateMainTable",
            "\\UserFrosting\\Sprinkle\\Core\\Database\\Migrations\\one\\CreateUsersTable",
            "\\UserFrosting\\Sprinkle\\Core\\Database\\Migrations\\one\\CreatePasswordResetsTable",
            "\\UserFrosting\\Sprinkle\\Core\\Database\\Migrations\\two\\CreateFlightsTable",
            "\\UserFrosting\\Sprinkle\\Core\\Database\\Migrations\\CreateMainTable"
        ];

        // Test results match expectations
        $this->assertEquals($expected, $results);
    }

    /**
     *    Make sure migrations can be returned for a specific sprinkle
     */
    public function testGetMigrationsForSprinkle()
    {
        // Simulate the SprinkleManager
        $sprinkleManager = m::mock('UserFrosting\System\Sprinkle\SprinkleManager');

        // Simulate the Filesystem.
        $filesystem = m::mock('Illuminate\Filesystem\Filesystem');

        // When `MigrationLocator` will ask the filesystem for `glob`, which it should do only once,
        // filesystem will return fake test path.
        $filesystem->shouldReceive('exists')->with(\UserFrosting\ROOT_DIR . "/app/sprinkles/core/src/Database/Migrations")->andReturn(true);
        $filesystem->shouldReceive('allFiles')->with(\UserFrosting\ROOT_DIR . "/app/sprinkles/core/src/Database/Migrations")->once()->andReturn([
            new SplFileInfo(\UserFrosting\ROOT_DIR . '/app/sprinkles/core/src/Database/Migrations/one/CreateUsersTable.php'),
            new SplFileInfo(\UserFrosting\ROOT_DIR . '/app/sprinkles/core/src/Database/Migrations/one/CreatePasswordResetsTable.php'),
            new SplFileInfo(\UserFrosting\ROOT_DIR . '/app/sprinkles/core/src/Database/Migrations/two/CreateFlightsTable.php'),
            new SplFileInfo(\UserFrosting\ROOT_DIR . '/app/sprinkles/core/src/Database/Migrations/CreateMainTable.php')
        ]);

        // Create a new MigrationLocator instance with our simulated SprinkleManager and filesystem
        // and ask to find core sprinkle migration files
        $locator = new MigrationLocator($sprinkleManager, $filesystem);
        $results = $locator->getMigrationsForSprinkle('core');

        // The `getMigrationForSprinkle` method should return this
        $expected = [
            "\\UserFrosting\\Sprinkle\\Core\\Database\\Migrations\\one\\CreateUsersTable",
            "\\UserFrosting\\Sprinkle\\Core\\Database\\Migrations\\one\\CreatePasswordResetsTable",
            "\\UserFrosting\\Sprinkle\\Core\\Database\\Migrations\\two\\CreateFlightsTable",
            "\\UserFrosting\\Sprinkle\\Core\\Database\\Migrations\\CreateMainTable"
        ];

        // Test results match expectations
        $this->assertEquals($expected, $results);
    }

    /**
     *    Make sure no error is thrown if the Migration dir doesn't exist
     */
    public function testGetMigrationsForSprinkleWithNoMigrationDir()
    {
        // Simulate the SprinkleManager
        $sprinkleManager = m::mock('UserFrosting\System\Sprinkle\SprinkleManager');

        // Simulate the Filesystem.
        $filesystem = m::mock('Illuminate\Filesystem\Filesystem');

        // When `MigrationLocator` will ask the filesystem for `glob`, which it should do only once,
        // filesystem will return fake test path.
        $filesystem->shouldReceive('exists')->with(\UserFrosting\ROOT_DIR . "/app/sprinkles/core/src/Database/Migrations")->andReturn(false);
        $filesystem->shouldNotReceive('allFiles');

        // Create a new MigrationLocator instance with our simulated SprinkleManager and filesystem
        // and ask to find core sprinkle migration files
        $locator = new MigrationLocator($sprinkleManager, $filesystem);
        $results = $locator->getMigrationsForSprinkle('core');

        // Test results match expectations
        $this->assertEquals([], $results);
    }

    /**
     *    Test MigratonLocator against the real thing, no Mockery
     */
    function testActualInstance()
    {
        // Get sprinkle manager and make sure `core` is returned
        $sprinkleManager = $this->ci->sprinkleManager;
        $this->assertContains('core', $sprinkleManager->getSprinkleNames());

        // Create a new MigrationLocator instance with our real SprinkleManager and filesystem
        // and ask to find core sprinkle migration files
        $locator = new MigrationLocator($sprinkleManager, new Filesystem);
        $results = $locator->getMigrationsForSprinkle('core');

        // Test results match expectations
        $this->assertContains('\UserFrosting\Sprinkle\Core\Database\Migrations\v400\SessionsTable', $results);
    }
}
