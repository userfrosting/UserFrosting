<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use Illuminate\Support\Arr;
use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationDependencyAnalyser as Analyser;
use UserFrosting\Sprinkle\Core\Util\BadClassNameException;

/**
 * Migrator Class
 *
 * Migrator service used to manage and run database migrations
 *
 * @author Louis Charette
 */
class Migrator
{
    /**
     * @var MigrationRepositoryInterface The migration repository implementation.
     */
    protected $repository;

    /**
     * @var Capsule
     */
    protected $db;

    /**
     * @var MigrationLocatorInterface The Migration locator instance
     */
    protected $locator;

    /**
     *    @var string The connection name
     */
    protected $connection;

    /**
     * @var array The notes for the current operation.
     */
    protected $notes = [];

    /**
     *    Constructor
     *
     *    @param Capsule $db The database instance
     *    @param MigrationRepositoryInterface $repository The migration repository
     *    @param MigrationLocatorInterface $locator The Migration locator
     */
    public function __construct(Capsule $db, MigrationRepositoryInterface $repository, MigrationLocatorInterface $locator)
    {
        $this->db = $db;
        $this->repository = $repository;
        $this->locator = $locator;
    }

    /**
     *    Run all the specified migrations up. Check that dependencies are met before running
     *
     *    @param  array $options Options for the current operations [step, pretend]
     *    @return array The list of ran migrations
     */
    public function run(array $options = [])
    {
        $this->notes = [];

        // Get the list of available migration classes in the the Filesystem
        $availableMigrations = $this->getAvailableMigrations();

        // Get ran migrations
        $ranMigrations = $this->repository->getRan();

        // Get outstanding migrations classes that requires to be run up
        $pendingMigrations = $this->pendingMigrations($availableMigrations, $ranMigrations);

        // First we will just make sure that there are any migrations to run. If there
        // aren't, we will just make a note of it to the developer so they're aware
        // that all of the migrations have been run against this database system.
        if (count($pendingMigrations) == 0) {
            return [];
        }

        // Next we need to validate that all pending migration dependencies are met or WILL BE MET
        // This operation is important as it's the one that place the outstanding migrations
        // in the correct order, making sure a migration script won't fail because the table
        // it depends on has not been created yet (for example).
        $analyser = new Analyser($pendingMigrations, $ranMigrations);

        // Any migration without a fulfilled dependency will cause this script to throw an exception
        if ($unfulfillable = $analyser->getUnfulfillable()) {
            $unfulfillableList = implode(", ", $unfulfillable);
            throw new \Exception("Unfulfillable migrations found :: $unfulfillableList");
        }

        // Run pending migration up
        return $this->runPending($analyser->getFulfillable(), $options);
    }

    /**
     *    Get the migration classes that have not yet run.
     *
     *    @param  array $available The available migrations returned by the migration locator
     *    @param  array $ran The list of already ran migrations returned by the migration repository
     *    @return array The list of pending migrations, ie the available migrations not ran yet
     */
    public function pendingMigrations($available, $ran)
    {
        return collect($available)->reject(function ($migration) use ($ran) {
            return collect($ran)->contains($migration);
        })->values()->all();
    }

    /**
     *    Run an array of migrations.
     *
     *    @param  array $migrations An array of migrations classes names to be run (unsorted, unvalidated)
     *    @param  array $options The options for the current operation [step, pretend]
     *    @return void
     */
    protected function runPending(array $migrations, array $options = [])
    {
        // Next, we will get the next batch number for the migrations so we can insert
        // correct batch number in the database migrations repository when we store
        // each migration's execution.
        $batch = $this->repository->getNextBatchNumber();

        // We extract a few of the options.
        $pretend = Arr::get($options, 'pretend', false);
        $step = Arr::get($options, 'step', 0);

        // We now have an ordered array of migrations, we will spin through them and run the
        // migrations "up" so the changes are made to the databases. We'll then log
        // that the migration was run so we don't repeat it next time we execute.
        foreach ($migrations as $migrationClass) {
            $this->runUp($migrationClass, $batch, $pretend);

            if ($step) {
                $batch++;
            }
        }

        return $migrations;
    }

