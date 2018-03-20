<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Integration;

use Mockery as m;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use UserFrosting\Sprinkle\Core\Bakery\MigrateRollbackCommand;
use UserFrosting\Tests\TestCase;

class BakeryMigrateRollbackCommandTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testBasicMigrationsCallMigratorWithProperArguments()
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('rollback')->once()->with(['pretend' => false, 'steps' => 1])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $commandTester = $this->runCommand($migrator, []);
    }

    public function testMigrationRepositoryCreatedWhenNecessary()
    {
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $repository = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository');

        $migrator->shouldReceive('repositoryExists')->once()->andReturn(false);
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);
        $migrator->shouldReceive('rollback')->once()->with(['pretend' => false, 'steps' => 1])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        $repository->shouldReceive('createRepository')->once();

        // Run command
        $commandTester = $this->runCommand($migrator, []);
    }

    public function testTheCommandMayBePretended()
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('rollback')->once()->with(['pretend' => true, 'steps' => 1])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $commandTester = $this->runCommand($migrator, ['--pretend' => true]);
    }

    public function testStepsMayBeSet()
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('rollback')->once()->with(['pretend' => false, 'steps' => 3])->andReturn([]);
        $migrator->shouldReceive('getNotes');

        // Run command
        $commandTester = $this->runCommand($migrator, ['--steps' => 3]);
    }

    protected function runCommand($migrator, $input = [])
    {
        // Place the mock migrator inside the $ci
        $ci = $this->ci;
        $ci->migrator = $migrator;

        // Create the app, create the command, replace $ci and add the command to the app
        $app = new Application();
        $command = new MigrateRollbackCommand();
        $command->setContainer($ci);
        $app->add($command);

        // Add the command to the input to create the execute argument
        $execute = array_merge([
            'command' => $command->getName()
        ], $input);

        // Execute command tester
        $commandTester = new CommandTester($command);
        $commandTester->execute($execute);

        return $commandTester;
    }
}
