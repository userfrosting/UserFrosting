<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\System\Bakery\BaseCommand;

class SetupFontAwesomeCommand extends BaseCommand
{
  /**
   * @var string Path to the build/ directory
   */
  protected $buildPath = \UserFrosting\ROOT_DIR . \UserFrosting\DS . \UserFrosting\BUILD_DIR_NAME. '/.npmrc';
  /**
   * {@inheritdoc}
   */
    protected function configure()
    {
        $this->setName("setup:font-awesome");

        // the short description shown while running "php bakery list"
        $this->setDescription("Switch between free/pro versions of Font Awesome");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Display header,
        $this->io->title("UserFrosting's Font Awesome Setup Wizard");
        $this->io->note("Font Awesome authentication key will be saved in `{$this->buildPath}`");
        $this->io->write('Please enter your Font Awesome authentication key. This will be used to download the neccessary packages.');

        // Ask for Font Awesome authentication key
        $newAuthKey = $this->io->ask('What is Font Awesome Authentication key?');

        // Set $buildPath. We'll use the aboslute path for this task
        $this->buildPath = \UserFrosting\ROOT_DIR . \UserFrosting\DS . \UserFrosting\BUILD_DIR_NAME;

        // Temporarily change the working directory (more reliable than --prefix npm switch)
        $wd = getcwd();
        chdir($this->buildPath);


        // Delete any Font Awesome settings in .npmrc but leave other lines untouched.
        passthru("sed '/npm.fontawesome.com/d' -i .npmrc");

        // Add the registry setting and new auth key to .npmc.
        passthru("printf '%s\n' '@fortawesome:registry=https://npm.fontawesome.com/' '//npm.fontawesome.com/:_authToken=$newAuthKey' >>.npmrc");

        // Install Font Awesome Pro Package.
        $this->io->writeln('> <comment>npm install --save-dev @fortawesome/fontawesome-pro</comment>');
        passthru('npm install --save-dev @fortawesome/fontawesome-pro');

        chdir($wd);
    }
}
