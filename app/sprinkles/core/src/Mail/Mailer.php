<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Mail;

use Monolog\Logger;

/**
 * Mailer Class
 *
 * A basic wrapper for sending template-based emails.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Mailer
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \PHPMailer
     */
    protected $phpMailer;

    /**
     * Create a new Mailer instance.
     *
     * @param  Logger              $logger A Monolog logger, used to dump debugging info for SMTP server transactions.
     * @param  mixed[]             $config An array of configuration parameters for phpMailer.
     * @throws \phpmailerException Wrong mailer config value given.
     */
    public function __construct($logger, $config = [])
    {
        $this->logger = $logger;

        // 'true' tells PHPMailer to use exceptions instead of error codes
        $this->phpMailer = new \PHPMailer(true);

        // Configuration options
        switch ($config['mailer']) {
            case 'mail':
                $this->phpMailer->isMail();
                break;
            case 'qmail':
                $this->phpMailer->isQmail();
                break;
            case 'sendmail':
                $this->phpMailer->isSendmail();
                break;
            case 'smtp':
                $this->phpMailer->isSMTP(true);
                $this->phpMailer->Host = $config['host'];
                $this->phpMailer->Port = $config['port'];
                $this->phpMailer->SMTPAuth = $config['auth'];
                $this->phpMailer->SMTPSecure = $config['secure'];
                $this->phpMailer->Username = $config['username'];
                $this->phpMailer->Password = $config['password'];
                $this->phpMailer->SMTPDebug = $config['smtp_debug'];

                if (isset($config['smtp_options'])) {
                    $this->phpMailer->SMTPOptions = $config['smtp_options'];
                }
                break;
            default:
                throw new \phpmailerException("'mailer' must be one of 'smtp', 'mail', 'qmail', or 'sendmail'.");
        }

        // Set any additional message-specific options
        // TODO: enforce which options can be set through this subarray
        if (isset($config['message_options'])) {
            $this->setOptions($config['message_options']);
        }

        // Pass logger into phpMailer object
        $this->phpMailer->Debugoutput = function ($message, $level) {
            $this->logger->debug($message);
        };
    }

    /**
     * Get the underlying PHPMailer object.
     *
     * @return \PHPMailer
     */
    public function getPhpMailer()
    {
        return $this->phpMailer;
    }

    /**
     * Send a MailMessage message.
     *
     * Sends a single email to all recipients, as well as their CCs and BCCs.
     * Since it is a single-header message, recipient-specific template data will not be included.
     * @param  MailMessage         $message
     * @param  bool                $clearRecipients Set to true to clear the list of recipients in the message after calling send().  This helps avoid accidentally sending a message multiple times.
     * @throws \phpmailerException The message could not be sent.
     */
    public function send(MailMessage $message, $clearRecipients = true)
    {
        $this->phpMailer->From = $message->getFromEmail();
        $this->phpMailer->FromName = $message->getFromName();
        $this->phpMailer->addReplyTo($message->getReplyEmail(), $message->getReplyName());

        // Add all email recipients, as well as their CCs and BCCs
        foreach ($message->getRecipients() as $recipient) {
            $this->phpMailer->addAddress($recipient->getEmail(), $recipient->getName());

            // Add any CCs and BCCs
            if ($recipient->getCCs()) {
                foreach ($recipient->getCCs() as $cc) {
                    $this->phpMailer->addCC($cc['email'], $cc['name']);
                }
            }

            if ($recipient->getBCCs()) {
                foreach ($recipient->getBCCs() as $bcc) {
                    $this->phpMailer->addBCC($bcc['email'], $bcc['name']);
                }
            }
        }

        $this->phpMailer->Subject = $message->renderSubject();
        $this->phpMailer->Body = $message->renderBody();

        // Try to send the mail.  Will throw an exception on failure.
        $this->phpMailer->send();

        // Clear recipients from the PHPMailer object for this iteration,
        // so that we can use the same object for other emails.
        $this->phpMailer->clearAllRecipients();

        // Clear out the MailMessage's internal recipient list
        if ($clearRecipients) {
            $message->clearRecipients();
        }
    }

    /**
     * Send a MailMessage message, sending a separate email to each recipient.
     *
     * If the message object supports message templates, this will render the template with the corresponding placeholder values for each recipient.
     * @param  MailMessage         $message
     * @param  bool                $clearRecipients Set to true to clear the list of recipients in the message after calling send().  This helps avoid accidentally sending a message multiple times.
     * @throws \phpmailerException The message could not be sent.
     */
    public function sendDistinct(MailMessage $message, $clearRecipients = true)
    {
        $this->phpMailer->From = $message->getFromEmail();
        $this->phpMailer->FromName = $message->getFromName();
        $this->phpMailer->addReplyTo($message->getReplyEmail(), $message->getReplyName());

        // Loop through email recipients, sending customized content to each one
        foreach ($message->getRecipients() as $recipient) {
            $this->phpMailer->addAddress($recipient->getEmail(), $recipient->getName());

            // Add any CCs and BCCs
            if ($recipient->getCCs()) {
                foreach ($recipient->getCCs() as $cc) {
                    $this->phpMailer->addCC($cc['email'], $cc['name']);
                }
            }

            if ($recipient->getBCCs()) {
                foreach ($recipient->getBCCs() as $bcc) {
                    $this->phpMailer->addBCC($bcc['email'], $bcc['name']);
                }
            }

            $this->phpMailer->Subject = $message->renderSubject($recipient->getParams());
            $this->phpMailer->Body = $message->renderBody($recipient->getParams());

            // Try to send the mail.  Will throw an exception on failure.
            $this->phpMailer->send();

            // Clear recipients from the PHPMailer object for this iteration,
            // so that we can send a separate email to the next recipient.
            $this->phpMailer->clearAllRecipients();
        }

        // Clear out the MailMessage's internal recipient list
        if ($clearRecipients) {
            $message->clearRecipients();
        }
    }

    /**
     * Set option(s) on the underlying phpMailer object.
     *
     * @param  mixed[] $options
     * @return Mailer
     */
    public function setOptions($options)
    {
        if (isset($options['isHtml'])) {
            $this->phpMailer->isHTML($options['isHtml']);
        }

        foreach ($options as $name => $value) {
            $this->phpMailer->set($name, $value);
        }

        return $this;
    }
}
