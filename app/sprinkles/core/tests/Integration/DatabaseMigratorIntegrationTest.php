<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Integration;

use Illuminate\Filesystem\Filesystem;
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocator;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Util\BadClassNameException;
use UserFrosting\Tests\TestCase;

class DatabaseMigratorIntegrationTest extends TestCase
{
    /**
     * @var string The db connection to use for the test.
     */
    protected $connection = 'test_integration';

    /**
     * @var string The migration table name
     */
    protected $migrationTable = 'migrations';

    /**
     * @var \Illuminate\Database\Schema\Builder
     */
    protected $schema;

    /**
     * @var Migrator The migrator instance.
     */
    protected $migrator;

    /**
     * @var MigrationLocator The migration locator instance.
     */
    protected $locator;

    /**
     * @var DatabaseMigrationRepository The migration repository instance.
     */
    protected $repository;

    /**
     * Setup migration instances used for all tests
     */
    public function setUp()
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        // Get the repository and locator instances
        $this->repository = new DatabaseMigrationRepository($this->ci->db, $this->migrationTable);
        $this->locator = new MigrationLocatorStub($this->ci->sprinkleManager, new Filesystem);

        // Get the migrator instance and setup right connection
        $this->migrator = new Migrator($this->ci->db, $this->repository, $this->locator);
        $this->migrator->setConnection($this->connection);

        // Get schema Builder
        $this->schema = $this->migrator->getSchemaBuilder();

