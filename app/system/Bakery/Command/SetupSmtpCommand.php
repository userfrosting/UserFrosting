<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery\Command;

use UserFrosting\Support\DotenvEditor\DotenvEditor;
use UserFrosting\System\Bakery\BaseCommand;
use UserFrosting\Support\Repository\Repository as Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * SMTP Setup Wizard CLI Tools.
 * Helper command to setup SMTP config in .env file
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SetupSmtpCommand extends BaseCommand
{
    /**
     * @var string Path to the .env file
     */
    protected $envPath = \UserFrosting\APP_DIR. '/.env';

    /**
     * @var string SMTP setup string
     */
    const Setup_SMTP = 'SMTP Server';

    /**
     * @var string Gmail setup string
     */
    const Setup_Gmail = 'Gmail';

    /**
     * @var string No email setup string
     */
    const Setup_None = 'No email support';

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('setup:smtp')
             ->setDescription('UserFrosting SMTP Configuration Wizard')
             ->setHelp('Helper command to setup outgoing email configuration. This can also be done manually by editing the <comment>app/.env</comment> file or using global server environment variables.')
             ->addOption('smtp_host', null, InputOption::VALUE_OPTIONAL, 'The SMTP server hostname')
             ->addOption('smtp_user', null, InputOption::VALUE_OPTIONAL, 'The SMTP server user')
             ->addOption('smtp_password', null, InputOption::VALUE_OPTIONAL, 'The SMTP server password');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var Config $config Get config
         */
        $config = $this->ci->config;

        // Display header,
        $this->io->title("UserFrosting's SMTP Setup Wizard");
        $this->io->note("SMTP credentials will be saved in `{$this->envPath}`");

        // Get an instance of the DotenvEditor
        $dotenvEditor = new DotenvEditor(\UserFrosting\APP_DIR, false);
        $dotenvEditor->load($this->envPath);
        $dotenvEditor->save(); // Save make sure empty file is created if none exist before reading it

        // Get keys
        $keys = [
            'SMTP_HOST' => ($dotenvEditor->keyExists('SMTP_HOST')) ? $dotenvEditor->getValue('SMTP_HOST') : '',
            'SMTP_USER' => ($dotenvEditor->keyExists('SMTP_USER')) ? $dotenvEditor->getValue('SMTP_USER') : '',
            'SMTP_PASSWORD' => ($dotenvEditor->keyExists('SMTP_PASSWORD')) ? $dotenvEditor->getValue('SMTP_PASSWORD') : ''
        ];

        // There may be some custom config or global env values defined on the server.
        // We'll check for that and ask for confirmation in this case.
        if ($config['mail.host'] != $keys['SMTP_HOST'] ||
            $config['mail.username'] != $keys['SMTP_USER'] ||
            $config['mail.password'] != $keys['SMTP_PASSWORD']) {

            $this->io->warning("Current SMTP configuration differ from the configuration defined in `{$this->envPath}`. Global system environment variables might be defined.");

            if (!$this->io->confirm('Continue?', false)) {
                return;
            }
        }

        // Ask for SMTP info
        $smtpParams = $this->askForSmtpMethod($input);

        // Time to save
        $this->io->section('Saving data');

        foreach ($smtpParams as $key => $value) {
            $dotenvEditor->setKey($key, $value);
        }
        $dotenvEditor->save();

        // Success
        $this->io->success("SMTP credentials saved to `{$this->envPath}`");
    }

    /**
     * Ask with setup method to use
     *
     * @param  InputInterface $input
     * @return array The SMTP connection info
     */
    protected function askForSmtpMethod(InputInterface $input)
    {
        // If the user defined any of the command input argument, skip right to SMTP method
        if ($input->getOption('smtp_host') || $input->getOption('smtp_user') || $input->getOption('smtp_password')) {
            return $this->askForSmtp($input);
        }

        // Display nice explanation and ask wich method to use
        $this->io->write("In order to send registration emails, UserFrosting requires an outgoing mail server. When using UserFrosting in a production environment, a SMTP server should be used. A Gmail account can be used if you're only playing with UserFrosting or on a local dev environment. You can also choose to not setup an outgoing mail server at the moment, but account registration won't work. You can always re-run this setup or edit `{$this->envPath}` if you have problems sending email later.");

        $choice = $this->io->choice('Select setup method', [self::Setup_SMTP, self::Setup_Gmail, self::Setup_None], self::Setup_SMTP);

        switch ($choice) {
            case self::Setup_SMTP:
                return $this->askForSmtp($input);
            break;
            case self::Setup_Gmail:
                return $this->askForGmail($input);
            break;
            case self::Setup_None:
            default:
                return $this->askForNone($input);
            break;
        }
    }

    /**
     * Ask for SMTP credential
     *
     * @param  InputInterface $input Command arguments
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

        return [
            'SMTP_HOST' => $smtpHost,
            'SMTP_USER' => $smtpUser,
            'SMTP_PASSWORD' => $smtpPassword
        ];
    }

    /**
     * Ask for Gmail
     *
     * @param  InputInterface $input Command arguments
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
            'SMTP_HOST' => 'smtp.gmail.com',
            'SMTP_USER' => $smtpUser,
            'SMTP_PASSWORD' => $smtpPassword
        ];
    }

    /**
     * Process the "no email support" setup option
     *
     * @param  InputInterface $input
     * @return array The SMTP connection info
     */
    protected function askForNone(InputInterface $input)
    {
        // Display big warning and confirmation
        $this->io->warning("By not setting up any outgoing mail server, public account registration won't work.");

        if ($this->io->confirm('Continue ?', false)) {
            return [
                'SMTP_HOST' => '',
                'SMTP_USER' => '',
                'SMTP_PASSWORD' => ''
            ];
        } else {
            $this->askForSmtpMethod($input);
        }
    }
}
