<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Mail;

/**
 * MailMessage Class
 *
 * Represents a basic mail message, containing a static subject and body.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
abstract class MailMessage
{
    /**
     * @var string The current sender email address.
     */
    protected $fromEmail = '';

    /**
     * @var string The current sender name.
     */
    protected $fromName = null;

    /**
     * @var EmailRecipient[] A list of recipients for this message.
     */
    protected $recipients = [];

    /**
     * @var string The current reply-to email.
     */
    protected $replyEmail = null;

    /**
     * @var string The current reply-to name.
     */
    protected $replyName = null;

    /**
     * Gets the fully rendered text of the message body.
     *
     * @param  array  $params
     * @return string
     */
    abstract public function renderBody($params = []);

    /**
     * Gets the fully rendered text of the message subject.
     *
     * @param  array  $params
     * @return string
     */
    abstract public function renderSubject($params = []);

    /**
     * Add an email recipient.
     *
     * @param EmailRecipient $recipient
     */
    public function addEmailRecipient(EmailRecipient $recipient)
    {
        $this->recipients[] = $recipient;

        return $this;
    }

    /**
     * Clears out all recipients for this message.
     */
    public function clearRecipients()
    {
        $this->recipients = [];
    }

    /**
     * Set sender information for this message.
     *
     * This is a shortcut for calling setFromEmail, setFromName, setReplyEmail, and setReplyName.
     * @param string $fromInfo An array containing 'email', 'name', 'reply_email', and 'reply_name'.
     */
    public function from($fromInfo = [])
    {
        $this->setFromEmail(isset($fromInfo['email']) ? $fromInfo['email'] : '');
        $this->setFromName(isset($fromInfo['name']) ? $fromInfo['name'] : null);
        $this->setReplyEmail(isset($fromInfo['reply_email']) ? $fromInfo['reply_email'] : null);
        $this->setReplyName(isset($fromInfo['reply_name']) ? $fromInfo['reply_name'] : null);

        return $this;
    }

    /**
     * Get the sender email address.
     *
     * @return string
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * Get the sender name.  Defaults to the email address if name is not set.
     *
     * @return string
     */
    public function getFromName()
    {
        return isset($this->fromName) ? $this->fromName : $this->getFromEmail();
    }

    /**
     * Get the list of recipients for this message.
     *
     * @return EmailRecipient[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Get the 'reply-to' address for this message.  Defaults to the sender email.
     *
     * @return string
     */
    public function getReplyEmail()
    {
        return isset($this->replyEmail) ? $this->replyEmail : $this->getFromEmail();
    }

    /**
     * Get the 'reply-to' name for this message.  Defaults to the sender name.
     *
     * @return string
     */
    public function getReplyName()
    {
        return isset($this->replyName) ? $this->replyName : $this->getFromName();
    }

    /**
     * Set the sender email address.
     *
     * @param string $fromEmail
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    /**
     * Set the sender name.
     *
     * @param string $fromName
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * Set the sender 'reply-to' address.
     *
     * @param string $replyEmail
     */
    public function setReplyEmail($replyEmail)
    {
        $this->replyEmail = $replyEmail;

        return $this;
    }

    /**
     * Set the sender 'reply-to' name.
     *
     * @param string $replyName
     */
    public function setReplyName($replyName)
    {
        $this->replyName = $replyName;

        return $this;
    }
}