        if (!$this->repository->repositoryExists()) {
            $this->repository->createRepository();
        }
    }

    public function testMigrationRepositoryCreated()
    {
        $this->assertTrue($this->schema->hasTable($this->migrationTable));
    }

    public function testBasicMigration()
    {
        $ran = $this->migrator->run();

        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));

        $this->assertEquals($this->locator->getMigrations(), $ran);
    }

    public function testRepository()
    {
        $ran = $this->migrator->run();

        // Theses assertions makes sure the repository and the migration returns the same format
        // N.B.: getLast return the migrations in reverse order (last ran first)
        $this->assertEquals($this->locator->getMigrations(), $ran);
        $this->assertEquals(array_reverse($this->locator->getMigrations()), $this->repository->getLast());
        $this->assertEquals($this->locator->getMigrations(), $this->repository->getRan());
    }

    public function testMigrationsCanBeRolledBack()
    {
        // Run up
        $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));

        $rolledBack = $this->migrator->rollback();
        $this->assertFalse($this->schema->hasTable('users'));
        $this->assertFalse($this->schema->hasTable('password_resets'));

        // Make sure the data returned from migrator is accurate.
        // N.B.: The order returned by the rollback method is ordered by which
        // migration was rollbacked first (reversed from the order they where ran up)
        $this->assertEquals(array_reverse($this->locator->getMigrations()), $rolledBack);
    }

    public function testMigrationsCanBeReset()
    {
        // Run up
        $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));

        $rolledBack = $this->migrator->reset();
        $this->assertFalse($this->schema->hasTable('users'));
        $this->assertFalse($this->schema->hasTable('password_resets'));

        // Make sure the data returned from migrator is accurate.
        $this->assertEquals(array_reverse($this->locator->getMigrations()), $rolledBack);
    }

    public function testNoErrorIsThrownWhenNoOutstandingMigrationsExist()
    {
        $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));
        $this->migrator->run();
    }

    public function testNoErrorIsThrownWhenNothingToRollback()
    {
        $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));
        $this->migrator->rollback();
        $this->assertFalse($this->schema->hasTable('users'));
        $this->assertFalse($this->schema->hasTable('password_resets'));
        $this->migrator->rollback();
    }

    public function testPretendUp()
    {
        $result = $this->migrator->run(['pretend' => true]);
        $notes = $this->migrator->getNotes();
        $this->assertFalse($this->schema->hasTable('users'));
        $this->assertFalse($this->schema->hasTable('password_resets'));
        $this->assertNotEquals([], $notes);
    }

    public function testPretendRollback()
    {
        // Run up as usual
        $result = $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));

        $rolledBack = $this->migrator->rollback(['pretend' => true]);
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));
        $this->assertEquals(array_reverse($this->locator->getMigrations()), $rolledBack);
    }

    public function testChangeRepositoryAndDeprecatedClass()
    {
        // Change the repository so we can test with the DeprecatedMigrationLocatorStub
        $locator = new DeprecatedMigrationLocatorStub($this->ci->sprinkleManager, new Filesystem);
        $this->migrator->setLocator($locator);

        // Run up
        $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('deprecated_table'));

        // Rollback
        $this->migrator->rollback();
        $this->assertFalse($this->schema->hasTable('deprecated_table'));
    }

    public function testWithInvalidClass()
    {
        // Change the repository so we can test with the InvalidMigrationLocatorStub
        $locator = new InvalidMigrationLocatorStub($this->ci->sprinkleManager, new Filesystem);
        $this->migrator->setLocator($locator);

        // Expect a `BadClassNameException` exception
        $this->expectException(BadClassNameException::class);

        // Run up
        $this->migrator->run();
    }

    public function testDependableMigrations()
    {
        // Change the repository so we can test with the DependableMigrationLocatorStub
        $locator = new DependableMigrationLocatorStub($this->ci->sprinkleManager, new Filesystem);
        $this->migrator->setLocator($locator);

        // Run up
        $migrated = $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));
        $this->assertTrue($this->schema->hasTable('flights'));

        // Note here the `two` migration has been placed at the bottom even if
        // it was supposed to be migrated first from the order the locator
        // returned them. This is because `two` migration depends on `one` migrations
        // We only check the last one, we don't care about the order the first two are since they are not dependendent on eachother
        $this->assertEquals('\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable', $migrated[2]);
    }

    public function testDependableMigrationsWithInstalled()
    {
        // Run the `one` migrations
        $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('password_resets'));

        // Change the repository so we can run up the `two` migrations
        $locator = new FlightsTableMigrationLocatorStub($this->ci->sprinkleManager, new Filesystem);
        $this->migrator->setLocator($locator);

        // Run up again
        $migrated = $this->migrator->run();
        $this->assertTrue($this->schema->hasTable('flights'));

        // Only the `CreateFlightsTable` migration should be ran
        $this->assertEquals([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable'
        ], $migrated);
    }

    public function testUnfulfillableMigrations()
    {
        // Change the repository so we can test with the DeprecatedStub
        $locator = new UnfulfillableMigrationLocatorStub($this->ci->sprinkleManager, new Filesystem);
        $this->migrator->setLocator($locator);

        // Should have an exception for unfulfilled migrations
        $this->expectException(\Exception::class);
        $migrated = $this->migrator->run();
    }
}

class MigrationLocatorStub extends MigrationLocator {
    public function getMigrations()
    {
        return [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable'
        ];
    }
}

class FlightsTableMigrationLocatorStub extends MigrationLocator {
    public function getMigrations()
    {
        return [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable'
        ];
    }
}

/**
 *    This stub contain migration which file doesn't exists
 */
class InvalidMigrationLocatorStub extends MigrationLocator {
    public function getMigrations()
    {
        return [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\Foo'
        ];
    }
}

/**
 *    This stub contain migration which order they need to be run is different
 *    than the order the file are returned because of dependencies management.
 *    The `two` migration should be run last since it depends on the other two
 */
class DependableMigrationLocatorStub extends MigrationLocator {
    public function getMigrations()
    {
        return [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable'
        ];
    }
}

/**
 *    This stub contain migration which order they need to be run is different
 *    than the order the file are returned because of dependencies management
 */
class UnfulfillableMigrationLocatorStub extends MigrationLocator {
    public function getMigrations()
    {
        return [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\UnfulfillableTable'
        ];
    }
}

/**
 *    This stub contain migration which order they need to be run is different
 *    than the order the file are returned because of dependencies management
 */
class DeprecatedMigrationLocatorStub extends MigrationLocator {
    public function getMigrations()
    {
        return [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\DeprecatedClassTable'
        ];
    }
}
