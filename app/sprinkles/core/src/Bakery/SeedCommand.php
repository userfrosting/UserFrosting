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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\Sprinkle\Core\Database\Seeder\SeederInterface;
use UserFrosting\Sprinkle\Core\Util\ClassFinder;
use UserFrosting\System\Bakery\BaseCommand;
use UserFrosting\System\Bakery\ConfirmableTrait;

/**
 * seed Bakery Command
 * Perform a database seed
 *
 * @author Louis Charette
 */
class SeedCommand extends BaseCommand
{
    use ConfirmableTrait;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('seed')
             ->setDescription('Seed the database with records')
             ->setHelp('This command runs a seed to populate the database with default, random and/or test data.')
             ->addArgument('class', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'The class name of the seeder. Separate multiple seeder with a space.')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to run when in production.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("UserFrosting's Seeder");

        // Get options
        $classes = $input->getArgument('class');

        foreach ($classes as $className) {
            $this->runSeed($className, $input);
        }

        // Success
        $this->io->success('Seed successful !');
    }

    /**
     *    Run seed
     *
     *    @param  string $className Seed classname (argument from console)
     *    @param  InputInterface $input
     *    @return void
     */
    protected function runSeed($className, InputInterface $input)
    {
        // Get the class instance
        $seed = $this->getSeed($className);

        // Display the class we are going to use as info
        $this->io->writeln("<info>Seeding class `".get_class($seed)."`</>");

        // Confirm action when in production mode
        if (!$this->confirmToProceed($input->getOption('force'))) {
            exit(1);
        }

        // TODO ::
        //   - Disable Model guarded policy
        //   - Create seeder:list command/options
        //   - Create default seeds list/service

        // Run seed
        try {
            $seed->run();
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());
            exit(1);
        }
    }

    /**
     *    Setup migrator and the shared options between other command
     *
     *    @param  string $name The seeder name
     *    @return mixed The seeder class instance
     */
    protected function getSeed($name)
    {
        $finder = new ClassFinder($this->ci->sprinkleManager);

        try {
            $class = $finder->getClass("Database\\Seeder\\$name");
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());
            exit(1);
        }

        $seedClass = new $class($this->ci);

        // Class must be an instance of `SeederInterface`
        if (!$seedClass instanceof SeederInterface) {
            $this->io->error('Seed class must be an instance of `SeederInterface`');
            exit(1);
        }

        return $seedClass;
    }
}
