<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Support\DotenvEditor\DotenvEditor;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\System\Bakery\BaseCommand;

/**
 * SMTP Setup Wizard CLI Tools.
 * Helper command to setup SMTP config in .env file.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SetupSmtpCommand extends BaseCommand
{
    /**
     * @var string Path to the .env file
     */
    protected $envPath = \UserFrosting\APP_DIR . '/.env';

    /**
     * @var string SMTP setup string
     */
    const Setup_SMTP = 'SMTP Server';

    /**
     * @var string Gmail setup string
     */
    const Setup_Gmail = 'Gmail';

    /**
     * @var string Native mail setup string
     */
    const Setup_Native = 'Native Mail';

    /**
     * @var string No email setup string
     */
    const Setup_None = 'No email support';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup:mail')
             ->setAliases(['setup:smtp'])
             ->setDescription('UserFrosting SMTP Configuration Wizard')
             ->setHelp('Helper command to setup outgoing email configuration. This can also be done manually by editing the <comment>app/.env</comment> file or using global server environment variables.')
             ->addOption('force', null, InputOption::VALUE_NONE, 'Force setup if SMTP appears to be already configured')
             ->addOption('smtp_host', null, InputOption::VALUE_OPTIONAL, 'The SMTP server hostname')
             ->addOption('smtp_user', null, InputOption::VALUE_OPTIONAL, 'The SMTP server user')
             ->addOption('smtp_password', null, InputOption::VALUE_OPTIONAL, 'The SMTP server password')
             ->addOption('smtp_port', null, InputOption::VALUE_OPTIONAL, 'The SMTP server port')
             ->addOption('smtp_auth', null, InputOption::VALUE_OPTIONAL, 'The SMTP server authentication')
             ->addOption('smtp_secure', null, InputOption::VALUE_OPTIONAL, 'The SMTP server security type');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var Config Get config
         */
        $config = $this->ci->config;

        // Display header,
        $this->io->title("UserFrosting's Mail Setup Wizard");

        // Get an instance of the DotenvEditor
        $dotenvEditor = new DotenvEditor(\UserFrosting\APP_DIR, false);
        $dotenvEditor->load($this->envPath);
        $dotenvEditor->save(); // Save make sure empty file is created if none exist before reading it

        // Check if db is already setup
        if (!$input->getOption('force') && $this->isSmtpConfigured($dotenvEditor)) {
            $this->io->note('Mail is already setup. Use the `php bakery setup:mail --force` command to run setup again.');

            return;
        }

        // Get keys
        $keys = [
            'MAIL_MAILER'   => ($dotenvEditor->keyExists('MAIL_MAILER')) ? $dotenvEditor->getValue('MAIL_MAILER') : '',
            'SMTP_HOST'     => ($dotenvEditor->keyExists('SMTP_HOST')) ? $dotenvEditor->getValue('SMTP_HOST') : '',
            'SMTP_USER'     => ($dotenvEditor->keyExists('SMTP_USER')) ? $dotenvEditor->getValue('SMTP_USER') : '',
            'SMTP_PASSWORD' => ($dotenvEditor->keyExists('SMTP_PASSWORD')) ? $dotenvEditor->getValue('SMTP_PASSWORD') : '',
            'SMTP_PORT'     => ($dotenvEditor->keyExists('SMTP_PORT')) ? $dotenvEditor->getValue('SMTP_PORT') : '',
            'SMTP_AUTH'     => ($dotenvEditor->keyExists('SMTP_AUTH')) ? $dotenvEditor->getValue('SMTP_AUTH') : '',
            'SMTP_SECURE'   => ($dotenvEditor->keyExists('SMTP_SECURE')) ? $dotenvEditor->getValue('SMTP_SECURE') : '',
        ];

        // There may be some custom config or global env values defined on the server.
        // We'll check for that and ask for confirmation in this case.
        if ($config['mail.mailer'] != $keys['MAIL_MAILER'] ||
            $config['mail.host'] != $keys['SMTP_HOST'] ||
            $config['mail.username'] != $keys['SMTP_USER'] ||
            $config['mail.password'] != $keys['SMTP_PASSWORD'] ||
            $config['mail.port'] != $keys['SMTP_PORT'] ||
            $config['mail.auth'] != $keys['SMTP_AUTH'] ||
            $config['mail.secure'] != $keys['SMTP_SECURE']
        ) {
            $this->io->warning("Current mail configuration from config service differ from the configuration defined in `{$this->envPath}`. Global system environment variables might be defined, and it might not be required to setup mail again.");

            if (!$this->io->confirm('Continue with mail setup?', false)) {
                return;
            }
        }

        $this->io->note("Mail configuration and SMTP credentials will be saved in `{$this->envPath}`");

        // Ask for SMTP info
        $smtpParams = $this->askForMailMethod($input);

        // Time to save
        $this->io->section('Saving data');

        foreach ($smtpParams as $key => $value) {
            $dotenvEditor->setKey($key, $value);
        }
        $dotenvEditor->save();

        // Success
        $this->io->success("Mail configuration saved to `{$this->envPath}`.\nYou can test outgoing mail using `test:mail` command.");
    }

    /**
     * Ask with setup method to use.
     *
     * @param InputInterface $input
     *
     * @return array The SMTP connection info
     */
    protected function askForMailMethod(InputInterface $input)
    {
        // If the user defined any of the command input argument, skip right to SMTP method
        if ($input->getOption('smtp_host') ||
            $input->getOption('smtp_user') ||
            $input->getOption('smtp_password') ||
            $input->getOption('smtp_port') ||
            $input->getOption('smtp_auth') ||
            $input->getOption('smtp_secure')
        ) {
            return $this->askForSmtp($input);
        }

        // Display nice explanation and ask wich method to use
        $this->io->write("In order to send registration emails, UserFrosting requires an outgoing mail server. When using UserFrosting in a production environment, a SMTP server should be used. A Gmail account or native mail command can be used if you're only playing with UserFrosting or on a local dev environment. You can also choose to not setup an outgoing mail server at the moment, but account registration won't work. You can always re-run this setup or edit `{$this->envPath}` if you have problems sending email later.");

        $choice = $this->io->choice('Select setup method', [self::Setup_SMTP, self::Setup_Gmail, self::Setup_Native, self::Setup_None], self::Setup_SMTP);

        switch ($choice) {
            case self::Setup_SMTP:
                return $this->askForSmtp($input);
            break;
            case self::Setup_Gmail:
                return $this->askForGmail($input);
            break;
            case self::Setup_Native:
                return $this->askForNative($input);
            break;
            case self::Setup_None:
            default:
                return $this->askForNone($input);
            break;
        }
    }

    /**
     * Ask for SMTP credential.
     *
     * @param InputInterface $input Command arguments
     *
     * @return array The SMTP connection info
     */
    protected function askForSmtp(InputInterface $input)
    {
        // Ask for the smtp values now
        $smtpHost = ($input->getOption('smtp_host')) ?: $this->io->ask('SMTP Server Host', 'host.example.com');
        $smtpUser = ($input->getOption('smtp_user')) ?: $this->io->ask('SMTP Server User', 'relay@example.com');
        $smtpPassword = ($input->getOption('smtp_password')) ?: $this->io->askHidden('SMTP Server Password', function ($password) {
            // Use custom validator to accept empty password
            return $password;
        });
        $smtpPort = ($input->getOption('smtp_port')) ?: $this->io->ask('SMTP Server Port', 587);
        $smtpAuth = ($input->getOption('smtp_auth')) ?: $this->io->confirm('SMTP Server Authentication', true);
        $smtpSecure = ($input->getOption('smtp_secure')) ?: $this->io->choice('SMTP Server Security type', ['tls', 'ssl', 'Other...'], 'tls');

        // Ask for custom input if 'other' was chosen
        if ($smtpSecure == 'Other...') {
            $smtpSecure = $this->io->ask('Enter custom SMTP Server Security type');
        }

        return [
            'MAIL_MAILER'   => 'smtp',
            'SMTP_HOST'     => $smtpHost,
            'SMTP_USER'     => $smtpUser,
            'SMTP_PASSWORD' => $smtpPassword,
            'SMTP_PORT'     => $smtpPort,
            'SMTP_AUTH'     => ($smtpAuth) ? 'true' : 'false',
            'SMTP_SECURE'   => $smtpSecure,
        ];
    }

    /**
     * Ask for Gmail.
     *
     * @param InputInterface $input Command arguments
     *
     * @return array The SMTP connection info
     */
    protected function askForGmail(InputInterface $input)
    {
        $smtpUser = ($input->getOption('smtp_user')) ?: $this->io->ask('Your full Gmail (e.g. example@gmail.com)');
        $smtpPassword = ($input->getOption('smtp_password')) ?: $this->io->askHidden('Your Gmail password', function ($password) {
            // Use custom validator to accept empty password
            return $password;
        });

        return [
            'MAIL_MAILER'   => 'smtp',
            'SMTP_HOST'     => 'smtp.gmail.com',
            'SMTP_USER'     => $smtpUser,
            'SMTP_PASSWORD' => $smtpPassword,
        ];
    }

    /**
     * Process the "native mail" setup option.
     *
     * @param InputInterface $input
     *
     * @return array The SMTP connection info
     */
    protected function askForNative(InputInterface $input)
    {
        // Display big warning and confirmation
        $this->io->warning('Native mail function should only be used locally, inside containers or for development purposes.');

        if ($this->io->confirm('Continue ?', false)) {
            return [
                'MAIL_MAILER'   => 'mail',
                'SMTP_HOST'     => '',
                'SMTP_USER'     => '',
                'SMTP_PASSWORD' => '',
                'SMTP_PORT'     => '',
                'SMTP_AUTH'     => '',
                'SMTP_SECURE'   => '',
            ];
        } else {
            $this->askForMailMethod($input);
        }
    }

    /**
     * Process the "no email support" setup option.
     *
     * @param InputInterface $input
     *
     * @return array The SMTP connection info
     */
    protected function askForNone(InputInterface $input)
    {
        // Display big warning and confirmation
        $this->io->warning("By not setting up any outgoing mail server, public account registration won't work.");

        if ($this->io->confirm('Continue ?', false)) {
            return [
                'MAIL_MAILER'   => 'smtp',
                'SMTP_HOST'     => '',
                'SMTP_USER'     => '',
                'SMTP_PASSWORD' => '',
                'SMTP_PORT'     => '',
                'SMTP_AUTH'     => '',
                'SMTP_SECURE'   => '',
            ];
        } else {
            $this->askForMailMethod($input);
        }
    }

    /**
     * Check if the app/.env SMTP portion is defined or not.
     *
     * @param DotenvEditor $dotenvEditor
     *
     * @return bool true if SMTP is configured in .env file
     */
    protected function isSmtpConfigured(DotenvEditor $dotenvEditor)
    {
        if ($dotenvEditor->keyExists('MAIL_MAILER') || (
            $dotenvEditor->keyExists('SMTP_HOST') &&
            $dotenvEditor->keyExists('SMTP_USER') &&
            $dotenvEditor->keyExists('SMTP_PASSWORD') &&
            $dotenvEditor->keyExists('SMTP_PORT') &&
            $dotenvEditor->keyExists('SMTP_AUTH') &&
            $dotenvEditor->keyExists('SMTP_SECURE')
        )) {
            return true;
        } else {
            return false;
        }
    }
}
