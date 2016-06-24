<?php

/**
 * UserMessage
 *
 * A user-viewable message, consisting of a message string or message token, and zero or more parameters for the message.
 * Parameters can be used, for example, to fill in placeholders in dynamically generated messages.
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @author    Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Support\Message;

class UserMessage
{

    /**
     * @var string The user-viewable error message.
     */       
    public $message;
    
    /**
     * @var array The parameters to be filled in for any placeholders in the message.
     */         
    public $parameters = [];
    
    /**
     * Public constructor.
     *
     * @param string $message
     * @param array $parameters The parameters to be filled in for any placeholders in the message.
     */    
    public function __construct($message, $parameters = [])
    {
        $this->message = $message;
        $this->parameters = $parameters;
    }

}
