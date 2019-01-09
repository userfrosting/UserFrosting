<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
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
             ->setDescription('Run tests')
             ->setHelp('Run php unit tests');
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

        // Add coverage report
        if ($input->getOption('coverage')) {
            $command .= ' --coverage-html _meta/coverage';
        }

        // Execute
        $this->io->writeln("> <comment>$command</comment>");
        passthru($command);
    }
}
