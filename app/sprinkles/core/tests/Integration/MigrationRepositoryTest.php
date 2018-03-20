<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Integration;

use Mockery as m;
use Illuminate\Support\Collection;
use UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
use UserFrosting\Tests\TestCase;

class MigrationRepositoryTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    protected $repository;

    public function setUp()
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        // Create mock objects
        $capsule = m::mock('Illuminate\Database\Capsule\Manager');
        $connection = m::mock('Illuminate\Database\Connection');
        $schemaBuilder = m::mock('Illuminate\Database\Schema\Builder');

        // Create repository instance
        $this->repository = new DatabaseMigrationRepository($capsule, 'migrations');

        // Set global expections for $capule and $connection
        // Repository -> capsule -> connection -> Schema
        // When repository call `getConnection`, it will receive the connection mock
        // When repository call `getSchemaBuilder`, it will receive the schema builder mock
        $capsule->shouldReceive('getConnection')->andReturn($connection);
        $connection->shouldReceive('getSchemaBuilder')->andReturn($schemaBuilder);
    }

    public function testGetRanMigrationsListMigrationsByPackage()
    {
        $query = m::mock('stdClass');

        // Set expectations for the Connection mock
        $this->repository->getConnection()->shouldReceive('table')->once()->with('migrations')->andReturn($query);

        // When getRan is called, the $query should be orderedBy batch, then migration and pluck.
        $query->shouldReceive('orderBy')->once()->with('id', 'asc')->andReturn(new Collection([['migration' => 'bar']]));

        $this->assertEquals(['bar'], $this->repository->getRan());
    }

    public function testGetLastBatchNumberReturnsMaxBatch()
    {
        $query = m::mock('stdClass');

        // Set expectations for the Connection mock
        $this->repository->getConnection()->shouldReceive('table')->once()->with('migrations')->andReturn($query);

        // Table query will need to tell what is the max batch number
        $query->shouldReceive('max')->once()->andReturn(1);

        $this->assertEquals(1, $this->repository->getLastBatchNumber());
    }

    public function testGetLastMigrationsGetsAllMigrationsWithTheLatestBatchNumber()
    {
        $query = m::mock('stdClass');

        // Set expectations for the Connection mock. Table will be get twice
        // (once for max batch, once to get the migration list)
        $this->repository->getConnection()->shouldReceive('table')->twice()->with('migrations')->andReturn($query);

        // Table query will need to tell what is the max batch number
        $query->shouldReceive('max')->once()->with('batch')->andReturn(1);

        // Table should be asked to search for batch 1, orderBy migration and return the foo migration
        $query->shouldReceive('where')->once()->with('batch', 1)->andReturn($query);
        $query->shouldReceive('orderBy')->once()->with('id', 'desc')->andReturn($query);
        $query->shouldReceive('get')->once()->andReturn(new Collection([['migration' => 'foo']]));

        $this->assertEquals(['foo'], $this->repository->getLast());
    }

    public function testLogMethodInsertsRecordIntoMigrationTable()
    {
        $query = m::mock('stdClass');

        // Set expectations for the Connection mock
        $this->repository->getConnection()->shouldReceive('table')->once()->with('migrations')->andReturn($query);

        // When log is called, the table query should receive the insert statement
        $query->shouldReceive('insert')->once()->with(['migration' => 'bar', 'batch' => 1, 'sprinkle' => '']);

        $this->repository->log('bar', 1);
    }

    public function testDeleteMethodRemovesAMigrationFromTheTable()
    {
        $query = m::mock('stdClass');

        // Set expectations for the Connection mock
        $this->repository->getConnection()->shouldReceive('table')->once()->with('migrations')->andReturn($query);

        // When delete is called, a where and delete operation should be performed on the table
        $query->shouldReceive('where')->once()->with('migration', 'foo')->andReturn($query);
        $query->shouldReceive('delete')->once();

        $this->repository->delete('foo');
    }

    public function testGetNextBatchNumberReturnsLastBatchNumberPlusOne()
    {
        $query = m::mock('stdClass');

        // Set expectations for the Connection mock
        $this->repository->getConnection()->shouldReceive('table')->once()->with('migrations')->andReturn($query);

        // Table query will need to tell what is the max batch number
        $query->shouldReceive('max')->once()->andReturn(2);

        $this->assertEquals(3, $this->repository->getNextBatchNumber());
    }

    public function testCreateRepositoryCreatesProperDatabaseTable()
    {
        // Setup expectations for SchemaBuilder. When asked to create the repository, the schema should reeceive the create command
        $this->repository->getSchemaBuilder()->shouldReceive('create')->once()->with('migrations', m::type('Closure'));
        $this->repository->createRepository();
    }
}
