<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
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
     * @var string The current sender email address.
     */
    protected $fromEmail = "";
    
    /**
     * @var string The current sender name.
     */    
    protected $fromName = null;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @var \PHPMailer
     */
    protected $phpMailer;
    
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
     * Create a new Mailer instance.
     *
     * @param 
     */
    public function __construct($logger, $config = [])
    {
        $this->logger = $logger;
        
        // 'true' tells PHPMailer to use exceptions instead of error codes
        $this->phpMailer = new \PHPMailer(true);
        
        // Configuration options
        if (isset($config['mailer'])) {
            if (!in_array($config['mailer'], ['smtp', 'mail', 'qmail', 'sendmail'])) {
                throw new \phpmailerException("'mailer' must be one of 'smtp', 'mail', 'qmail', or 'sendmail'.");
            }
            
            if ($config['mailer'] == 'smtp') {
                $this->phpMailer->isSMTP(true);
                $this->phpMailer->Host =       $config['host'];
                $this->phpMailer->Port =       $config['port'];
                $this->phpMailer->SMTPAuth =   $config['auth'];
                $this->phpMailer->SMTPSecure = $config['secure'];
                $this->phpMailer->Username =   $config['username'];
                $this->phpMailer->Password =   $config['password'];
                $this->phpMailer->SMTPDebug =  $config['smtp_debug'];
                
                if (isset($config['smtp_options'])) {
                    $this->phpMailer->SMTPOptions = $config['smtp_options'];
                }
            }
            
            // Set any additional message-specific options
            // TODO: enforce which options can be set through this subarray
            if (isset($config['message_options'])) {
                $this->setOptions($config['message_options']);
            }
        }
        
        // Pass logger into phpMailer object
        $this->phpMailer->Debugoutput = function($message, $level) {
            $this->logger->debug($message);
        };
    }

    /**
     * Add an email recipient.
     *
     * Each "recipient" added with this method will be sent out as a separate email.  To CC or BCC recipients on the same email,
     * use the ->cc and ->bcc methods on the EmailRecipient object returned by this method.
     * @param string $email The primary recipient email address.
     * @param string $name The primary recipient name.
     * @param array $params An array of template parameters to render the email message with for this particular recipient.
     * @return EmailRecipient The EmailRecipient object created for this recipient, which you can call ->cc() or ->bcc() on to add CC and BCC.
     */
    public function addEmailRecipient($email, $name = "", $params = [])
    {
        $r = new EmailRecipient($email, $name, $params);
        $this->recipients[] = $r;
        return $r;
    }
    
    /**
     * Set sender information for this message.
     *
     * This uses the site setting "admin_email" as the "from" field, and "site_title" as the "from" name.
     * @param string $fromEmail The sender email address.
     * @param string $fromName The sender name.
     * @param string $replyToEmail The reply-to email address.  Will default to $email if not set.
     * @param string $replyToName The reply-to name.  Will default to $name if not set.     
     */
    public function from($fromInfo = [])
    {
        $this->setFromEmail(isset($fromInfo['email']) ? $fromInfo['email'] : "");
        $this->setFromName(isset($fromInfo['name']) ? $fromInfo['name'] : null);
        $this->setReplyEmail(isset($fromInfo['reply_email']) ? $fromInfo['reply_email'] : null);
        $this->setReplyName(isset($fromInfo['reply_name']) ? $fromInfo['reply_name'] : null);
        
        return $this;
    }
    
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    public function getFromName()
    {
        return isset($this->fromName) ? $this->fromName : $this->getFromEmail();
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
    
    public function getRecipients()
    {
        return $this->recipients;
    }
    
    public function getReplyEmail()
    {
        return isset($this->replyEmail) ? $this->replyEmail : $this->getFromEmail();
    }

    public function getReplyName()
    {
        return isset($this->replyName) ? $this->replyName : $this->getFromName();
    }
    
    /**
     * Send a message.
     *
     * Sends a separate email to each recipient.  If the message object supports message templates, this will
     * render the template with the corresponding placeholder values for each recipient.
     * @param MailMessageInterface $message
     * @throws phpmailerException The message could not be sent.
     */
    public function send($message)
    {        
        $this->phpMailer->From = $this->getFromEmail();
        $this->phpMailer->FromName = $this->getFromName();
        $this->phpMailer->addReplyTo($this->getReplyEmail(), $this->getReplyName());
        
        // Loop through email recipients, sending customized content to each one
        foreach ($this->recipients as $recipient){
            $this->phpMailer->addAddress($recipient->getEmail(), $recipient->getName());
            
            // Add any CCs and BCCs
            if ($recipient->getCCs()){
                foreach($recipient->getCCs() as $cc){
                    $this->phpMailer->addCC($cc['email'], $cc['name']);
                }
            }
            
            if ($recipient->getBCCs()){
                foreach($recipient->getBCCs() as $bcc){
                    $this->phpMailer->addBCC($bcc['email'], $bcc['name']);
                }
            }
            
            $message->setParams($recipient->getParams());
            
            $this->phpMailer->Subject = $message->renderSubject();
            $this->phpMailer->Body    = $message->renderBody();
            
            // Try to send the mail.  Will throw an exception on failure.
            $this->phpMailer->send();
            
            // Clear recipients from the PHPMailer object for this iteration,
            // so that we can send a separate email to the next recipient.
            $this->phpMailer->clearAllRecipients();
        }
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
    
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
        return $this;
    }
    
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
    
    public function setReplyEmail($replyEmail)
    {
        $this->replyEmail = $replyEmail;
        return $this;
    }
    
    public function setReplyName($replyName)
    {
        $this->replyName = $replyName;
        return $this;
    }    
}

