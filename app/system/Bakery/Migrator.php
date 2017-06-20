<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Str;
use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UserFrosting\Sprinkle\Core\Util\BadClassNameException;
use UserFrosting\System\Database\Model\Migrations;
use UserFrosting\System\Bakery\DatabaseTest;

/**
 * Migration CLI Tools.
 * Perform database migrations commands
 *
 * @extends Debug
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Migrator
{
    use DatabaseTest;

    /**
     * @var @Symfony\Component\Console\Style\SymfonyStyle
     * See http://symfony.com/doc/current/console/style.html
     */
    protected $io;

    /**
     * @var ContainerInterface $ci The global container object, which holds all of UserFristing services.
     */
    protected $ci;

    /**
     * @var @Illuminate\Database\Schema
     */
    protected $schema;

     /**
     * @var String table The name of the migration table
     */
    protected $table = "migrations";

    /**
     * @var Array sprinkles The list of defined sprinkles
     */
    protected $sprinkles;

    /**
     * @var Int Current batch number. All the migration class run `up` in a single command will be grouped by this batch number
     */
    protected $batch;

    /**
     * @var Collection List of pending migration that require installation
     */
    protected $pending;

    /**
     * @var Collection List of installed migration. This is built from the log data in the database
     */
    protected $installed;

    /**
     * @var List of fulfillable migration (Migration that needs to be run and their dependencies are met)
     * This list is very important. While `pending` is a list of migrations that needs to be run, `fulfillable`
     * contain the order in which they are required to be run. When resolving the dependencies, this list will
     * automatically be sorted to make sure the dependencies are run in the correct order
     */
    protected $fulfillable;

    /**
     * @var List of unfulfillable migration (Migration that needs to be run and their dependencies are NOT met)
     * Note : An error could be thrown when an unfulfillable migration is met, but it makes much nicer cli error this way
     */
    protected $unfulfillable;

    /**
     * Constructor.
     *
     * @access public
     * @param SymfonyStyle $io
     * @param ContainerInterface $ci
     * @return void
     */
    public function __construct(SymfonyStyle $io, ContainerInterface $ci)
    {
        $this->io = $io;
        $this->ci = $ci;

        // Start by testing the DB connexion, just in case
        try {
            $this->io->writeln("<info>Testing database connexion</info>", OutputInterface::VERBOSITY_VERBOSE);
            $this->testDB();
            $this->io->writeln("Ok", OutputInterface::VERBOSITY_VERBOSE);
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());
            exit(1);
        }

        // Get schema required to run the table blueprints
        $this->schema = Capsule::schema();

        // Make sure the setup table exist
        $this->setupVersionTable();
    }

    /**
     * Run all the migrations available
     *
     * @access public
     * @param bool $pretend (default: false)
     * @return void
     */
    public function runUp($pretend = false)
    {
        // Get installed migrations and pluck by class name. We only need this for now
        $migrations = Migrations::get();
        $this->installed = $migrations->pluck('migration');

        $this->io->writeln("\n<info>Installed migrations:</info>", OutputInterface::VERBOSITY_VERBOSE);
        $this->io->writeln($this->installed->toArray(), OutputInterface::VERBOSITY_VERBOSE);

        // Get pending migrations
        $this->io->section("Fetching available migrations");
        $this->pending = $this->getPendingMigrations();

        // If there's no pending migration, don't need to go further
        if ($this->pending->isEmpty()) {
            $this->io->success("Nothing to migrate !");
            return;
        }

        // Resolve the dependencies
        $this->resolveDependencies();

        // If there are any unfulfillable migration, we can't continue
        if (!$this->unfulfillable->isEmpty()) {

            $msg = "\nSome migrations dependencies can't be met. Check those migrations for unmet dependencies and try again:";

            foreach ($this->unfulfillable as $migration) {
                $msg .= "\n{$migration->className} depends on \n  - ";
                $msg .= implode("\n  - ", $migration->dependencies);
                $msg .= "\n";
            }

            $this->io->error($msg);
            exit(1);
        }

        // Ready to run !
        $this->io->section("Running migrations");

        if ($pretend) {
            $this->io->note("Running migration in pretend mode");
        }

        // We have a list of fulfillable migration, we run them up!
        foreach ($this->fulfillable as $migration) {
            $this->io->write("\n> <info>Migrating {$migration->className}...</info>");

            if ($pretend) {
                $this->io->newLine();
                $this->pretendToRun($migration, 'up');
            } else {
                $migration->up();
                $migration->seed();
                $this->log($migration);
                $this->io->writeln(" Done!");
            }
        }

        // If all went well and there's no fatal errors, we are ready to bake
        $this->io->success("Migration successful !");
    }

    /**
     * Rollback the last btach of migrations.
     *
     * @access public
     * @param int $step (default: 1). Number of batch we will be going back. -1 revert all migrations
     * @param string $sprinkle (default: "") Limit rollback to a specific sprinkle
     * @param bool $pretend (default: false)
     * @return void
     */
    public function runDown($step = 1, $sprinkle = "", $pretend = false)
    {
        // Can't go furhter down than 1 step
        if ($step <= 0 && $step != -1) {
            throw new \InvalidArgumentException("Step can't be 0 or less");
        }

        // Get last batch number
        $batch = $this->getNextBatchNumber();

        // Calculate the number of steps back we need to take
        if ($step == -1) {
            $stepsBack = 1;
            $this->io->warning("Rolling back all migrations");
        } else {
            $stepsBack = max($batch - $step, 1);
            $this->io->note("Rolling back $step steps to batch $stepsBack", OutputInterface::VERBOSITY_VERBOSE);
        }

        // Get installed migrations
        $migrations = Migrations::orderBy("created_at", "desc")->where('batch', '>=', $stepsBack);

        // Add the sprinkle requirement too
        if ($sprinkle != "") {
            $this->io->note("Rolling back sprinkle `$sprinkle`", OutputInterface::VERBOSITY_VERBOSE);
            $migrations->where('sprinkle', $sprinkle);
        }

        // Run query
        $migrations = $migrations->get();

        // If there's nothing to rollback, stop here
        if ($migrations->isEmpty()) {
            $this->io->writeln("<info>Nothing to rollback</info>");
            exit(1);
        }

        // Get pending migrations
        $this->io->writeln("<info>Migration to rollback:</info>");
        $this->io->listing($migrations->pluck('migration')->toArray());

        // Ask confirmation to continue.
        if (!$pretend && !$this->io->confirm("Continue?", false)) {
            exit(1);
        }

        if ($pretend) {
            $this->io->note("Rolling back in pretend mode");
        }

        // Loop again to run down each migration
        foreach ($migrations as $migration) {

            // Check if those migration class are available
            if (!class_exists($migration->migration)) {
                $this->io->warning("Migration class {$migration->migration} doesn't exist.");
                continue;
            }

            $this->io->write("> <info>Rolling back {$migration->migration}...</info>");
            $migrationClass = $migration->migration;
            $instance = new $migrationClass($this->schema, $this->io);

            if ($pretend) {
                $this->io->newLine();
                $this->pretendToRun($instance, 'down');
            } else {
                $instance->down();
                $migration->delete();
                $this->io->writeln(" Done!");
            }

            $this->io->newLine();
        }

        // If all went well and there's no fatal errors, we are ready to bake
        $this->io->success("Rollback successful !");
    }

    /**
     * Pretend to run migration class.
     *
     * @access protected
     * @param mixed $migration
     * @param string $method up/down
     */
    protected function pretendToRun($migration, $method)
    {
        foreach ($this->getQueries($migration, $method) as $query) {
            $this->io->writeln($query['query'], OutputInterface::VERBOSITY_VERBOSE);
        }
    }

    /**
     * Return all of the queries that would be run for a migration.
     *
     * @access protected
     * @param mixed $migration
     * @param string $method up/down
     * @return void
     */
    protected function getQueries($migration, $method)
    {
        $db = $this->schema->getConnection();

        return $db->pretend(function () use ($migration, $method) {
            if (method_exists($migration, $method)) {
                $migration->{$method}();
            }
        });
    }

    /**
     * Get pending migrations by looking at all the migration files
     * and finding the one not yet runed by compairing with the ran migrations
     *
     * @access protected
     * @return void
     */
    protected function getPendingMigrations()
    {
        $pending = collect([]);

        // Get the sprinkle list
        $sprinkles = $this->ci->sprinkleManager->getSprinkleNames();

        // Loop all the sprinkles to find their pending migrations
        foreach ($sprinkles as $sprinkle) {

            $this->io->writeln("> Fetching from `$sprinkle`");

            // We get all the migrations. This will return them as a colleciton of class names
            $migrations = $this->getMigrations($sprinkle);

            // We filter the available migration by removing the one that have already been run
            // This reject the class name found in the installed collection
            $migrations = $migrations->reject(function ($value, $key) {
                return $this->installed->contains($value);
            });

            // Load each class
            foreach ($migrations as $migrationClass) {

                // Make sure the class exist
                if (!class_exists($migrationClass)) {
                    throw new BadClassNameException("Unable to find the migration class '$migrationClass'." );
                }

                // Load the migration class
                $migration = new $migrationClass($this->schema, $this->io);

                //Set the sprinkle
                $migration->sprinkle = $sprinkle;

                // Also set the class name. We could find it using ::class, but this
                // will make it easier to manipulate the collection
                $migration->className = $migrationClass;

                // Add it to the pending list
                $pending->push($migration);
            }
        }

        // Get pending migrations
        $pendingArray = ($pending->pluck('className')->toArray()) ?: "";
        $this->io->writeln("\n<info>Pending migrations:</info>", OutputInterface::VERBOSITY_VERBOSE);
        $this->io->writeln($pendingArray, OutputInterface::VERBOSITY_VERBOSE);

        return $pending;
    }

    /**
     * Get the list of migrations avaiables in the filesystem.
     * Return a list of resolved className
     *
     * @access public
     * @param string $sprinkleName
     * @return void
     */
    public function getMigrations($sprinkle)
    {
        // Find all the migration files
        $path = $this->migrationDirectoryPath($sprinkle);
        $files = glob($path . "*/*.php");

        // Transform the array in a collection
        $migrations = collect($files);

        // We transform the path into a migration object
        $migrations->transform(function ($file) use ($sprinkle, $path) {
            // Deconstruct the path
            $migration = str_replace($path, "", $file);
            $className = basename($file, '.php');
            $sprinkleName = Str::studly($sprinkle);
            $version = str_replace("/$className.php", "", $migration);

            // Reconstruct the classname
            $className = "\\UserFrosting\\Sprinkle\\".$sprinkleName."\\Database\\Migrations\\".$version."\\".$className;

            return $className;
        });

        return $migrations;
    }

    /**
     * Resolve all the dependencies for all the pending migrations
     * This function fills in the `fullfillable` and `unfulfillable` list
     *
     * @access protected
     * @return void
     */
    protected function resolveDependencies()
    {
        $this->io->writeln("\n<info>Resolving migrations dependencies...</info>", OutputInterface::VERBOSITY_VERBOSE);

        // Reset fulfillable/unfulfillable lists
        $this->fulfillable = collect([]);
        $this->unfulfillable = collect([]);

        // Loop pending and check for dependencies
        foreach ($this->pending as $migration) {
            $this->validateClassDependencies($migration);
        }

        $fulfillable = ($this->fulfillable->pluck('className')->toArray()) ?: "";
        $this->io->writeln("\n<info>Fulfillable migrations:</info>", OutputInterface::VERBOSITY_VERBOSE);
        $this->io->writeln($fulfillable, OutputInterface::VERBOSITY_VERBOSE);

        $unfulfillable = ($this->unfulfillable->pluck('className')->toArray()) ?: "";
        $this->io->writeln("\n<info>Unfulfillable migrations:</info>", OutputInterface::VERBOSITY_VERBOSE);
        $this->io->writeln($unfulfillable, OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * Check if a migration dependencies are met.
     * To test if a migration is fulfillable, the class must :
     * Already been installed OR exist and have all it's dependencies met
     *
     * @access protected
     * @param $migration
     * @return bool true/false if all conditions are met
     */
    protected function validateClassDependencies($migration)
    {
        $this->io->writeln("> Checking dependencies for {$migration->className}", OutputInterface::VERBOSITY_VERBOSE);

        // If it's already marked as fulfillable, it's fulfillable
        // Return true directly (it's already marked)
        if ($this->fulfillable->contains($migration)) {
            return true;
        }

        // If it's already marked as unfulfillable, it's unfulfillable
        // Return false directly (it's already marked)
        if ($this->unfulfillable->contains($migration)) {
            return false;
        }

        // If it's already run, it's fulfillable
        // Mark it as such for next time it comes up in this loop
        if ($this->installed->contains($migration->className)) {
            return $this->markAsFulfillable($migration);
        }

        // Loop dependencies. If one is not fulfillable, then this one is not either
        foreach ($migration->dependencies as $dependencyClass) {

            // The dependency might already be installed. Check that first
            if ($this->installed->contains($dependencyClass)) {
                continue;
            }

            // Try to find it in the `pending` list. Cant' find it? Then it's not fulfillable
            $dependency = $this->pending->where('className', $dependencyClass)->first();

            // Check migration dependencies of this one right now
            // If ti's not fullfillable, then this one isn't either
            if (!$dependency || !$this->validateClassDependencies($dependency)) {
                return $this->markAsUnfulfillable($migration);
            }
        }

        // If no dependencies returned false, it's fulfillable
        return $this->markAsFulfillable($migration);
    }

    /**
     * Mark a dependency as fulfillable.
     * Removes it from the pending list and add it to the fulfillable list
     *
     * @access protected
     * @param $migration
     * @return true
     */
    protected function markAsFulfillable($migration)
    {
        $this->fulfillable->push($migration);
        return true;
    }

    /**
     * Mark a dependency as unfulfillable.
     * Removes it from the pending list and add it to the unfulfillable list
     *
     * @access protected
     * @param $migration
     * @return false
     */
    protected function markAsUnfulfillable($migration)
    {
        $this->unfulfillable->push($migration);
        return false;
    }

    /**
     * Log that a migration was run.
     *
     * @access public
     * @param mixed $migration
     * @return void
     */
    protected function log($migration)
    {
        // Get the next batch number if not defined
        if (!$this->batch) {
            $this->batch = $this->getNextBatchNumber();
        }

        $log = new Migrations([
            'sprinkle' => $migration->sprinkle,
            'migration' => $migration->className,
            'batch' => $this->batch
        ]);
        $log->save();
    }

    /**
     * Return the next batch number from the db.
     * Batch number is used to group together migration run in the same operation
     *
     * @access public
     * @return int the next batch number
     */
    public function getNextBatchNumber()
    {
        $batch = Migrations::max('batch');
        return ($batch) ? $batch + 1 : 1;
    }

    /**
     * Create the migration history table if needed.
     * Also check if the tables requires migrations
     * We run the migration file manually for this one
     *
     * @access public
     * @return void
     */
    protected function setupVersionTable()
    {
        // Check if the `migrations` table exist. Create it manually otherwise
        if (!$this->schema->hasColumn($this->table, 'id')) {
            $this->io->section("Creating the `{$this->table}` table");

            $migration = new \UserFrosting\System\Database\Migrations\v410\MigrationTable($this->schema, $this->io);
            $migration->up();

            $this->io->success("Table `{$this->table}` created");
        }
    }

    /**
     * Returns the path of the Migration directory.
     *
     * @access protected
     * @param mixed $sprinkleName
     * @return void
     */
    protected function migrationDirectoryPath($sprinkleName)
    {
        $path = \UserFrosting\SPRINKLES_DIR .
                \UserFrosting\DS .
                $sprinkleName .
                \UserFrosting\DS .
                \UserFrosting\SRC_DIR_NAME .
                "/Database/Migrations/";

        return $path;
    }
}
