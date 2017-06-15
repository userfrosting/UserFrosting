<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Alert;

/**
 * SessionAlertStream Class
 *
 * Implements a message stream for use between HTTP requests, with i18n support via the MessageTranslator class
 * Using the session storage to store the alerts
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SessionAlertStream extends AlertStream
{
    /**
     * @var UserFrosting\Session\Session We use the session object so that added messages will automatically appear in the session.
     */
    protected $session;

    /**
     * Create a new message stream.
     */
    public function __construct($messagesKey, $translator = null, $session)
    {
        $this->session = $session;
        parent::__construct($messagesKey, $translator);
    }

    /**
     * Get the messages from this message stream.
     *
     * @return array An array of messages, each of which is itself an array containing "type" and "message" fields.
     */
    public function messages()
    {
        return $this->session[$this->messagesKey];
    }

    /**
     * Clear all messages from this message stream.
     */
    public function resetMessageStream()
    {
        $this->session[$this->messagesKey] = [];
    }

    /**
     * Save messages to the stream
     */
    protected function saveMessages($messages)
    {
        $this->session[$this->messagesKey] = $messages;
    }
}
