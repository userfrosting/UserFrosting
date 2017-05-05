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
use UserFrosting\System\UserFrosting;

/**
 * Assets builder CLI Tools.
 * Wrapper for npm/node commands
 *
 * @extends Bakery
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Assets extends Bakery
{
    /**
     * @var string Path to the build/ directory
     */
    protected $buildPath;

    /**
     * Run the `build-assets` composer script
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
        $bakery->io->write("\n<info>/*********************************/\n/* UserFrosting's Assets Builder */\n/*********************************/</info>");

        // Set $path
        $bakery->buildPath = $bakery->projectRoot . "build";

        // Perform tasks
        $bakery->npmInstall();
        $bakery->assetsInstall();
        $bakery->checkAssets();

        // If all went well and there's no fatal errors, we are ready to bake
        $bakery->io->write("\n<fg=black;bg=green>Assets install looks successful !</>\n");
    }

    /**
     * Install npm package
     *
     * @access protected
     * @return void
     */
    protected function npmInstall()
    {
        $this->io->write("<info>Installing npm dependencies</info>");
        $this->io->write("> npm install");
        passthru("npm install --prefix " . $this->buildPath);
    }

    /**
     * Perform UF Assets installation
     *
     * @access protected
     * @return void
     */
    protected function assetsInstall()
    {
        $this->io->write("\n<info>Installing assets bundles</info>");
        $this->io->write("> npm run uf-assets-install");
        passthru("npm run uf-assets-install --prefix " . $this->buildPath);
    }

    /**
     * Check that the assets where installed in the core sprinkles
     *
     * @access protected
     * @return void
     */
    protected function checkAssets()
    {
        $this->io->write("\n<info>Testing assets installation</info>");

        // Get path and vendor files
        $vendorPath = \UserFrosting\APP_DIR . '/' . \UserFrosting\SPRINKLES_DIR_NAME . "/core/assets/vendor/*";
        $coreVendorFiles = glob($vendorPath);

        if (!$coreVendorFiles){
            $this->io->write("\n<error>FATAL ERROR :: NPM bundle failed. Directory `$vendorPath` is empty.</error>");
            exit(1);
        }
    }
}