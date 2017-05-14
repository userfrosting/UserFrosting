<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use UserFrosting\System\UserFrosting;

/**
 * Base class for UserFrosting Bakery CLI tools.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
abstract class Bakery
{
    /**
     * @var @Composer\Composer
     */
    protected $composer;

    /**
     * @var @Composer\IO\IOInterface
     */
    protected $io;

    /**
     * @var string Path to the project root folder
     */
    protected $projectRoot;

    /**
     * @var ContainerInterface $ci The global container object, which holds all of UserFristing services.
     */
    protected $ci;

    /**
     * @param IOInterface $io
     * @param Composer $composer
     */
    public function __construct(IOInterface $io, Composer $composer)
    {
        $this->io = $io;
        $this->composer = $composer;

        // Get composer.json location
        $composerFile = Factory::getComposerFile();

        // Calculate project root from composer.json, if necessary
        $this->projectRoot = realpath(dirname($composerFile));
        $this->projectRoot = rtrim($this->projectRoot, '/\\') . '/';

        // Autoload UF stuff
        $this->autoload();
    }

    /**
     * Load the composer autoload file
     * This is not loaded by default, even when running CLI from composer
     *
     * @access private
     * @return void
     */
    private function autoload()
    {
        // Require composer autoload file. Not having this file means Composer might not be installed / run
        if (!file_exists($this->projectRoot . 'app/vendor/autoload.php')) {
            $this->io->error("ERROR :: File `app/vendor/autoload.php` not found. This indicate that composer has not yet been run on this install. Install composer and run `composer install` from the `app/` directory. Check the documentation for more details.");
            exit(1);
        } else {
            require_once $this->projectRoot . 'app/vendor/autoload.php';
        }
    }

    /**
     * Function that set the UF container, loading all the sprinkles in the process
     * This is not loaded by default by the Bakery, since some commands doesn't requires it
     * And it may also cause error to define it too early in the install/debug process
     *
     * @access protected
     * @return void
     */
    protected function getContainer()
    {
        // Setup the sprinkles
        $uf = new UserFrosting();

        // Set argument as false, we are using the CLI
        $uf->setupSprinkles(false);

        // Get the container
        $this->ci = $uf->getContainer();
    }

    /**
     * Process the arguments passed with the composer run-script and return them in a nice collection.
     *
     * @access protected
     * @param @Composer\Script\Event $event
     * @return void
     */
    protected function getArguments(Event $event)
    {
        $args = collect($event->getArguments());
        $args = $args->mapWithKeys(function ($item) {
            $item = explode("=", $item);
            $arg = $item[0];
            $param = (count($item) > 1) ? $item[1] : true;

            return [$arg => $param];
        });

        return $args;
    }
}