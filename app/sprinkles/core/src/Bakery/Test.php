<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Illuminate\Support\Str;
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
             ->addArgument('testscope', InputArgument::OPTIONAL, "Test Scope can either be a sprinkle name or a class formated as 'SprinkleName\Tests\TestClass` or 'SprinkleName\Tests\TestClass::method` (Optional)")
             ->setDescription('Runs automated tests')
             ->setHelp("Run PHP unit tests. Tests from a specific sprinkle can optionally be run using the 'testscope' argument (`php bakery test SprinkleName`). A specific test class can also be be run using the testscope argument (`php bakery test 'SprinkleName\Tests\TestClass'`), as a specific test method (`php bakery test 'SprinkleName\Tests\TestClass::method'`).");
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
            $slashes = '\\\\';
            if (strpos($testscope, '\\') !== false) {
                $this->io->note("Executing Specified Test Scope : $testscope");
                $testscope = str_replace('\\', $slashes, $testscope);
                $command .= " --filter='UserFrosting" . $slashes . 'Sprinkle' . $slashes . $testscope . "'";
            } else {
                $this->io->note("Executing all tests in Sprinkle '".Str::studly($testscope)."'");
                $command .= " --filter='UserFrosting" . $slashes . 'Sprinkle' . $slashes . Str::studly($testscope) . $slashes . 'Tests' . $slashes . "' ";
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
