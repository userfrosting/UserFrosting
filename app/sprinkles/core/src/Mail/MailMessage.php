<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Mail;

/**
 * MailMessage Class
 *
 * Represents a basic mail message, containing a static subject and body.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class MailMessage
{
    protected $body;
       
    protected $subject;
    
    /**
     * Create a new MailMessage instance.
     *
     * @param Slim\Views\Twig The view object used to render mail templates.
     */
    public function __construct($subject = "", $body = "")
    {
        $this->subject = $subject;
        $this->body = $body;
    }
    
    public function renderBody()
    {
        return $this->body;
    }
    
    public function renderSubject()
    {
        return $this->subject;
    }
    
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
}
