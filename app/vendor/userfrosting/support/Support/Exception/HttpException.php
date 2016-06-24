<?php
/**
 * HttpException
 *
 * Child classes of HttpException should be thrown when we want to return
 * an HTTP status code and user-viewable message(s) during the application lifecycle.
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @author    Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Support\Exception;

use UserFrosting\Support\Message\UserMessage as UserMessage;

class HttpException extends \Exception
{

    /**
     * @var integer
     * Every exception that inherits from this class should have a hardcoded http error code.
     */    
    protected $http_error_code = 500;
    
    /**
     * @var array[UserMessage]
     */
    protected $messages = [];
    
    /**
     * @var string Default user-viewable error message associated with this exception.
     */        
    protected $default_message = "SERVER_ERROR";
    
    /**
     * Return the HTTP status code associated with this exception.
     *
     * @return int
     */
    public function getHttpErrorCode()
    {
        return $this->http_error_code;
    }
    
    /**
     * Return the user-viewable messages associated with this exception.
     *
     * @return array[UserMessage]
     */    
    public function getUserMessages()
    {
        if (empty($this->messages)){
           $this->addUserMessage($this->default_message);
        }
        
        return $this->messages;
    }
    
    /**
     * Add a user-viewable message for this exception.
     *
     * @param UserMessage|string $message
     * @param array $parameters The parameters to be filled in for any placeholders in the message.
     */     
    public function addUserMessage($message, $parameters = [])
    {
        if ($message instanceof UserMessage){
            $this->messages[] = $message;
        } else {
            // Tight coupling is probably OK here
            $this->messages[] = new UserMessage($message, $parameters);
        }
    }
}
