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
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Base class for UserFrosting Bakery CLI tools.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
abstract class Bakery
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var string
     */
    protected $projectRoot;

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
     * autoload function.
     *
     * @access private
     * @return void
     */
    private function autoload()
    {
        // Require composer autoload file. Not having this file means Composer might not be installed / run
        if (!file_exists($this->projectRoot . 'app/vendor/autoload.php')) {
            $this->io->write("<error>ERROR :: File `app/vendor/autoload.php` not found. This indicate that composer has not yet been run on this install. Install composer and run `composer install` from the `app/` directory. Check the documentation for more details.</error>");
            exit(1);
        } else {
            require_once $this->projectRoot . 'app/vendor/autoload.php';
        }
    }
}