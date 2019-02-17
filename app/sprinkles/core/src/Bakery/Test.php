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
     * @var string
     */
    protected $slashes = '\\';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('test')
             ->addOption('coverage', 'c', InputOption::VALUE_NONE, 'Enable code coverage report.')
             ->addOption('coverage-format', null, InputOption::VALUE_REQUIRED, 'Select test coverage format. Choose from html, clover, crap4j, php, text, xml, etc. Default to HTML.')
             ->addOption('coverage-path', null, InputOption::VALUE_REQUIRED, 'Code coverage report saving location. Default to `_meta/coverage`.')
             ->addArgument('testscope', InputArgument::OPTIONAL, 'Test Scope can either be a sprinkle name or a test class (optional).')
             ->setDescription('Runs automated tests')
             ->setHelp("Run PHP unit tests. Tests from a specific sprinkle can optionally be run using the 'testscope' argument (eg. `php bakery test SprinkleName`). A specific test class can also be run using the testscope argument (eg. `php bakery test 'UserFrosting\Sprinkle\SprinkleName\Tests\TestClass'`), as a specific test method (eg. `php bakery test 'UserFrosting\Sprinkle\SprinkleName\Tests\TestClass::method'`).");
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

        // Process test scope
        $testscope = $input->getArgument('testscope');
        if ($testscope) {
            if (strpos($testscope, $this->slashes) !== false) {
                $command .= $this->parseTestScope($testscope);
            } else {
                $command .= $this->parseSprinkleTestScope($testscope);
            }
        }

        // Add coverage report
        if ($input->getOption('coverage') || $input->getOption('coverage-format') || $input->getOption('coverage-path')) {
            $format = ($input->getOption('coverage-format')) ?: 'html';
            $path = ($input->getOption('coverage-path')) ?: '_meta/coverage';

            switch ($format) {
                case 'clover':
                case 'xml':
                case 'crap4j':
                    $path = $path . '/coverage.xml';
                    break;
                case 'php':
                    $path = $path . '/coverage.php';
                    break;
                case 'text':
                    $path = '';
                    break;
                case 'html':
                default:
                    $file = '';
                    break;
            }

            $command .= " --coverage-$format $path";
        }

        // Execute
        $this->io->writeln("> <comment>$command</comment>");
        passthru($command);
    }

    /**
     * Return the sprinkle test class
     *
     * @param  string $testscope Testscope received from command line
     * @return string
     */
    protected function parseSprinkleTestScope($testscope)
    {
        /** @var \UserFrosting\System\Sprinkle\SprinkleManager $sprinkleManager */
        $sprinkleManager = $this->ci->sprinkleManager;

        // Get the Sprinkle name from the SprinkleManager, as we need the correct case
        $sprinkle = $sprinkleManager->getSprinkle($testscope);

        // Make sure sprinkle exist
        if (!$sprinkle) {
            $this->io->error("Sprinkle $testscope not found");
            exit(1);
        }

        $sprinkleName = Str::studly($sprinkle);
        $this->io->note("Executing all tests for Sprinkle '$sprinkleName'");

        // Check if sprinkle has phpunit.xml file
        $phpunitConfig = $sprinkleManager->getSprinklePath($sprinkle) . \UserFrosting\DS . 'phpunit.xml';
        if (file_exists($phpunitConfig)) {
            return " -c $phpunitConfig ";
        }

        // Add command part
        $testClass = $sprinkleManager->getSprinkleClassNamespace($sprinkleName) . "\Tests";
        $testClass = str_replace($this->slashes, $this->slashes . $this->slashes, $testClass);

        return " --filter='$testClass' ";
    }

    /**
     * Parse testscope for
     * @param  string $testscope
     * @return string string to append to command
     */
    protected function parseTestScope($testscope)
    {
        $this->io->note("Executing Specified Test Scope : $testscope");
        $testscope = str_replace($this->slashes, $this->slashes . $this->slashes, $testscope);

        return " --filter='$testscope'";
    }
}
