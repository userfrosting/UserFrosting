<?php

namespace UserFrosting;

/**
 * Invalid Database Exception.
 */
class DatabaseInvalidException extends \Exception {
    /**
     * Public constructor.
     */
    public function __construct($message, $code, $previous)
    {
        $message = 'The database is in an invalid state: ' . $message;
        parent::__construct($message, 500, $previous);
    }
}
