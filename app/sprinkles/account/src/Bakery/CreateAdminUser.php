<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\System\Bakery\BaseCommand;
use UserFrosting\System\Bakery\DatabaseTest;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Facades\Password;

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
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName("create-admin")
             ->setDescription("Create the initial admin (root) user account");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("Root account setup");

        // Need the database
        try {
            $this->io->writeln("<info>Testing database connection</info>", OutputInterface::VERBOSITY_VERBOSE);
            $this->testDB();
            $this->io->writeln("Ok", OutputInterface::VERBOSITY_VERBOSE);
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());
            exit(1);
        }

        /**
         * @var \UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
         */
        $migrator = $this->ci->migrator;

        /**
         * @var \UserFrosting\Sprinkle\Core\Database\Migrator\DatabaseMigrationRepository;
         */
        $repository = $migrator->getRepository();

        // Need migration table
        if (!$repository->repositoryExists()) {
            $this->io->error("Migrations doesn't appear to have been run! Make sure the database is properly migrated by using the `php bakery migrate` command.");
            exit(1);
        }

        // Make sure the required migrations have been run
        $ranMigrations = $repository->getRan();
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

            // Get the account details
            $userName = $this->askUsername();
            $email = $this->askEmail();
            $firstName = $this->askFirstName();
            $lastName = $this->askLastName();
            $password = $this->askPassword();

            // Ok, now we've got the info and we can create the new user.
            $this->io->write("\n<info>Saving the root user details...</info>");
            $rootUser = new User([
                "user_name" => $userName,
                "email" => $email,
                "first_name" => $firstName,
                "last_name" => $lastName,
                "password" => Password::hash($password)
            ]);

            $rootUser->save();

            $defaultRoles = [
                'user' => Role::where('slug', 'user')->first(),
                'group-admin' => Role::where('slug', 'group-admin')->first(),
                'site-admin' => Role::where('slug', 'site-admin')->first()
            ];

            foreach ($defaultRoles as $slug => $role) {
                if ($role) {
                    $rootUser->roles()->attach($role->id);
                }
            }

            $this->io->success("Root user creation successful!");
        }
    }

    /**
     * Ask for the username
     *
     * @return string
     */
    protected function askUsername()
    {
        while (!isset($userName) || !$this->validateUsername($userName)) {
            $userName = $this->io->ask("Choose a root username (1-50 characters, no leading or trailing whitespace)");
        }
        return $userName;
    }

    /**
     * Validate the username.
     *
     * @param string $userName
     * @return bool
     */
    protected function validateUsername($userName)
    {
        // Validate length
        if (strlen($userName) < 1 || strlen($userName) > 50) {
            $this->io->error("Username must be between 1-50 characters");
            return false;
        }

        // Validate format
        $options = [
            'options' => [
                'regexp' => "/^\S((.*\S)|)$/"
            ]
        ];
        $validate = filter_var($userName, FILTER_VALIDATE_REGEXP, $options);
        if (!$validate) {
            $this->io->error("Username can't have any leading or trailing whitespace");
            return false;
        }

        return true;
    }

    /**
     * Ask for the email
     *
     * @return string
     */
    protected function askEmail()
    {
        while (!isset($email) || !$this->validateEmail($email)) {
            $email = $this->io->ask("Enter a valid email address (1-254 characters, must be compatible with FILTER_VALIDATE_EMAIL)");
        }
        return $email;
    }

    /**
     * Validate the email.
     *
     * @param string $email
     * @return bool
     */
    protected function validateEmail($email)
    {
        // Validate length
        if (strlen($email) < 1 || strlen($email) > 254) {
            $this->io->error("Email must be between 1-254 characters");
            return false;
        }

        // Validate format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->io->error("Email must be compatible with FILTER_VALIDATE_EMAIL");
            return false;
        }

        return true;
    }

    /**
     * Ask for the first name
     *
     * @return string
     */
    protected function askFirstName()
    {
        while (!isset($firstName) || !$this->validateFirstName($firstName)) {
            $firstName = $this->io->ask("Enter the user first name (1-20 characters)");
        }
        return $firstName;
    }

    /**
     * validateFirstName function.
     *
     * @param string $firstName
     * @return bool
     */
    protected function validateFirstName($firstName)
    {
        // Validate length
        if (strlen($firstName) < 1 || strlen($firstName) > 20) {
            $this->io->error("First name must be between 1-20 characters");
            return false;
        }

        return true;
    }

    /**
     * Ask for the last name
     *
     * @return string
     */
    protected function askLastName()
    {
        while (!isset($lastName) || !$this->validateLastName($lastName)) {
            $lastName = $this->io->ask("Enter the user last name (1-30 characters)");
        }
        return $lastName;
    }

    /**
     * validateLastName function.
     *
     * @param string $lastName
     * @return bool
     */
    protected function validateLastName($lastName)
    {
        // Validate length
        if (strlen($lastName) < 1 || strlen($lastName) > 30) {
            $this->io->error("Last name must be between 1-30 characters");
            return false;
        }

        return true;
    }

    /**
     * Ask for the password
     *
     * @return string
     */
    protected function askPassword()
    {
        while (!isset($password) || !$this->validatePassword($password) || !$this->confirmPassword($password)) {
            $password = $this->io->askHidden("Enter password (12-255 characters)");
        }
        return $password;
    }

    /**
     * validatePassword function.
     *
     * @param string $password
     * @return bool
     */
    protected function validatePassword($password)
    {
        if (strlen($password) < 12 || strlen($password) > 255) {
            $this->io->error("Password must be between 12-255 characters");
            return false;
        }

        return true;
    }

    /**
     * confirmPassword function.
     *
     * @param string $passwordToConfirm
     * @return bool
     */
    protected function confirmPassword($passwordToConfirm)
    {
        while (!isset($password)) {
            $password = $this->io->askHidden("Please re-enter the chosen password");
        }
        return $this->validatePasswordConfirmation($password, $passwordToConfirm);
    }

    /**
     * validatePasswordConfirmation function.
     *
     * @param string $password
     * @param string $passwordToConfirm
     * @return bool
     */
    protected function validatePasswordConfirmation($password, $passwordToConfirm)
    {
        if ($password != $passwordToConfirm) {
            $this->io->error("Passwords do not match, please try again.");
            return false;
        }

        return true;
    }
}
