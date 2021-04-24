<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Alert;

use UserFrosting\I18n\Translator;
use UserFrosting\Session\Session;

/**
 * SessionAlertStream Class
 * Implements a message stream for use between HTTP requests, with i18n support via the Translator class
 * Using the session storage to store the alerts.
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
     * @param string          $messagesKey Store the messages under this key
     * @param Translator|null $translator
     * @param Session         $session
     */
    public function __construct($messagesKey, Translator $translator = null, Session $session)
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
        return $this->session->get($this->messagesKey) ?: [];
    }

    /**
     * Clear all messages from this message stream.
     */
    public function resetMessageStream()
    {
        $this->session->set($this->messagesKey, []);
    }

    /**
     * Save messages to the stream.
     *
     * @param array $messages The message
     */
    protected function saveMessages(array $messages)
    {
        $this->session->set($this->messagesKey, $messages);
    }
}
