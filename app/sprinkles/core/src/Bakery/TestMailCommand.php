<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Carbon\Carbon;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\System\Bakery\BaseCommand;
use UserFrosting\Sprinkle\Core\Mail\EmailRecipient;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;

/**
 * TestMail CLI Command.
 *
 * @author Louis Charette
 */
class TestMailCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('test:mail')
             ->setDescription('Test mail settings')
             ->setHelp('This command let you test the email sending capability of your UserFrosting setup.')
             ->addOption('to', null, InputOption::VALUE_REQUIRED, 'Email address to send test email to. Use admin contact if omitted.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Testing Email Configuration');

        /** @var \UserFrosting\Support\Repository\Repository */
        $config = $this->ci->config;

        $to = $input->getOption('to') ?: $config['address_book.admin.email'];
        $this->io->writeln("Sending test email to : $to");

        // Create and send email
        $message = new TwigMailMessage($this->ci->view, 'mail/test.html.twig');
        $message->from($config['address_book.admin'])
                ->addEmailRecipient(new EmailRecipient($to, $to))
                ->addParams([
                    'request_date' => Carbon::now()->format('Y-m-d H:i:s')
                ]);

        try {
            $this->ci->mailer->send($message);
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());
            exit(1);
        }

        $this->io->success("Test email sent to $to !");
    }
}
