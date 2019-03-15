<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration;

use Mockery as m;
use UserFrosting\Tests\TestCase;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocator;
use UserFrosting\UniformResourceLocator\Resource;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceLocation;
use UserFrosting\UniformResourceLocator\ResourceStream;

class MigrationLocatorTest extends TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    /**
     * Make sure no error is thrown if the Migration dir doesn't exist
     */
    public function testGetMigrationsWithNoMigrationDir()
    {
        // Setup mock locator
        $resourceLocator = m::mock(ResourceLocator::class);

        // Setup mock stream
        $resourceStream = m::mock(ResourceStream::class);
        $resourceStream->shouldReceive('getPath')->andReturn('src/Database/Migrations');

        // Setup mock locations
        $resourceCoreLocation = m::mock(ResourceLocation::class);
        $resourceCoreLocation->shouldReceive('getName')->andReturn('Core');
        $resourceCoreLocation->shouldReceive('getPath')->andReturn('app/sprinkles/Core');
        $resourceAccountLocation = m::mock(ResourceLocation::class);
        $resourceAccountLocation->shouldReceive('getName')->andReturn('account');
        $resourceAccountLocation->shouldReceive('getPath')->andReturn('app/sprinkles/Account');

        // When `MigrationLocator` will ask the resource locator to `listResources`, we simulate returning no Resources
        $resourceLocator->shouldReceive('listResources')->once()->andReturn([]);
        $locator = new MigrationLocator($resourceLocator);
        $results = $locator->getMigrations();

        // Test results match expectations
        $this->assertCount(0, $results);
        $this->assertEquals([], $results);
    }

    /**
     * Make sure migrations can be returned for all sprinkles
     */
    public function testGetMigrations()
    {
        // Setup mock locator
        $resourceLocator = m::mock(ResourceLocator::class);

        // Setup mock stream
        $resourceStream = m::mock(ResourceStream::class);
        $resourceStream->shouldReceive('getPath')->andReturn('src/Database/Migrations');

        // Setup mock locations
        $resourceCoreLocation = m::mock(ResourceLocation::class);
        $resourceCoreLocation->shouldReceive('getName')->andReturn('Core');
        $resourceCoreLocation->shouldReceive('getPath')->andReturn('app/sprinkles/Core');

        $resourceAccountLocation = m::mock(ResourceLocation::class);
        $resourceAccountLocation->shouldReceive('getName')->andReturn('account');
        $resourceAccountLocation->shouldReceive('getPath')->andReturn('app/sprinkles/Account');

        // When `MigrationLocator` will ask the resource locator to `listResources`, we simulate returning Resources
        $resourceLocator->shouldReceive('listResources')->once()->andReturn([
            new Resource($resourceStream, $resourceCoreLocation, 'one/CreateUsersTable.php'),
            new Resource($resourceStream, $resourceCoreLocation, 'one/CreatePasswordResetsTable.php'),
            new Resource($resourceStream, $resourceCoreLocation, 'two/CreateFlightsTable.php'),
            new Resource($resourceStream, $resourceCoreLocation, 'CreateMainTable.php'),
            new Resource($resourceStream, $resourceAccountLocation, 'one/CreateUsersTable.php'),
            new Resource($resourceStream, $resourceAccountLocation, 'one/CreatePasswordResetsTable.php'),
            new Resource($resourceStream, $resourceAccountLocation, 'two/CreateFlightsTable.php'),
            new Resource($resourceStream, $resourceAccountLocation, 'CreateMainTable.php')
        ]);

        // Create a new MigrationLocator instance with our simulated ResourceLocation
        $locator = new MigrationLocator($resourceLocator);
        $results = $locator->getMigrations();

        // The `getMigration` method should return this
        $expected = [
            '\\UserFrosting\\Sprinkle\\Core\\Database\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Sprinkle\\Core\\Database\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Sprinkle\\Core\\Database\\Migrations\\two\\CreateFlightsTable',
            '\\UserFrosting\\Sprinkle\\Core\\Database\\Migrations\\CreateMainTable',
            '\\UserFrosting\\Sprinkle\\Account\\Database\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Sprinkle\\Account\\Database\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Sprinkle\\Account\\Database\\Migrations\\two\\CreateFlightsTable',
            '\\UserFrosting\\Sprinkle\\Account\\Database\\Migrations\\CreateMainTable'
        ];

        // Test results match expectations
        $this->assertCount(8, $results);
        $this->assertEquals($expected, $results);

        return $locator;
    }

    /**
     *    Test MigratonLocator against the real thing, no Mockery
     */
    public function testActualInstance()
    {
        // Get sprinkle manager and make sure `core` is returned
        $this->assertContains('core', $this->ci->sprinkleManager->getSprinkleNames());

        // Create a new MigrationLocator instance with our real SprinkleManager and filesystem
        // and ask to find core sprinkle migration files
        $locator = new MigrationLocator($this->ci->locator);
        $results = $locator->getMigrations();

        // We'll need to convert the array returned by `getMigrations` to a
        // collection to make it easier to search
        $this->assertInternalType('array', $results);
        $this->assertContains('\UserFrosting\Sprinkle\Core\Database\Migrations\v400\SessionsTable', $results);
    }
}
