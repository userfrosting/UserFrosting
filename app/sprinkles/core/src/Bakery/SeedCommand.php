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
use UserFrosting\Sprinkle\Core\Database\Seeder\Seeder;
use UserFrosting\Sprinkle\Core\Bakery\Helper\ConfirmableTrait;
use UserFrosting\System\Bakery\BaseCommand;

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
     * @var Seeder $seeder
     */
    protected $seeder;

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("UserFrosting's Seeder");

        // Prepare seed locator
        $this->seeder = $this->ci->seeder;

        // Get options
        $classes = $input->getArgument('class');

        // Seeds list
        $seeds = [];

        // Start by gettings seeds
        foreach ($classes as $className) {

            // Get seed class and
            try {
                $seedClass = $this->seeder->getSeedClass($className);
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());
                exit(1);
            }

            // Display the class we are going to use as info
            $this->io->writeln('<info>Seeding database using class `'.get_class($seedClass).'`</>');

            // Add seed class to list
            $seeds[] = $seedClass;
        }

        // Confirm action when in production mode
        if (!$this->confirmToProceed($input->getOption('force'))) {
            exit(1);
        }

        // Run seeds
        foreach ($seeds as $seed) {
            try {
                $this->seeder->executeSeed($seed);
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());
                exit(1);
            }
        }

        // Success
        $this->io->success('Seed successful !');
    }
}
