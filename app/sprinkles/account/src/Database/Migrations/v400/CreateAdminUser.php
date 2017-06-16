<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Database\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Util\Password;
use UserFrosting\System\Bakery\Migration;

/**
 * CreateAdminUser migration
 * This migration handle the creation of the admin user. This is skipped if the user already exist
 * Version 4.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @extends Migration
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CreateAdminUser extends Migration
{
    /**
     * {@inheritDoc}
     */
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RoleUsersTable'
    ];

    /**
     * Seed the master user into the User Table
     */
    public function seed()
    {
        $this->io->section("Root account setup");

        // Make sure that there are no users currently in the user table
        // We setup the root account here so it can be done independent of the version check
        if (User::count() > 0) {

            $this->io->warning("Table 'users' is not empty. Skipping root account setup. To set up the root account again, please truncate or drop the table and try again.");

        } else {

            $this->io->writeln("To complete the installation process, you must set up a master (root) account.");
            $this->io->writeln("Please answer the following questions to complete this process:\n");

            // Get the account details
            $user_name = $this->askUsername();
            $email = $this->askEmail();
            $first_name = $this->askFirstName();
            $last_name = $this->askLastName();
            $password = $this->askPassword();

            // Ok, now we've got the info and we can create the new user.
            $this->io->write("\n<info>Saving the root user details...</info>");
            $rootUser = new User([
                "user_name" => $user_name,
                "email" => $email,
                "first_name" => $first_name,
                "last_name" => $last_name,
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
        }
    }

    /**
     * Ask for the username
     *
     * @access protected
     * @return void
     */
    protected function askUsername()
    {
        while (!isset($user_name) || !$this->validateUsername($user_name)) {
            $user_name = $this->io->ask("Enter the username (1-50 characters, no leading or trailing whitespace): ");
        }
        return $user_name;
    }

    /**
     * Validate the username.
     *
     * @access protected
     * @param mixed $user_name
     * @return void
     */
    protected function validateUsername($user_name)
    {
        // Validate length
        if (strlen($user_name) < 1 || strlen($user_name) > 50) {
            $this->io->error("Username must be between 1-50 characters");
            return false;
        }

        // Validate format
        $options = [
            'options' => [
                'regexp' => "/^\S((.*\S)|)$/"
            ]
        ];
        $validate = filter_var($user_name, FILTER_VALIDATE_REGEXP, $options);
        if (!$validate) {
            $this->io->error("Username can't have any leading or trailing whitespace");
            return false;
        }

        return true;
    }

    /**
     * Ask for the email
     *
     * @access protected
     * @return void
     */
    protected function askEmail()
    {
        while (!isset($email) || !$this->validateEmail($email)) {
            $email = $this->io->ask("Enter a valid email address (1-254 characters, must be compatible with FILTER_VALIDATE_EMAIL): ");
        }
        return $email;
    }

    /**
     * Validate the email.
     *
     * @access protected
     * @param mixed $email
     * @return void
     */
    protected function validateEmail($email)
    {
        // Validate lenght
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
     * @access protected
     * @return void
     */
    protected function askFirstName()
    {
        while (!isset($first_name) || !$this->validateFirstName($first_name)) {
            $first_name = $this->io->ask("Enter the admin user first name (1-20 characters): ");
        }
        return $first_name;
    }

    /**
     * validateFirstName function.
     *
     * @access protected
     * @param mixed $name
     * @return void
     */
    protected function validateFirstName($first_name)
    {
        // Validate lenght
        if (strlen($first_name) < 1 || strlen($first_name) > 20) {
            $this->io->error("First name must be between 1-20 characters");
            return false;
        }

        return true;
    }

    /**
     * Ask for the last name
     *
     * @access protected
     * @return void
     */
    protected function askLastName()
    {
        while (!isset($last_name) || !$this->validateLastName($last_name)) {
            $last_name = $this->io->ask("Enter the admin user last name (1-30 characters): ");
        }
        return $last_name;
    }

    /**
     * validateLastName function.
     *
     * @access protected
     * @param mixed $last_name
     * @return void
     */
    protected function validateLastName($last_name)
    {
        // Validate lenght
        if (strlen($last_name) < 1 || strlen($last_name) > 30) {
            $this->io->error("Last name must be between 1-30 characters");
            return false;
        }

        return true;
    }

    /**
     * Ask for the password
     *
     * @access protected
     * @return void
     */
    protected function askPassword()
    {
        while (!isset($password) || !$this->validatePassword($password) || !$this->confirmPassword($password)) {
            $password = $this->io->askHidden("Enter password (12-255 characters): ");
        }
        return $password;
    }

    /**
     * validatePassword function.
     *
     * @access protected
     * @param mixed $password
     * @return void
     */
    protected function validatePassword($password)
    {
        if (strlen($password) < 12 || strlen($password) > 255) {
            $this->io->error("Password must be between 1-20 characters");
            return false;
        }

        return true;
    }

    /**
     * confirmPassword function.
     *
     * @access protected
     * @param mixed $passwordToConfirm
     * @return void
     */
    protected function confirmPassword($passwordToConfirm)
    {
        while (!isset($password)) {
            $password = $this->io->askHidden("Please re-enter the chosen password: ");
        }
        return $this->validatePasswordConfirmation($password, $passwordToConfirm);
    }

    /**
     * validatePasswordConfirmation function.
     *
     * @access protected
     * @param mixed $password
     * @param mixed $passwordToConfirm
     * @return void
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
