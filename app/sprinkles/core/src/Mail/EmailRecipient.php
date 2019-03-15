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
 * EmailRecipient Class
 *
 * A class representing a recipient for a MailMessage, with associated parameters.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class EmailRecipient
{
    /**
     * @var string The email address for this recipient.
     */
    protected $email;

    /**
     * @var string The name for this recipient.
     */
    protected $name;

    /**
     * @var array Any additional parameters (name => value) to use when rendering an email template for this recipient.
     */
    protected $params = [];

    /**
     * @var array A list of CCs for this recipient.  Each CC is an associative array with `email` and `name` properties.
     */
    protected $cc = [];

    /**
     * @var array A list of BCCs for this recipient.  Each BCC is an associative array with `email` and `name` properties.
     */
    protected $bcc = [];

    /**
     * Create a new EmailRecipient instance.
     *
     * @param string $email  The primary recipient email address.
     * @param string $name   The primary recipient name.
     * @param array  $params An array of template parameters to render the email message with for this particular recipient.
     */
    public function __construct($email, $name = '', $params = [])
    {
        $this->email = $email;
        $this->name = $name;
        $this->params = $params;
    }

    /**
     * Add a CC for this primary recipient.
     *
     * @param string $email The CC recipient email address.
     * @param string $name  The CC recipient name.
     */
    public function cc($email, $name = '')
    {
        $this->cc[] = [
            'email' => $email,
            'name'  => $name
        ];
    }

    /**
     * Add a BCC for this primary recipient.
     *
     * @param string $email The BCC recipient email address.
     * @param string $name  The BCC recipient name.
     */
    public function bcc($email, $name = '')
    {
        $this->bcc[] = [
            'email' => $email,
            'name'  => $name
        ];
    }

    /**
     * Get the primary recipient email address.
     *
     * @return string the primary recipient email address.
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the primary recipient name.
     *
     * @return string the primary recipient name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the parameters to use when rendering the template this recipient.
     *
     * @return array The parameters (name => value) to use when rendering an email template for this recipient.
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get the list of CCs for this recipient.
     *
     * @return array A list of CCs for this recipient.  Each CC is an associative array with `email` and `name` properties.
     */
    public function getCCs()
    {
        return $this->cc;
    }

    /**
     * Get the list of BCCs for this recipient.
     *
     * @return array A list of BCCs for this recipient.  Each BCC is an associative array with `email` and `name` properties.
     */
    public function getBCCs()
    {
        return $this->bcc;
    }
}
