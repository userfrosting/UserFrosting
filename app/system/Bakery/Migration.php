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
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Str;
use UserFrosting\System\Bakery\Bakery;
use UserFrosting\System\Bakery\Debug;
use UserFrosting\System\Model\Migrations;
use UserFrosting\Sprinkle\Core\Util\BadClassNameException;

/**
 * Migration CLI Tools.
 * Perform database migrations commands
 * N.B.: This class extends `Debug` since we'll reuse debug db testing
 *
 * @extends Debug
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Migration extends Debug
{
    /**
     * @var @Illuminate\Database\Schema
     */
    protected $schema;

     /**
     * @var table The name of the migration table
     */
    protected $table = "migrations";

    /**
     * @var sprinkles The list of defined sprinkles
     */
    protected $sprinkles;

    /**
     * @var Int Current batch number. All the migration class run `up` in a single command will be grouped by this batch number
     */
    protected $batch;

    /**
     * @var List of pending migration that require installation
     */
    protected $pending;

    /**
     * @var List of installed migration
     */
    protected $installed;

    /**
     * @var List of fulfillable migration (Migration that needs to be run and their dependencies are met)
     */
    protected $fulfillable;

    /**
     * @var List of unfulfillable migration (Migration that needs to be run and their dependencies are NOT met)
     */
    protected $unfulfillable;

    /**
     * Run the `migrate` composer script
     *
     * @access public
     * @static
     * @param Event $event
     * @return void
     */
    public static function main(Event $event)
    {
        $bakery = new self($event->getIO(), $event->getComposer());
        $bakery->runUp();
    }

    /**
     * Run all the migrations available
     *
     * @access public
     * @return void
     */
    public function runUp()
    {
        // Display header,
        $this->io->write("\n<info>/****************************/\n/* UserFrosting's Migration */\n/****************************/</info>");

        // First we need to container to load all the sprinkles
        $this->getContainer();

        // Start by testing the DB connexion, just in case
        $this->testDB();

        // Get schema required to run the table blueprints
        $this->schema = Capsule::schema();

        // Get installed migrations
        $this->installed = $this->getInstalledMigrations();

        $this->io->debug("\n<info>Installed migrations:</info>");
        $this->io->debug($this->installed->toArray());

        // Get pending migrations
        $this->io->write("\n<info>Fetching available migrations...</info>");
        $this->pending = $this->getPendingMigrations();

        $this->io->debug("\n<info>Pending migrations:</info>");
        $this->io->debug($this->pending->toArray());

        // Checkpoint n° 1
        // If there's no pending migration, don't need to go further
        if ($this->pending->isEmpty()) {
            $this->io->write("\n<fg=black;bg=green>Nothing to migrate !</>\n");
            return;
        }

        // Reset fulfillable/unfulfillable lists
        $this->fulfillable = collect([]);
        $this->unfulfillable = collect([]);

        // Loop pending and check for dependencies
        foreach ($this->pending as $migration) {
            $this->checkDependencies($migration);
        }

        $this->io->debug("\n<info>Fulfillable migrations:</info>");
        $this->io->debug($this->fulfillable->toArray());

        $this->io->debug("\n<info>Unfulfillable migrations:</info>");
        $this->io->debug($this->unfulfillable->toArray());

        // If there are any unfulfillable migration, we can't continue
        if (!$this->unfulfillable->isEmpty()) {
            $this->io->write("\n<error>Some migrations dependencies can't be met. Check those migrations for unmet dependencies and try again:</error>");
            $this->io->write($this->unfulfillable->toArray());
            exit(1);
        }

        $this->io->write("\n<info>Running migrations...</info>");

        // We have a list of fulfillable migration, we run them up!
        foreach ($this->fulfillable as $migration) {

            $this->io->write("> $migration");

            // Running up
            //$migrationClass = new $migration($this->schema);
            //$migrationClass->up();

            // Log that migrations
            //$this->log($migration, d'oh);
        }

        // If all went well and there's no fatal errors, we are ready to bake
        $this->io->write("\n<fg=black;bg=green>Migration successful !</>\n");
    }

    /**
     * Get a list of all ran migration from the database history
     * Return the list grouped by sprinkles
     *
     * @access protected
     * @return void
     */
    protected function getInstalledMigrations()
    {
        // Make sure the setup table exist
        $this->setupVersionTable();

        // Load from the database
        $migrations = Migrations::orderBy('created_at', 'asc')->get();

        // Load the list of ran migrations, pluck by sprinkle and class name
        return $migrations->pluck('migration');
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

        // Load sprinkles if not already done
        if (empty($this->sprinkles)) {
            $this->loadSprinkles();
        }

        // Loop all the sprinkles to find their pending migrations
        foreach ($this->sprinkles as $sprinkle) {

            $sprinkleName = Str::studly($sprinkle);

            $this->io->write("- Fetching from `$sprinkleName` sprinkle");

            // We get all the available migrations class
            $availableMigrations = $this->getMigrationsClass($sprinkle);

            // We filter the available migration by removing the one that have already been run
            $newMigrations = $availableMigrations->reject(function ($value, $key) {
                return $this->installed->contains($value);
            });

            //Merge the filtered migrations back into "pending"
            $pending = $pending->merge($newMigrations);
        }

        return $pending;
    }

    /**
     * Get the list of all sprinkles.
     *
     * @access protected
     * @return void
     */
    protected function loadSprinkles()
    {
        $this->sprinkles = $this->ci->sprinkleManager->getSprinkleNames();
    }

    /**
     * Get the list of migrations avaiables in the filesystem.
     * Return a list of resolved className
     *
     * @access public
     * @param mixed $sprinkleName
     * @return void
     */
    public function getMigrationsClass($sprinkle)
    {
        // Find all the migration files
        $path = $this->migrationDirectoryPath($sprinkle);
        $files = glob($path . "*/*.php");

        // Transform the array in a collection
        $migrations = collect($files);

        // We transform the path into a migration object
        $migrations->transform(function ($item, $key) use ($path, $sprinkle) {

            // Deconstruct the path
            $migration = str_replace($path, "", $item);
            $className = basename($item, '.php');
            $sprinkleName = Str::studly($sprinkle);
            $version = str_replace("/$className.php", "", $migration);

            // Reconstruct the classname
            $className = "\\UserFrosting\\Sprinkle\\".$sprinkleName."\\Model\Migrations\\".$version."\\".$className;

            // Make sure the class exist
            if (!class_exists($className)) {
                throw new BadClassNameException("Unable to find the migration class '$className'." );
            }

            return $className;
        });

        return $migrations;
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
    protected function checkDependencies($migration)
    {
        // If it's already marked as fulfillable, it's fulfillable
        // Return true directly, it's already marked
        if ($this->fulfillable->contains($migration)) {
            return true;
        }

        // If it's already marked as unfulfillable, it's unfulfillable
        // Return true directly, it's already marked
        if ($this->unfulfillable->contains($migration)) {
            return false;
        }

        // If it's already run, it's fulfillable
        if ($this->installed->contains($migration)) {
            return $this->markAsFulfillable($migration);
        }

        // If class is in neither of those, we check it's in pending.
        // If it's not, then it certainly doesn't exist and it's unfulfillable
        // Since it's a dependencies that doesn't exist, we won't add it to the list
        // of unfulfillable migration (it's not a migration to begin with)
        if (!$this->pending->contains($migration)) {
            return false;
        }

        // Loop dependencies. If one is not fulfillable, then this one is not either
        foreach ($migration::dependencies() as $dependency) {
            if (!$this->checkDependencies($dependency)) {
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
        $this->pending->pull($migration);
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
        $this->pending->pull($migration);
        $this->unfulfillable->push($migration);
        return false;
    }

    /**
     * Log that a migration was run.
     *
     * @access public
     * @param string $sprinkle
     * @param string $version
     * @return void
     */
    protected function log($migration, $sprinkleName)
    {
        // Get the next batch number if not defined
        if (!$this->batch) {
            $this->batch = $this->getNextBatchNumber();
        }

        new Migrations([
            'sprinkle' => $sprinkleName,
            'migration' => $migration,
            'batch' => $this->batch
        ]);
    }

    /**
     * Remove a migration from the log.
     *
     * @access public
     * @param mixed $migration
     * @return void
     */
    public function delete($migration)
    {
        //TODO
    }

    public function getNextBatchNumber()
    {
        return Migrations::max('batch');
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
        // Temp, for debug & tests
        //$migration = new \UserFrosting\System\Bakery\Migrations\v410\MigrationTable($this->schema);
        //$migration->down();

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