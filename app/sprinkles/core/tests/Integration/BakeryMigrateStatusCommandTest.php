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
use UserFrosting\Sprinkle\Core\Bakery\MigrateStatusCommand;
use UserFrosting\Tests\TestCase;

class BakeryMigrateStatusCommandTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testBasicMigrationsCallMigratorWithProperArguments()
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $repository = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository');

        // Define dummy data
        $available = ['foo', 'bar', 'oof', 'rab'];
        $installed = ['foo', 'bar'];
        $pending = ['oof', 'rab'];

        // Set expectations
        $migrator->shouldReceive('setConnection')->once()->with(null)->andReturn(null);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);
        $migrator->shouldReceive('getAvailableMigrations')->once()->andReturn($available);
        $migrator->shouldReceive('pendingMigrations')->once()->with($available, $installed)->andReturn($pending);

        $repository->shouldReceive('getRan')->once()->andReturn($installed);

        // Run command
        $commandTester = $this->runCommand($migrator, []);
    }

    public function testDatabaseMayBeSet()
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $repository = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository');

        // Define dummy data
        $available = ['foo', 'bar', 'oof', 'rab'];
        $installed = ['foo', 'bar'];
        $pending = ['oof', 'rab'];

        // Set expectations
        $migrator->shouldReceive('setConnection')->once()->with('test')->andReturn(null);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);
        $migrator->shouldReceive('getAvailableMigrations')->once()->andReturn($available);
        $migrator->shouldReceive('pendingMigrations')->once()->with($available, $installed)->andReturn($pending);

        $repository->shouldReceive('getRan')->once()->andReturn($installed);

        // Run command
        $commandTester = $this->runCommand($migrator, ['--database' => 'test']);
    }

    protected function runCommand($migrator, $input = [])
    {
        // Place the mock migrator inside the $ci
        $ci = $this->ci;
        $ci->migrator = $migrator;

        // Create the app, create the command, replace $ci and add the command to the app
        $app = new Application();
        $command = new MigrateStatusCommand();
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
