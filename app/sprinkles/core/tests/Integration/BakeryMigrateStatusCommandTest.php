<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration;

use Mockery as m;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use UserFrosting\Sprinkle\Core\Bakery\MigrateStatusCommand;
use UserFrosting\Tests\TestCase;

class BakeryMigrateStatusCommandTest extends TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testBasicMigrationsCallMigratorWithProperArguments()
    {
        // Setup migrator mock
        $migrator = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\Migrator');
        $repository = m::mock('UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository');

        // Define dummy data
        $available = ['foo', 'bar', 'oof', 'rab'];
        $installed = $this->getInstalledMigrationStub()->pluck('migration')->all();
        $pending = ['oof', 'rab'];

        // Set expectations
        $migrator->shouldReceive('setConnection')->once()->with(null)->andReturn(null);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);
        $migrator->shouldReceive('getAvailableMigrations')->once()->andReturn($available);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn($pending);

        $repository->shouldReceive('getMigrations')->once()->andReturn($this->getInstalledMigrationStub());

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
        $installed = $this->getInstalledMigrationStub()->pluck('migration')->all();
        $pending = ['oof', 'rab'];

        // Set expectations
        $migrator->shouldReceive('setConnection')->once()->with('test')->andReturn(null);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('getRepository')->once()->andReturn($repository);
        $migrator->shouldReceive('getAvailableMigrations')->once()->andReturn($available);
        $migrator->shouldReceive('getPendingMigrations')->once()->andReturn($pending);

        $repository->shouldReceive('getMigrations')->once()->andReturn($this->getInstalledMigrationStub());

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

    protected function getInstalledMigrationStub()
    {
        return collect([
            (object) ['migration' => 'foo', 'batch' => 1, 'sprinkle' => 'foo'],
            (object) ['migration' => 'bar', 'batch' => 2, 'sprinkle' => 'bar']
        ]);
    }
}