    /**
     *    Run "up" a migration class
     *
     *    @param  string $migrationClass The migration class name
     *    @param  int $batch The current bacth number
     *    @param  bool $pretend If this operation should be pretended / faked
     *    @return void
     */
    protected function runUp($migrationClass, $batch, $pretend)
    {
        // First we will resolve a "real" instance of the migration class from
        // the class name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
        $migration = $this->resolve($migrationClass);

        // Move into pretend mode if requested
        if ($pretend) {
            return $this->pretendToRun($migration, 'up');
        }

        // Run the actuall migration
        $this->runMigration($migration, 'up');

        // Once we have run a migrations class, we will log that it was run in this
        // repository so that we don't try to run it next time we do a migration
        // in the application. A migration repository keeps the migrate order.
        $this->repository->log($migrationClass, $batch);

        $this->note("<info>Migrated:</info>  {$migrationClass}");
    }

    /**
     *    Rollback the last migration operation.
     *
     *    @param  array $options The options for the current operation [steps, pretend]
     *    @return array The list of rolledback migration classes
     */
    public function rollback(array $options = [])
    {
        $this->notes = [];

        // We want to pull in the last batch of migrations that ran on the previous
        // migration operation. We'll then reverse those migrations and run each
        // of them "down" to reverse the last migration "operation" which ran.
        $migrations = $this->getMigrationsForRollback($options);

        if (count($migrations) === 0) {
            return [];
        } else {
            return $this->rollbackMigrations($migrations, $options);
        }
    }

    /**
     * Get the migrations for a rollback operation.
     *
     * @param  array  $options The options for the current operation
     * @return array  An ordered array of migrations to rollback
     */
    protected function getMigrationsForRollback(array $options)
    {
        $steps = Arr::get($options, 'steps', 0);
        if ($steps > 0) {
            return $this->repository->getMigrations($steps);
        } else {
            return $this->repository->getLast();
        }
    }

    /**
     *    Rollback the given migrations.
     *
     *    @param  array $migrations An array of migrations to rollback formated as an eloquent collection
     *    @param  array $options The options for the current operation
     *    @return array The list of rolledback migration classes
     */
    protected function rollbackMigrations(array $migrations, array $options)
    {
        $rolledBack = [];

        // Get the available migration classes in the filesystem
        $availableMigrations = collect($this->getAvailableMigrations());

        // Next we will run through all of the migrations and call the "down" method
        // which will reverse each migration in order. This getLast method on the
        // repository already returns these migration's classenames in reverse order.
        foreach ($migrations as $migration) {

            // We have to make sure the class exist first
            if (!$availableMigrations->contains($migration)) {
                //throw new \Exception("Can't rollback migrations `$migration`. The migration class doesn't exist");
                continue;
            }

            // Add the migration to the list of rolledback migration
            $rolledBack[] = $migration;

            // Extract some options
            $pretend = Arr::get($options, 'pretend', false);

            // Run the migration down
            $this->runDown($migration, $pretend);
        }

        return $rolledBack;
    }

    /**
     *    Rolls all of the currently applied migrations back.
     *
     *    @param bool $pretend Should this operation be pretended
     *    @return array An array of all the rolledback migration classes
     */
    public function reset($pretend = false)
    {
        $this->notes = [];

        // We get the list of all the migrations class available and reverse
        // said list so we can run them back in the correct order for resetting
        // this database. This will allow us to get the database back into its
        // "empty" state and ready to be migrated "up" again.
        //
        // !TODO :: Should compare to the install list to make sure no outstanding migration (ran, but with no migraiton class anymore) still exist in the db
        $migrations = array_reverse($this->repository->getRan());

        if (count($migrations) === 0) {
            return [];
        } else {
            return $this->rollbackMigrations($migrations, compact('pretend'));
        }
    }

