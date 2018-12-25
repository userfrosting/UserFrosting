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
            ->addArgument('testscope', InputArgument::OPTIONAL, 'Test Scope :Sprinkle, Class and Medhod  Name (Optional): ')
            ->setDescription('Run tests, Optionally sprcific Sprinkle, Class and Medhod ')
            ->setHelp('Run php unit tests, Optionally specif Sprinkle, Class and Medhod name');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("UserFrosting's Tester");

        // Get command
        $command = \UserFrosting\VENDOR_DIR . '/bin/phpunit --colors=always';
        if ($output->isVerbose() || $output->isVeryVerbose()) {
            $command .= ' -v';
        }

        $testscope = $input->getArgument('testscope');
        if ($testscope) {
            $slashes = " \\\\ ";
            if (strpos($testscope, "\\") !== false) {
                $this->io->writeln("> <comment>Executing Specified Test Scope $testscope</comment>");
                $testscope1 = str_replace("\\", trim($slashes), $testscope);
                $command .= " --filter='UserFrosting" . trim($slashes) . "Sprinkle" . trim($slashes) . $testscope1 . "'";
            } else {
                $this->io->writeln("> <comment>Executing all tests in Sprinkle $testscope</comment>");
                $command .= " --filter='UserFrosting" . trim($slashes) . "Sprinkle" . trim($slashes) . $testscope . trim($slashes) . "Tests" . trim($slashes) . "' ";
            }
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
