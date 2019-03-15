<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Sprinkle\Core\Bakery\Helper\DatabaseTest;
use UserFrosting\Sprinkle\Account\Account\Registration;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\System\Bakery\BaseCommand;

/**
 * Create root user CLI command.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CreateAdminUser extends BaseCommand
{
    use DatabaseTest;

    /**
     * @var string[] Migration dependencies for this command to work
     */
    protected $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RoleUsersTable'
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('create-admin')
             ->setDescription('Create the initial admin (root) user account')
             ->addOption('username', null, InputOption::VALUE_OPTIONAL, 'The admin user username')
             ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'The admin user email')
             ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'The admin user password')
             ->addOption('firstName', null, InputOption::VALUE_OPTIONAL, 'The admin user first name')
             ->addOption('lastName', null, InputOption::VALUE_OPTIONAL, 'The admin user last name');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Root account setup');

        // Need the database
        try {
            $this->io->writeln('<info>Testing database connection</info>', OutputInterface::VERBOSITY_VERBOSE);
            $this->testDB();
            $this->io->writeln('Ok', OutputInterface::VERBOSITY_VERBOSE);
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());
            exit(1);
        }

        /** @var \UserFrosting\Sprinkle\Core\Database\Migrator\Migrator; */
        $migrator = $this->ci->migrator;

        /** @var \UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository; */
        $repository = $migrator->getRepository();

        // Need migration table
        if (!$repository->repositoryExists()) {
            $this->io->error("Migrations doesn't appear to have been run! Make sure the database is properly migrated by using the `php bakery migrate` command.");
            exit(1);
        }

        // Make sure the required migrations have been run
        $ranMigrations = $repository->getMigrationsList();
        foreach ($this->dependencies as $migration) {
            if (!in_array($migration, $ranMigrations)) {
                $this->io->error("Migration `$migration` doesn't appear to have been run! Make sure all migrations are up to date by using the `php bakery migrate` command.");
                exit(1);
            }
        }

        // Make sure that there are no users currently in the user table
        // We setup the root account here so it can be done independent of the version check
        if (User::count() > 0) {
            $this->io->note("Table 'users' is not empty. Skipping root account setup. To set up the root account again, please truncate or drop the table and try again.");
        } else {
            $this->io->writeln("Please answer the following questions to create the root account:\n");

            // Don't need password confirmation if it's defined as an option
            $requireConfirmation = ($input->getOption('password') == '') ? true : false;

            // Get the account details
            $username = $this->askUsername($input->getOption('username'));
            $email = $this->askEmail($input->getOption('email'));
            $firstName = $this->askFirstName($input->getOption('firstName'));
            $lastName = $this->askLastName($input->getOption('lastName'));
            $password = $this->askPassword($input->getOption('password'), $requireConfirmation);

            // Ok, now we've got the info and we can create the new user.
            $this->io->write("\n<info>Saving the root user details...</info>");
            $registration = new Registration($this->ci, [
                'user_name'     => $username,
                'email'         => $email,
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'password'      => $password
            ]);
            $registration->setRequireEmailVerification(false);
            $registration->setDefaultRoles(['user', 'group-admin', 'site-admin']);

            try {
                $rootUser = $registration->register();
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());
                exit(1);
            }

            $this->io->success('Root user creation successful!');
        }
    }

    /**
     * Ask for the username and return a valid one
     *
     * @param  string $username The base/default username
     * @return string The validated username
     */
    protected function askUsername($username = '')
    {
        while (!isset($username) || !$this->validateUsername($username)) {
            $username = $this->io->ask('Choose a root username (1-50 characters, no leading or trailing whitespace)');
        }

        return $username;
    }

    /**
     * Validate the username.
     *
     * @param  string $username The input
     * @return bool   Is the username validated ?
     */
    protected function validateUsername($username)
    {
        // Validate length
        if (strlen($username) < 1 || strlen($username) > 50) {
            $this->io->error('Username must be between 1-50 characters');

            return false;
        }

        // Validate format
        $options = [
            'options' => [
                'regexp' => "/^\S((.*\S)|)$/"
            ]
        ];
        $validate = filter_var($username, FILTER_VALIDATE_REGEXP, $options);
        if (!$validate) {
            $this->io->error("Username can't have any leading or trailing whitespace");

            return false;
        }

        return true;
    }

    /**
     * Ask for the email and return a valid one
     *
     * @param  string $email The base/default email
     * @return string The validated email
     */
    protected function askEmail($email = '')
    {
        while (!isset($email) || !$this->validateEmail($email)) {
            $email = $this->io->ask('Enter a valid email address (1-254 characters, must be compatible with FILTER_VALIDATE_EMAIL)');
        }

        return $email;
    }

    /**
     * Validate the email.
     *
     * @param  string $email The input
     * @return bool   Is the email validated ?
     */
    protected function validateEmail($email)
    {
        // Validate length
        if (strlen($email) < 1 || strlen($email) > 254) {
            $this->io->error('Email must be between 1-254 characters');

            return false;
        }

        // Validate format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->io->error('Email must be compatible with FILTER_VALIDATE_EMAIL');

            return false;
        }

        return true;
    }

    /**
     * Ask for the first name and return a valid one
     *
     * @param  string $firstName The base/default first name
     * @return string The validated first name
     */
    protected function askFirstName($firstName = '')
    {
        while (!isset($firstName) || !$this->validateFirstName($firstName)) {
            $firstName = $this->io->ask('Enter the user first name (1-20 characters)');
        }

        return $firstName;
    }

    /**
     * Validate the first name
     *
     * @param  string $firstName The input
     * @return bool   Is the input validated ?
     */
    protected function validateFirstName($firstName)
    {
        // Validate length
        if (strlen($firstName) < 1 || strlen($firstName) > 20) {
            $this->io->error('First name must be between 1-20 characters');

            return false;
        }

        return true;
    }

    /**
     * Ask for the last name and return a valid one
     *
     * @param  string $lastName The base/default last name
     * @return string The validated last name
     */
    protected function askLastName($lastName = '')
    {
        while (!isset($lastName) || !$this->validateLastName($lastName)) {
            $lastName = $this->io->ask('Enter the user last name (1-30 characters)');
        }

        return $lastName;
    }

    /**
     * Validate the last name entered is valid
     *
     * @param  string $lastName The lastname
     * @return bool   Input is valid or not
     */
    protected function validateLastName($lastName)
    {
        // Validate length
        if (strlen($lastName) < 1 || strlen($lastName) > 30) {
            $this->io->error('Last name must be between 1-30 characters');

            return false;
        }

        return true;
    }

    /**
     * Ask for the password and return a valid one
     *
     * @param  string $password            The base/default password
     * @param  bool   $requireConfirmation (default true)
     * @return string The validated password
     */
    protected function askPassword($password = '', $requireConfirmation = true)
    {
        while (!isset($password) || !$this->validatePassword($password) || !$this->confirmPassword($password, $requireConfirmation)) {
            $password = $this->io->askHidden('Enter password (12-255 characters)');
        }

        return $password;
    }

    /**
     * Validate password input
     *
     * @param  string $password The input
     * @return bool   Is the password valid or not
     */
    protected function validatePassword($password)
    {
        //TODO Config for this ??
        if (strlen($password) < 12 || strlen($password) > 255) {
            $this->io->error('Password must be between 12-255 characters');

            return false;
        }

        return true;
    }

    /**
     * Ask for password confirmation
     *
     * @param  string $passwordToConfirm
     * @param  bool   $requireConfirmation (default true)
     * @return bool   Is the password confirmed or not
     */
    protected function confirmPassword($passwordToConfirm, $requireConfirmation = true)
    {
        if (!$requireConfirmation) {
            return true;
        }

        while (!isset($password)) {
            $password = $this->io->askHidden('Please re-enter the chosen password');
        }

        return $this->validatePasswordConfirmation($password, $passwordToConfirm);
    }

    /**
     * Validate the confirmation password
     *
     * @param  string $password          The confirmation
     * @param  string $passwordToConfirm The password to confirm
     * @return bool   Is the confirmation password valid or not
     */
    protected function validatePasswordConfirmation($password, $passwordToConfirm)
    {
        if ($password != $passwordToConfirm) {
            $this->io->error('Passwords do not match, please try again.');

            return false;
        }

        return true;
    }
}
