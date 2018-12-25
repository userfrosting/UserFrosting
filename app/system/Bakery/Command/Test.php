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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use UserFrosting\System\Bakery\BaseCommand;

/**
 * Automated testing CLI tool.
 * Sets up environment and runs PHPUnit tests in each Sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Test extends BaseCommand
{
    /**
     * @var string Path to the build/ directory
     */
    protected $buildPath;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('test')
            ->addOption('coverage', 'c', InputOption::VALUE_NONE, 'Generate code coverage report in HTML format. Will be saved in _meta/coverage')
            ->addArgument('sprinkle', InputArgument::OPTIONAL, 'Sprinkle Name (Optional): ')
            ->setDescription('Run tests, Optionally Specify Sprinkle name')
            ->setHelp('Run php unit tests, Optionally Specify Sprinkle name');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("UserFrosting's Tester");
        $this->io->text("Optionally Specify Sprinkle name as an argument");
        $this->io->newLine();

        // Get command
        $command = \UserFrosting\VENDOR_DIR . '/bin/phpunit --colors=always';
        if ($output->isVerbose() || $output->isVeryVerbose()) {
            $command .= ' -v';
        }

        $sprinkle = $input->getArgument('sprinkle');
        if ($sprinkle) {
            $slashes = " \\\\ ";
            $command .= " --filter='UserFrosting" . trim($slashes) . "Sprinkle" . trim($slashes) . $sprinkle . trim($slashes) . "Tests" . trim($slashes) . "' ";
        }

        // Add coverage report
        if ($input->getOption('coverage')) {
            $command .= ' --coverage-html _meta/coverage';
        }

        // Execute
        $this->io->writeln("> <comment>$command</comment>");
        passthru($command);
    }
}
