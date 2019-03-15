<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Alert;

use UserFrosting\I18n\MessageTranslator;
use UserFrosting\Session\Session;

/**
 * SessionAlertStream Class
 * Implements a message stream for use between HTTP requests, with i18n support via the MessageTranslator class
 * Using the session storage to store the alerts
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SessionAlertStream extends AlertStream
{
    /**
     * @var Session We use the session object so that added messages will automatically appear in the session.
     */
    protected $session;

    /**
     * Create a new message stream.
     *
     * @param string                 $messagesKey Store the messages under this key
     * @param MessageTranslator|null $translator
     * @param Session                $session
     */
    public function __construct($messagesKey, MessageTranslator $translator = null, Session $session)
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
        return $this->session[$this->messagesKey] ?: [];
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
     *
     * @param string $messages The message
     */
    protected function saveMessages($messages)
    {
        $this->session[$this->messagesKey] = $messages;
    }
}
