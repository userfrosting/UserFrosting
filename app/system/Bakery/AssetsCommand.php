<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\System\Bakery\Bakery;

/**
 * Assets builder CLI Tools.
 * Wrapper for npm/node commands
 *
 * @extends Bakery
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AssetsCommand extends Bakery
{
    /**
     * @var string Path to the build/ directory
     */
    protected $buildPath;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("build-assets")
             ->setDescription("Build the assets using node and npm")
             ->setHelp("The build directory contains the scripts and configuration files required to download Javascript, CSS, and other assets used by UserFrosting. This command will install Gulp, Bower, and several other required npm packages locally. With <info>npm</info> set up with all of its required packages, it can be use it to automatically download and install the assets in the correct directories. For more info, see <comment>https://learn.userfrosting.com/basics/installation</comment>")
             ->addOption("compile", "c", InputOption::VALUE_NONE, "Compile the assets and asset bundles for production environment");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Display header,
        $this->io->title("UserFrosting's Assets Builder");

        // Set $path
        $this->buildPath = $this->projectRoot . "build";

        // Perform tasks
        $this->npmInstall();
        $this->assetsInstall();

        // Compile if requested
        if ($input->getOption('compile')) {
            $this->buildAssets();
        }

        // Test the result
        $this->checkAssets();

        // If all went well and there's no fatal errors, we are successful
        $this->io->success("Assets install looks successful");
    }

    /**
     * Install npm package
     *
     * @access protected
     * @return void
     */
    protected function npmInstall()
    {
        $this->io->section("<info>Installing npm dependencies</info>");
        $this->io->writeln("> <comment>npm install</comment>");
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
        $this->io->section("Installing assets bundles");
        $this->io->writeln("> <comment>npm run uf-assets-install</comment>");
        passthru("npm run uf-assets-install --prefix " . $this->buildPath);
    }

    protected function buildAssets()
    {
        $this->io->section("Building assets");

        $this->io->writeln("> <comment>npm run uf-bundle-build</comment>");
        passthru("npm run uf-bundle-build --prefix " . $this->buildPath);

        $this->io->writeln("> <comment>npm run uf-bundle</comment>");
        passthru("npm run uf-bundle --prefix " . $this->buildPath);

        $this->io->writeln("> <comment>npm run uf-bundle-clean</comment>");
        passthru("npm run uf-bundle-clean --prefix " . $this->buildPath);
    }

    /**
     * Check that the assets where installed in the core sprinkles
     *
     * @access protected
     * @return void
     */
    protected function checkAssets()
    {
        $this->io->section("Testing assets installation");

        // Get path and vendor files
        $vendorPath = \UserFrosting\APP_DIR . '/' . \UserFrosting\SPRINKLES_DIR_NAME . "/core/assets/vendor/*";
        $coreVendorFiles = glob($vendorPath);

        if (!$coreVendorFiles){
            $this->io->error("NPM bundle failed. Directory `$vendorPath` is empty.");
            exit(1);
        }
    }
}