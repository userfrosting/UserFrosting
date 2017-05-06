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
use UserFrosting\System\Bakery\Bakery;
use UserFrosting\System\Bakery\Debug;
use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Model\Version;

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
     * @var DB Schema
     */
    protected $schema;

     /**
     * @var table The name of the migration table
     */
    protected $table = "version";

    /**
     * Run the `migrate` composer script
     *
     * @access public
     * @static
     * @param Event $event
     * @return void
     */
    public static function run(Event $event)
    {
        $bakery = new self($event->getIO(), $event->getComposer());

        // Display header,
        $bakery->io->write("\n<info>/****************************/\n/* UserFrosting's Migration */\n/****************************/</info>");

        // First we need to container to load all the sprinkles
        $bakery->getContainer();

        // Start by testing the DB connexion, just in case
        $bakery->testDB();

        // Get schema
        $bakery->schema = Capsule::schema();

        // Setup version table
        $bakery->setupVersionTable();

        // Get loaded sprinkles
        $sprinkles = $bakery->ci->sprinkleManager->getSprinkleNames();

        // If all went well and there's no fatal errors, we are ready to bake
        $bakery->io->write("\n<fg=black;bg=green>Migrated successfully !</>\n");
    }

    /**
     * Get the ran migrations.
     *
     * @access public
     * @return void
     */
    public function getRan()
    {
        return Version::orderBy('created_at', 'asc')->all();
    }

    /**
     * Get list of migrations for a given sprinkle
     *
     * @access public
     * @param int $steps (default: 1)
     * @param string $sprinkle (default: "")
     * @return void
     */
    public function getMigrations($steps = 1, $sprinkle = "")
    {
        $query = Version::orderBy('created_at', 'asc');

        if ($sprinkle != "") {
            $query = $query->forSprinkle($sprinkle);
        }

        return $query->take($steps)->get()->all();
    }

    /**
     * Log that a migration was run.
     *
     * @access public
     * @param string $sprinkle
     * @param string $version
     * @return void
     */
    public function log($sprinkle, $version)
    {
        new Version([
            'sprinkle' => $sprinkle,
            'version' => $version
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

    /**
     * Get the last migration batch number.
     *
     * @access public
     * @return void
     */
    public function getLastVersionNumber()
    {
        //TODO
    }

    /**
     * Create the migration history table if needed.
     * Also check if the tables requires migrations
     * We run the migration file manually for this one
     *
     * @access public
     * @return void
     */
    public function setupVersionTable()
    {
        $this->io->write("\n<info>Looking for the `{$this->table}` table...</info>");

        $messages = [];

        // Run 4.0.0 migration
        if (!$this->schema->hasTable($this->table)) {
            $migration = new \UserFrosting\System\Bakery\Migrations\Version\V4_0_0_Migration;
            $migration->up($this->schema);
            $messages[] = "Table `{$this->table}` created successfully";
        }

        // Run 4.1.0 migration
        if (!$this->schema->hasColumn($this->table, 'id')) {
            $migration = new \UserFrosting\System\Bakery\Migrations\Version\V4_1_0_Migration;
            $migration->up($this->schema);
            $messages[] = "Table `{$this->table}` migrated to `4.1.0` successfully";
        }

        // Show message if no migrations were run
        if (empty($messages)) {
            $messages[] = "Table `{$this->table}` found and up to date";
        }

        // Write messages
        $this->io->write($messages);
    }
}