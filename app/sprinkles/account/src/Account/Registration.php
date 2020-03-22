<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Account;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Container\ContainerInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Facades\Password;
use UserFrosting\Sprinkle\Core\Mail\EmailRecipient;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\Support\Exception\HttpException;

/**
 * Handles user registration tasks.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Registration
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var array The user profile data
     */
    protected $userdata;

    /**
     * @var bool Is the created user verified
     */
    protected $verified;

    /**
     * @var bool Require email verification
     */
    protected $requireEmailVerification;

    /**
     * @var string The default group slug
     */
    protected $defaultGroup;

    /**
     * @var array Default roles applied to a new user
     */
    protected $defaultRoles = [];

    /**
     * @var array The minimum info required to register a new user
     */
    protected $requiredProperties = [
        'user_name',
        'first_name',
        'last_name',
        'email',
        'password',
    ];

    /**
     * Constructor.
     *
     * @param ContainerInterface $ci       The global container object
     * @param array              $userdata The user data
     */
    public function __construct(ContainerInterface $ci, $userdata = [])
    {
        $this->userdata = $userdata;
        $this->ci = $ci;

        $this->setDefaults();
    }

    /**
     * Register a new user.
     *
     * @return UserInterface The created user
     */
    public function register()
    {
        // Validate the userdata
        $this->validate();

        // Set default group
        $defaultGroup = $this->ci->classMapper->getClassMapping('group')::where('slug', $this->defaultGroup)->first();

        if (!$defaultGroup) {
            $e = new HttpException("Account registration is not working because the default group '{$this->defaultGroup}' does not exist.");
            $e->addUserMessage('ACCOUNT.REGISTRATION_BROKEN');

            throw $e;
        }

        $this->setUserProperty('group_id', $defaultGroup->id);

        // Hash password
        $this->hashPassword();

        // Set verified flag
        $this->setUserProperty('flag_verified', !$this->getRequireEmailVerification());

        // All checks passed!  log events/activities, create user, and send verification email (if required)
        // Begin transaction - DB will be rolled back if an exception occurs
        $user = Capsule::transaction(function () {
            // Log throttleable event
            $this->ci->throttler->logEvent('registration_attempt');

            // Create the user
            $user = $this->ci->classMapper->createInstance('user', $this->userdata);

            // Store new user to database
            $user->save();

            // Create activity record
            $this->ci->userActivityLogger->info("User {$user->user_name} registered for a new account.", [
                'type'    => 'sign_up',
                'user_id' => $user->id,
            ]);

            // Load default roles
            $defaultRoles = $this->ci->classMapper->getClassMapping('role')::whereIn('slug', $this->defaultRoles)->get();
            $defaultRoleIds = $defaultRoles->pluck('id')->all();

            // Attach default roles
            $user->roles()->attach($defaultRoleIds);

            // Verification email
            if ($this->requireEmailVerification) {
                $this->sendVerificationEmail($user);
            }

            return $user;
        });

        return $user;
    }

    /**
     * Validate the user name and email is unique.
     *
     * @throws HttpException If data doesn't validate
     *
     * @return bool Returns true if the data is valid
     */
    public function validate()
    {
        // Make sure all required fields are defined
        foreach ($this->requiredProperties as $property) {
            if (!isset($this->userdata[$property])) {
                $e = new HttpException("Account can't be registrated as '$property' is required to create a new user.");
                $e->addUserMessage('USERNAME.IN_USE');

                throw $e;
            }
        }

        // Check if username is unique
        if (!$this->usernameIsUnique($this->userdata['user_name'])) {
            $e = new HttpException('Username is already in use.');
            $e->addUserMessage('USERNAME.IN_USE', ['user_name' => $this->userdata['user_name']]);

            throw $e;
        }

        // Check if email is unique
        if (!$this->emailIsUnique($this->userdata['email'])) {
            $e = new HttpException('Email is already in use.');
            $e->addUserMessage('EMAIL.IN_USE', ['email' => $this->userdata['email']]);

            throw $e;
        }

        // Validate password requirements
        // !TODO

        return true;
    }

    /**
     * Check Unique Username
     * Make sure the username is not already in use.
     *
     * @param string $username
     *
     * @return bool Return true if username is unique
     */
    public function usernameIsUnique($username)
    {
        return !($this->ci->classMapper->getClassMapping('user')::findUnique($username, 'user_name'));
    }

    /**
     * Check Unique Email
     * Make sure the email is not already in use.
     *
     * @param string $email
     *
     * @return bool Return true if email is unique
     */
    public function emailIsUnique($email)
    {
        return !($this->ci->classMapper->getClassMapping('user')::findUnique($email, 'email'));
    }

    /**
     * Hash the user password in the userdata array.
     */
    protected function hashPassword()
    {
        $this->userdata['password'] = Password::hash($this->userdata['password']);
    }

    /**
     * Set default value from config.
     */
    protected function setDefaults()
    {
        $this->verified = $this->ci->config['site.registration.require_email_verification'];
        $this->requireEmailVerification = $this->ci->config['site.registration.require_email_verification'];
        $this->defaultGroup = $this->ci->config['site.registration.user_defaults.group'];
        $this->defaultRoles = $this->ci->classMapper->getClassMapping('role')::getDefaultSlugs();
    }

    /**
     * Send verification email for specified user.
     *
     * @param UserInterface $user The user to send the email for
     */
    protected function sendVerificationEmail(UserInterface $user)
    {
        // Try to generate a new verification request
        $verification = $this->ci->repoVerification->create($user, $this->ci->config['verification.timeout']);

        // Create and send verification email
        $message = new TwigMailMessage($this->ci->view, 'mail/verify-account.html.twig');

        $message->from($this->ci->config['address_book.admin'])
                ->addEmailRecipient(new EmailRecipient($user->email, $user->full_name))
                ->addParams([
                    'user'  => $user,
                    'token' => $verification->getToken(),
                ]);

        $this->ci->mailer->send($message);
    }

    /**
     * @return bool
     */
    public function getRequireEmailVerification()
    {
        return $this->requireEmailVerification;
    }

    /**
     * @param bool $requireEmailVerification
     *
     * @return static
     */
    public function setRequireEmailVerification($requireEmailVerification)
    {
        $this->requireEmailVerification = $requireEmailVerification;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultGroup()
    {
        return $this->defaultGroup;
    }

    /**
     * @param string $defaultGroup
     *
     * @return static
     */
    public function setDefaultGroup($defaultGroup)
    {
        $this->defaultGroup = $defaultGroup;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultRoles()
    {
        return $this->defaultRoles;
    }

    /**
     * @param array $defaultRoles
     *
     * @return static
     */
    public function setDefaultRoles($defaultRoles)
    {
        $this->defaultRoles = $defaultRoles;

        return $this;
    }

    /**
     * @return array
     */
    public function getUserdata()
    {
        return $this->userdata;
    }

    /**
     * @param array $userdata
     *
     * @return static
     */
    public function setUserdata($userdata)
    {
        $this->userdata = $userdata;

        return $this;
    }

    /**
     * Define a user property.
     *
     * @param string $property The property to set
     * @param mixed  $value    The property value
     */
    public function setUserProperty($property, $value)
    {
        $this->userdata[$property] = $value;
    }
}
