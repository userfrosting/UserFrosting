<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Integration;

use Mockery as m;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Grammars\Grammar;
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocator;
use UserFrosting\Tests\TestCase;

/**
 *    Tests for the Migrator Class
 *
 *    Theses tests make sure the Migrator works correctly, without validating
 *    agaist a simulated database. Those tests are performed by `DatabaseMigratorIntegrationTest`
 *
 *    @author Louis Charette
 */
class DatabaseMigratorTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

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
     * @var Connection
     */
    protected $connection;

    /**
     * Setup base mock and migrator instance.
     *
     * @return void
     */
    public function setUp()
    {
        // Boot parent TestCase
        parent::setUp();

        // Create mock objects
        $this->schema = m::mock(Builder::class);
        $this->repository = m::mock(DatabaseMigrationRepository::class);
        $this->locator = m::mock(MigrationLocator::class);
        $capsule = m::mock(Capsule::class);
        $this->connection = m::mock(Connection::class);

        // Set global expections for $capule and $connection
        $capsule->shouldReceive('getConnection')->andReturn($this->connection);
        $this->connection->shouldReceive('getSchemaBuilder')->andReturn($this->schema);

        // Setup the migrator instance
        $this->migrator = new Migrator($capsule, $this->repository, $this->locator);
    }

    /**
     *    Basic test to make sure the base method syntaxt is ok
     */
    public function testMigratorUpWithNoMigrations()
    {
        // Locator will be asked to return the avaialble migrations
        $this->locator->shouldReceive('getMigrations')->once()->andReturn([]);

        // Repository will be asked to return the ran migrations
        $this->repository->shouldReceive('getRan')->once()->andReturn([]);

        $migrations = $this->migrator->run();
        $this->assertEmpty($migrations);
    }

    /**
     *    Basic test where all avaialble migrations are pending and fulfillable
     */
    public function testMigratorUpWithOnlyPendingMigrations()
    {
        // The migrations set
        $testMigrations = [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable'
        ];

        // When running up, Locator will return all 3 migration classes
        $this->locator->shouldReceive('getMigrations')->andReturn($testMigrations);

        // Repository will be asked to return the ran migrations, the next batch number and will log 3 new migrations
        $this->repository->shouldReceive('getRan')->andReturn([]);
        $this->repository->shouldReceive('getNextBatchNumber')->andReturn(1);
        $this->repository->shouldReceive('log')->times(3)->andReturn(null);

        // SchemaBuilder will create all 3 tables
        $this->schema->shouldReceive('create')->times(3)->andReturn(null);

        // Connection will be asked for the SchemaGrammar
        $grammar = m::mock(Grammar::class);
        $this->connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
        $grammar->shouldReceive('supportsSchemaTransactions')->andReturn(false);

        // Run migrations up
        $migrations = $this->migrator->run();

        // All classes should have been migrated
        $this->assertEquals($testMigrations, $migrations);
    }

    /**
     *    Test where one of the avaialble migrations is already installed
     */
    public function testMigratorUpWithOneInstalledMigrations()
    {
        // When running up, Locator will return all 3 migration classes
        $this->locator->shouldReceive('getMigrations')->andReturn([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable'
        ]);

        // Repository will be asked to return the ran migrations (one), the next batch number and will log 2 new migrations
        $this->repository->shouldReceive('getRan')->andReturn([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable'
        ]);
        $this->repository->shouldReceive('getNextBatchNumber')->andReturn(2);
        $this->repository->shouldReceive('log')->times(2)->andReturn(null);

        // SchemaBuilder will only create 2 tables
        $this->schema->shouldReceive('create')->times(2)->andReturn(null);

        // Connection will be asked for the SchemaGrammar
        $grammar = m::mock(Grammar::class);
        $this->connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
        $grammar->shouldReceive('supportsSchemaTransactions')->andReturn(false);

        // Run migrations up
        $migrations = $this->migrator->run();

        // The migration already ran shoudn't be in the pending ones
        $this->assertEquals([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable'
        ], $migrations);
    }

    /**
     *    Test where all avaialble migrations have been ran
     */
    public function testMigratorUpWithNoPendingMigrations()
    {
        // The migrations set
        $testMigrations = [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable'
        ];

        // When running up, Locator will return all 3 migration classes
        $this->locator->shouldReceive('getMigrations')->andReturn($testMigrations);

        // Repository will be asked to return the ran migrations (one), the next batch number and will log 2 new migrations
        $this->repository->shouldReceive('getRan')->andReturn($testMigrations);
        $this->repository->shouldNotReceive('getNextBatchNumber');
        $this->repository->shouldNotReceive('log');

        // SchemaBuilder will only create 2 tables
        $this->schema->shouldNotReceive('create');

        // Run migrations up
        $migrations = $this->migrator->run();

        // The migration already ran shoudn't be in the pending ones
        $this->assertEquals([], $migrations);
    }

    /**
     *    Test where one of the available migrations is missing a dependency
     */
    //!TODO

    /**
     *    Test rolling back where no migrations have been ran
     */
    public function testMigratorRollbackWithNoInstalledMigrations()
    {
        // Repository will be asked to return the last batch of ran migrations
        $this->repository->shouldReceive('getLast')->andReturn([]);

        // Run migrations up
        $migrations = $this->migrator->rollback();

        // The migration already ran shoudn't be in the pending ones
        $this->assertEquals([], $migrations);
    }

    /**
     *    Test rolling back all installed migrations
     */
    public function testMigratorRollbackAllInstalledMigrations()
    {
        // The migrations set
        $testMigrations = [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable'
        ];

        // When running up, Locator will return all 3 migration classes
        $this->locator->shouldReceive('getMigrations')->once()->andReturn($testMigrations);

        // Repository will be asked to return the ran migrations (one), the next batch number and will log 2 new migrations
        $this->repository->shouldReceive('getLast')->once()->andReturn($testMigrations);
        $this->repository->shouldReceive('delete')->times(3)->andReturn([]);

        // SchemaBuilder will only create 2 tables
        $this->schema->shouldReceive('dropIfExists')->times(3)->andReturn([]);

        // Connection will be asked for the SchemaGrammar
        $grammar = m::mock(Grammar::class);
        $this->connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
        $grammar->shouldReceive('supportsSchemaTransactions')->andReturn(false);

        // Run migrations up
        $migrations = $this->migrator->rollback();

        // The migration already ran shoudn't be in the pending ones
        $this->assertEquals($testMigrations, $migrations);
    }

    /**
     *    Test where one of the installed migration is not in the available migration classes
     */
    public function testMigratorRollbackAllInstalledMigrationsWithOneMissing()
    {
        // When running up, Locator will return all 3 migration classes
        $this->locator->shouldReceive('getMigrations')->once()->andReturn([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable'
        ]);

        // Repository will be asked to return the ran migrations (one), the next batch number and will log 2 new migrations
        $this->repository->shouldReceive('getLast')->once()->andReturn([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable'
        ]);
        $this->repository->shouldReceive('delete')->times(1)->andReturn([]);

        // SchemaBuilder will only create 2 tables
        $this->schema->shouldReceive('dropIfExists')->times(1)->andReturn([]);

        // Connection will be asked for the SchemaGrammar
        $grammar = m::mock(Grammar::class);
        $this->connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
        $grammar->shouldReceive('supportsSchemaTransactions')->andReturn(false);

        // Run migrations up
        $migrations = $this->migrator->rollback();

        // The migration already ran shoudn't be in the pending ones
        $this->assertEquals([
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable'
        ], $migrations);
    }

    /**
     *    Test where one of the installed migration is not in the available migration classes
     */
    public function testMigratorResetAllInstalledMigrations()
    {
        // The migrations set
        $testMigrations = [
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreateUsersTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\one\\CreatePasswordResetsTable',
            '\\UserFrosting\\Tests\\Integration\\Migrations\\two\\CreateFlightsTable'
        ];

        // When running up, Locator will return all 3 migration classes
        $this->locator->shouldReceive('getMigrations')->once()->andReturn($testMigrations);

        // Repository will be asked to return the ran migrations (one), the next batch number and will log 2 new migrations
        $this->repository->shouldReceive('getRan')->once()->andReturn($testMigrations);
        $this->repository->shouldReceive('delete')->times(3)->andReturn([]);

        // SchemaBuilder will only create 2 tables
        $this->schema->shouldReceive('dropIfExists')->times(3)->andReturn([]);

        // Connection will be asked for the SchemaGrammar
        $grammar = m::mock(Grammar::class);
        $this->connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
        $grammar->shouldReceive('supportsSchemaTransactions')->andReturn(false);

        // Run migrations up
        $migrations = $this->migrator->reset();

        // The migration already ran shoudn't be in the pending ones
        $this->assertEquals(array_reverse($testMigrations), $migrations);
    }
}
