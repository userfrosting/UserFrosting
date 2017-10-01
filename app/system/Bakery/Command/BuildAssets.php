<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\System\Bakery\BaseCommand;

/**
 * Assets builder CLI Tools.
 * Wrapper for npm/node commands
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class BuildAssets extends BaseCommand
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
             ->addOption("compile", "c", InputOption::VALUE_NONE, "Compile the assets and asset bundles for production environment")
             ->addOption("force", "f", InputOption::VALUE_NONE, "Force assets compilation by deleting cached data and installed assets before proceeding");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Display header,
        $this->io->title("UserFrosting's Assets Builder");

        // Set $path
        $this->buildPath = $this->projectRoot . \UserFrosting\DS . \UserFrosting\BUILD_DIR_NAME;

        // Delete cached data is requested
        if ($input->getOption('force')) {
            $this->clean();
        }

        // Perform tasks
        $this->npmInstall();
        $this->assetsInstall();

        // Compile if requested
        if ($input->getOption('compile') || $this->isProduction()) {
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

        // Temporarily change the working directory so we can install npm dependencies
        $wd = getcwd();
        chdir($this->buildPath);
        passthru("npm install");
        chdir($wd);
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

    /**
     * Build the production bundle.
     *
     * @access protected
     * @return void
     */
    protected function buildAssets()
    {
        $this->io->section("Building assets for production");

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
        $vendorPath = \UserFrosting\SPRINKLES_DIR . "/core/assets/vendor/*";
        $coreVendorFiles = glob($vendorPath);

        if (!$coreVendorFiles){
            $this->io->error("Assets installation seems to have failed. Directory `$vendorPath` is empty, but it shouldn't be. Check the above log for any errors.");
            exit(1);
        }

        // Check that `bundle.result.json` is present in production mode
        $config = $this->ci->config;
        $resultFile = \UserFrosting\ROOT_DIR . \UserFrosting\DS . \UserFrosting\BUILD_DIR_NAME . \UserFrosting\DS . $config['assets.compiled.schema'];
        if ($this->isProduction() && !file_exists($resultFile)) {
            $this->io->error("Assets building seems to have failed. File `$resultFile` not found. This file is required for production envrionement. Check the above log for any errors.");
            exit(1);
        }
    }

    /**
     * Run the `uf-clean` command to delete installed assets, delete compiled
     * bundle config file and delete compiled assets
     *
     * @access protected
     * @return void
     */
    protected function clean()
    {
        $this->io->section("Cleaning cached data");
        $this->io->writeln("> <comment>npm run uf-clean</comment>");
        passthru("npm run uf-clean --prefix " . $this->buildPath);
    }

    /**
     * Return if the app is in production mode
     *
     * @access protected
     * @return bool
     */
    protected function isProduction()
    {
        // N.B.: Need to touch the config service first to load dotenv values
        $config = $this->ci->config;
        $mode = getenv("UF_MODE") ?: '';

        return ($mode == "production");
    }
}