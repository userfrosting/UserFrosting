<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;

/**
 * MigrationRepository Class
 *
 * Repository used to store all migrations run against the database
 *
 * @author Louis Charette
 */
class DatabaseMigrationRepository implements MigrationRepositoryInterface
{
    /**
     * @var Capsule
     */
    protected $db;

    /**
     * @var string The name of the migration table.
     */
    protected $table;

    /**
     * @var string The connection name
     */
    protected $connection;

    /**
     * Create a new database migration repository instance.
     *
     * @param  Capsule  $db
     * @param  string  $table
     * @return void
     */
    public function __construct(Capsule $db, $table = "migrations")
    {
        $this->table = $table;
        $this->db = $db;
    }

    /**
     * Get the ran migrations.
     *
     * @return array An array of migration class names in the order they where ran
     */
    public function getRan()
    {
        return $this->table()
                ->orderBy('id', 'asc')
                ->pluck('migration')->all();
    }

    /**
     * Get list of migrations.
     *
     * @param  int  $steps Number of batch to return
     * @return array
     */
    public function getMigrations($steps)
    {
        $batch = max($this->getNextBatchNumber() - $steps, 1);
        return $this->table()->where('batch', '>=', $batch)->orderBy('id', 'desc')->get()->pluck('migration')->all();
    }

    /**
     * Get the last migration batch in reserve order they were ran (last one first)
     *
     * @return array
     */
    public function getLast()
    {
        $query = $this->table()->where('batch', $this->getLastBatchNumber());

        return $query->orderBy('id', 'desc')->get()->pluck('migration')->all();
    }

    /**
     * Log that a migration was run.
     *
     * @param  string  $file
     * @param  int     $batch
     * @param  string  $sprinkle
     * @return void
     */
    public function log($file, $batch, $sprinkle = "")
    {
        $record = ['migration' => $file, 'batch' => $batch, 'sprinkle' => $sprinkle];

        $this->table()->insert($record);
    }

    /**
     * Remove a migration from the log.
     *
     * @param  string  $migration
     * @return void
     */
    public function delete($migration)
    {
        $this->table()->where('migration', $migration)->delete();
    }

    /**
     * Get the next migration batch number.
     *
     * @return int
     */
    public function getNextBatchNumber()
    {
        return $this->getLastBatchNumber() + 1;
    }

    /**
     * Get the last migration batch number.
     *
     * @return int
     */
    public function getLastBatchNumber()
    {
        return $this->table()->max('batch');
    }

    /**
     * Create the migration repository data store.
     *
     * @return void
     */
    public function createRepository()
    {
        $this->getSchemaBuilder()->create($this->table, function (Blueprint $table) {
            // The migrations table is responsible for keeping track of which of the
            // migrations have actually run for the application. We'll create the
            // table to hold the migration file's path as well as the batch ID.
            $table->increments('id');
            $table->string('sprinkle');
            $table->string('migration');
            $table->integer('batch');
        });
    }

    /**
     *    Delete the migration repository data store
     *
     *    @return void
     */
    public function deleteRepository()
    {
        $this->getSchemaBuilder()->drop($this->table);
    }

    /**
     * Determine if the migration repository exists.
     *
     * @return bool
     */
    public function repositoryExists()
    {
        return $this->getSchemaBuilder()->hasTable($this->table);
    }

    /**
     * Get a query builder for the migration table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table()
    {
        return $this->getConnection()->table($this->table);
    }

    /**
     * Returns the schema builder instance
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    public function getSchemaBuilder()
    {
        return $this->getConnection()->getSchemaBuilder();
    }

    /**
     * Resolve the database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return $this->db->getConnection($this->connection);
    }

    /**
     *    Set the information source to gather data.
     *
     *    @param string $name The source name
     */
    public function setSource($name)
    {
        $this->connection = $name;
    }
}
