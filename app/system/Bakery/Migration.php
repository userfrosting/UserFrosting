<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Composer\Script\Event;
use Composer\Composer;
use Composer\IO\IOInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Str;
use UserFrosting\System\Bakery\Bakery;
use UserFrosting\System\Bakery\Traits\DatabaseTest;
use UserFrosting\System\Model\Migrations;
use UserFrosting\Sprinkle\Core\Util\BadClassNameException;

/**
 * Migration CLI Tools.
 * Perform database migrations commands
 *
 * @extends Debug
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Migration extends Bakery
{
    use Traits\DatabaseTest;

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
     * Run the `migrate` composer script
     *
     * @access public
     * @static
     * @param @Composer\Script\Event $event
     * @return void
     */
    public static function main(Event $event)
    {
        $bakery = new self($event->getIO(), $event->getComposer());
        $bakery->runUp();
    }

    /**
     * Run the `migrate:rollback` composer script
     *
     * @access public
     * @static
     * @param Event $event
     * @return void
     */
    public static function rollback(Event $event)
    {
        $bakery = new self($event->getIO(), $event->getComposer());

        // Get the arguments
        $args = $bakery->getArguments($event);
        $step = $args->get('step', 1);
        $sprinkle = $args->get('sprinkle');

        $bakery->runDown($step, $sprinkle);
    }

    /**
     * {@inheritDoc}
     */
    public function __construct(IOInterface $io, Composer $composer)
    {
        parent::__construct($io, $composer);

        // Display header,
        $this->io->write("\n<info>/****************************/\n/* UserFrosting's Migration */\n/****************************/</info>");

        // First we need to container to load all the sprinkles
        $this->getContainer();

        // Start by testing the DB connexion, just in case
        $this->testDB();

        // Get schema required to run the table blueprints
        $this->schema = Capsule::schema();

        // Make sure the setup table exist
        $this->setupVersionTable();
    }

    /**
     * Run all the migrations available
     *
     * @access public
     * @return void
     */
    public function runUp()
    {
        // Get installed migrations and pluck by class name. We only need this for now
        $migrations = Migrations::get();
        $this->installed = $migrations->pluck('migration');

        $this->io->debug("\n<info>Installed migrations:</info>");
        $this->io->debug($this->installed->toArray());

        // Get pending migrations
        $this->io->write("\n<info>Fetching available migrations...</info>");
        $this->pending = $this->getPendingMigrations();

        // If there's no pending migration, don't need to go further
        if ($this->pending->isEmpty()) {
            $this->io->write("\n<fg=black;bg=green>Nothing to migrate !</>\n");
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

        $this->io->write("\n<info>Running migrations...</info>");

        // We have a list of fulfillable migration, we run them up!
        foreach ($this->fulfillable as $migration) {
            $this->io->write("> Migrating {$migration->className}...", false);
            $migration->up();
            $this->log($migration);
            $this->io->write(" Done!");
        }

        // If all went well and there's no fatal errors, we are ready to bake
        $this->io->write("\n<fg=black;bg=green>Migration successful !</>\n");
    }

    /**
     * Rollback the last migrations.
     *
     * @access public
     * @param int $step (default: 1)
     * @param string $sprinkle (default: "")
     * @return void
     */
    public function runDown($step = 1, $sprinkle = "")
    {
        // Can't go furhter down than 1 step
        if ($step <= 0) {
            throw new \InvalidArgumentException("Step can't be less than 1");
        }

        // Get last batch number
        $batch = $this->getNextBatchNumber();

        // Calculate the number of steps back we need to take
        $stepsBack = max($batch - $step, 1);
        $this->io->debug("\nRolling back $step steps to batch $stepsBack");

        // Get installed migrations
        $migrations = Migrations::orderBy("created_at", "desc")->where('batch', '>=', $stepsBack);

        // Add the sprinkle requirement too
        if ($sprinkle != "") {
            $this->io->debug("Rolling back sprinkle `$sprinkle`");
            $migrations->where('sprinkle', $sprinkle);
        }

        // Run query
        $migrations = $migrations->get();

        // If there's nothing to rollback, stop here
        if ($migrations->isEmpty()) {
            $this->io->write("\n<info>Nothing to rollback</info>");
            exit(1);
        }

        // Get pending migrations
        $this->io->write("\n<info>Migration to rollback:</info>");
        $this->io->write($migrations->pluck('migration')->toArray());

        // Ask confirmation to continue.
        if (!$this->io->askConfirmation("\nContinue? [y/N]", false)) {
            exit(1);
        }

        // Only thing we have to check here before going further is if those migration class are available
        // We do it before running anything down to be sure not to break anything
        foreach ($migrations as $migration) {
            if (!class_exists($migration->migration)) {
                $this->io->error("Migration class {$migration->migration} doesn't exist.");
                exit(1);
            }
        }

        // Loop again to run down each migration
        foreach ($migrations as $migration) {
            $this->io->write("> Rolling back {$migration->migration}...", false);
            $migrationClass = $migration->migration;
            $instance = new $migrationClass($this->schema);
            $instance->down();
            $migration->delete();
            $this->io->write(" Done!");
        }

        // If all went well and there's no fatal errors, we are ready to bake
        $this->io->write("\n<fg=black;bg=green>Rollback successful !</>\n");
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

            $this->io->write("> Fetching from `$sprinkle`");

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
                    throw new BadClassNameException("Unable to find the migration class '$migration'." );
                }

                // Load the migration class
                $migration = new $migrationClass($this->schema);

                //Set the sprinkle
                $migration->sprinkle = $sprinkle;

                // Also set the class name. We could find it using ::class, but this
                // will make it easier to manipulate the collection
                $migration->className = $migrationClass;

                // Add it to the pending list
                $pending->push($migration);
            }
        }

        $this->io->debug("\n<info>Pending migrations:</info>");
        $this->io->debug($pending->pluck('className')->toArray());

        return $pending;
    }

    /**
     * Get the list of migrations avaiables in the filesystem.
     * Return a list of resolved className
     *
     * @access public
     * @param mixed $sprinkleName
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
            $className = "\\UserFrosting\\Sprinkle\\".$sprinkleName."\\Model\Migrations\\".$version."\\".$className;

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
        $this->io->debug("\n<info>Resolving migrations dependencies...</info>");

        // Reset fulfillable/unfulfillable lists
        $this->fulfillable = collect([]);
        $this->unfulfillable = collect([]);

        // Loop pending and check for dependencies
        foreach ($this->pending as $migration) {
            $this->validateClassDependencies($migration);
        }

        $this->io->debug("\n<info>Fulfillable migrations:</info>");
        $this->io->debug($this->fulfillable->pluck('className')->toArray());

        $this->io->debug("\n<info>Unfulfillable migrations:</info>");
        $this->io->debug($this->unfulfillable->pluck('className')->toArray());
    }

    /**
     * Check if a migration dependencies are met.
     * To test if a migration is fulfillable, the class must :
     * Already been installed OR exist and have all it's dependencies met
     *
     * @access protected
     * @param mixed $migration
     * @return bool true/false if all conditions are met
     */
    protected function validateClassDependencies($migration)
    {
        $this->io->debug("> Checking dependencies for {$migration->className}");

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
        if ($this->installed->contains($migration)) {
            return $this->markAsFulfillable($migration);
        }

        // Loop dependencies. If one is not fulfillable, then this one is not either
        foreach ($migration->dependencies as $dependencyClass) {

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
            $this->io->write("\n<info>Creating the `{$this->table}` table...</info>");

            $migration = new \UserFrosting\System\Bakery\Migrations\v410\MigrationTable($this->schema);
            $migration->up();

            $this->io->write("Table `{$this->table}` created");
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
        $path = \UserFrosting\APP_DIR .
                \UserFrosting\DS .
                \UserFrosting\SPRINKLES_DIR_NAME .
                \UserFrosting\DS .
                $sprinkleName .
                \UserFrosting\DS .
                \UserFrosting\SRC_DIR_NAME .
                "/Model/Migrations/";

        return $path;
    }
}