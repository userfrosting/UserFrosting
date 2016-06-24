<?php

namespace UserFrosting;

/**
 * Notification Class
 *
 * A class for sending email/message notifications to users.  For now, only supports email recipients.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com/
 */
class Notification {
    
    /**
     * @var Slim The Slim app, containing configuration info
     */
    public static $app;
    
    /**
     * @var Twig_Template The Twig template object, to source the content for this message.
     */
    protected $template;

    /**
     * @var string The email address that this notification should come from, for email notifications.
     */
    protected $from_email = "";
    
    /**
     * @var string The name that this notification should come from, for email notifications.
     */    
    protected $from_name = "";
    
    /**
     * @var string The reply-to email for this notification, for email notifications.
     */    
    protected $reply_to_email = "";
    
    /**
     * @var string The reply-to name for this notification, for email notifications.
     */       
    protected $reply_to_name = "";
    
    /**
     * @var array[EmailRecipient] A list of email recipients for this notification.
     */       
    protected $email_recipients = [];
    
    /**
     * Create a new Notification instance.
     *
     * @param Twig_Template $template The Twig template object to use to create this notification.
     * @todo Allow other content sources (string, database, etc)
     */
    public function __construct($template){
        $this->template = $template;
        if (!static::$app)
            static::$app = UserFrosting::getInstance();
    }

    /**
     * Set the sender of this notification as the website.
     *
     * This uses the site setting "admin_email" as the "from" field, and "site_title" as the "from" name.
     * @param string $reply_to_email The reply-to email address.  Will default to the admin_email if not set.
     * @param string $reply_to_name The reply-to name.  Will default to the site_title if not set.     
     */
    public function fromWebsite($reply_to_email = null, $reply_to_name = null){
        $this->from_email = static::$app->site->admin_email;
        $this->from_name = static::$app->site->site_title;
        $this->reply_to_email = $reply_to_email ? $reply_to_email : static::$app->site->admin_email;
        $this->reply_to_name = $reply_to_name ? $reply_to_name : static::$app->site->site_title;
    }

    /**
     * Set the sender of this notification as a specific user
     *
     * For email, this uses the user's email as the "from" and "reply-to" emails, and the user's display_name as the "from" and "reply-to" names.
     * @param User $user The user to send from.   
     */     
    public function fromUser($user){
        $this->from_email = $this->reply_to_email = $user->email;
        $this->from_name = $this->reply_to_name = $user->display_name;
    }
    
    /**
     * Set the sender of this notification as an arbitary email address and name.
     *
     * This uses the site setting "admin_email" as the "from" field, and "site_title" as the "from" name.
     * @param string $email The sender email address.
     * @param string $name The sender name.
     * @param string $reply_to_email The reply-to email address.  Will default to $email if not set.
     * @param string $reply_to_name The reply-to name.  Will default to $name if not set.     
     */    
    public function from($email, $name = "", $reply_to_email = null, $reply_to_name = null){
        $this->from_email = $email;
        $this->from_name = $name;
        $this->reply_to_email = $reply_to_email ? $reply_to_email : $email;
        $this->reply_to_name = $reply_to_name ? $reply_to_name : $name;
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
    public function addEmailRecipient($email, $name = "", $params = []){
        $r = new EmailRecipient($email, $name, $params);
        $this->email_recipients[] = $r;
        return $r;
    }
    
    /**
     * Send the notification.
     *
     * This method sends the notification to all recipients, rendering the template with the corresponding parameters for each recipient.
     * @throws phpmailerException The message could not be sent.
     */
    public function send(){
        $mail = new \PHPMailer(true);
        
        $mail->From = $this->from_email;
        $mail->FromName = $this->from_name;
        $mail->addReplyTo($this->reply_to_email, $this->reply_to_name);
        
        $twig = static::$app->view()->getEnvironment();
        
        // Loop through email recipients, sending customized content to each one
        foreach ($this->email_recipients as $recipient){
            $mail->addAddress($recipient->getEmail(), $recipient->getName());
            
            // Add any CCs and BCCs
            if ($recipient->getCCs()){
                foreach($recipient->getCCs() as $cc){
                    $mail->addCC($cc['email'], $cc['name']);
                }
            }
            
            if ($recipient->getBCCs()){
                foreach($recipient->getBCCs() as $bcc){
                    $mail->addBCC($bcc['email'], $bcc['name']);
                }
            }
            
            $params = $recipient->getParams();
            
            // Must manually merge in global variables for block rendering
            $params = array_merge($twig->getGlobals(), $params);
            $mail->Subject = $this->template->renderBlock('subject', $params);
            $mail->Body    = $this->template->renderBlock('body', $params);
            
            $mail->isHTML(true);                                  // Set email format to HTML       
            
            // Send mail as SMTP, if desired
            if (static::$app->config('mail') == 'smtp'){
                $config = static::$app->config('smtp');
                $mail->isSMTP(true);
                $mail->Host =       $config['host'];
                $mail->Port =       $config['port'];
                $mail->SMTPAuth =   $config['auth'];
                $mail->SMTPSecure = $config['secure'];
                $mail->Username =   $config['user'];
                $mail->Password =   $config['pass'];
            }
            
            // Try to send the mail.  Will throw an exception on failure.
            $mail->send();
            
            // Clear all PHPMailer recipients (from the message for this iteration)
            $mail->clearAllRecipients();
        }
    }
}

