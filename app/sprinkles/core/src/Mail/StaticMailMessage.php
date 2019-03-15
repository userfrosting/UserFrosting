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
 * StaticMailMessage Class
 *
 * Represents a basic mail message, containing a static subject and body.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class StaticMailMessage extends MailMessage
{
    /**
     * @var string The default body for this message.
     */
    protected $body;

    /**
     * @var string The default subject for this message.
     */
    protected $subject;

    /**
     * Create a new MailMessage instance.
     *
     * @param string $subject
     * @param string $body
     */
    public function __construct($subject = '', $body = '')
    {
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * {@inheritdoc}
     */
    public function renderBody($params = [])
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function renderSubject($params = [])
    {
        return $this->subject;
    }

    /**
     * Set the text of the message subject.
     *
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set the text of the message body.
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }
}