    /**
     *    Run "down" a migration instance.
     *
     *    @param  string $migrationClass The migration class name
     *    @param  bool $pretend Is the operation should be pretended
     *    @return void
     */
    protected function runDown($migrationClass, $pretend)
    {
        // We resolve an instance of the migration. Once we get an instance we can either run a
        // pretend execution of the migration or we can run the real migration.
        $instance = $this->resolve($migrationClass);

        if ($pretend) {
            return $this->pretendToRun($instance, 'down');
        }

        $this->runMigration($instance, 'down');

        // Once we have successfully run the migration "down" we will remove it from
        // the migration repository so it will be considered to have not been run
        // by the application then will be able to fire by any later operation.
        $this->repository->delete($migrationClass);

        $this->note("<info>Rolled back:</info>  {$migrationClass}");
    }

    /**
     *    Run a migration inside a transaction if the database supports it.
     *    Note : As of Laravel 5.4, only PostgresGrammar supports it
     *
     *    @param  object $migration The migration instance
     *    @param  string $method The method used [up, down]
     *    @return void
     */
    protected function runMigration($migration, $method)
    {
        $callback = function () use ($migration, $method) {
            if (method_exists($migration, $method)) {
                $migration->{$method}();
            }
        };

        if ($this->getSchemaGrammar()->supportsSchemaTransactions()) {
            $this->getConnection()->transaction($callback);
        } else {
            $callback();
        }

    }

    /**
     *    Pretend to run the migrations.
     *
     *    @param  object $migration The migration instance
     *    @param  string $method The method used [up, down]
     *    @return void
     */
    protected function pretendToRun($migration, $method)
    {
        $name = get_class($migration);
        $this->note("\n<info>$name</info>");

        foreach ($this->getQueries($migration, $method) as $query) {
            $this->note("> {$query['query']}");
        }
    }

    /**
     *    Get all of the queries that would be run for a migration.
     *
     *    @param  object $migration The migration instance
     *    @param  string $method The method used [up, down]
     *    @return array The queries executed by the processed schema
     */
    protected function getQueries($migration, $method)
    {
        // Get the connection instance
        $connection = $this->getConnection();

        return $connection->pretend(function () use ($migration, $method) {
            if (method_exists($migration, $method)) {
                $migration->{$method}();
            }
        });
    }

    /**
     *    Resolve a migration instance from it's class name.
     *
     *    @param  string $migrationClass The class name
     *    @return object The migration class instance
     */
    public function resolve($migrationClass)
    {
        if (!class_exists($migrationClass)) {
            throw new BadClassNameException("Unable to find the migration class '$migrationClass'." );
        }

        return new $migrationClass($this->getSchemaBuilder());
    }

    /**
     *    Get all of the migration files in a given path.
     *
     *    @return array The list of migration classes found in the filesystem
     */
    public function getAvailableMigrations()
    {
        return $this->locator->getMigrations();
    }

    /**
     *    Get the migration repository instance.
     *
     *    @return \Illuminate\Database\Migrations\MigrationRepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     *    Set the migration repository instance
     *
     *    @param MigrationRepositoryInterface $repository
     */
    public function setRepository(MigrationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     *    Determine if the migration repository exists.
     *
     *    @return bool If the repository exist
     */
    public function repositoryExists()
    {
        return $this->repository->repositoryExists();
    }

    /**
     *    Get the migration locator instance.
     *
     *    @return MigrationLocatorInterface
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     *    Set the migration locator instance
     *
     *    @param MigrationLocatorInterface $locator
     */
    public function setLocator(MigrationLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     *    Get the schema builder.
     *
     *    @return \Illuminate\Database\Schema\Builder
     */
    public function getSchemaBuilder()
    {
        return $this->getConnection()->getSchemaBuilder();
    }

    /**
     *    Return the connection instance
     *
     *    @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return $this->db->getConnection($this->connection);
    }

    /**
     *    Define which connection to use
     *
     *    @param string $name The connection name
     */
    public function setConnection($name)
    {
        $this->repository->setSource($name);
        $this->connection = $name;
    }

    /**
     *    Get instance of Grammar
     *    @return \Illuminate\Database\Schema\Grammars\Grammar
     */
    protected function getSchemaGrammar()
    {
        return $this->getConnection()->getSchemaGrammar();
    }

    /**
     *    Raise a note event for the migrator.
     *
     *    @param  string  $message The message
     *    @return void
     */
    protected function note($message)
    {
        $this->notes[] = $message;
    }

    /**
     *    Get the notes for the last operation.
     *
     *    @return array An array of notes
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
