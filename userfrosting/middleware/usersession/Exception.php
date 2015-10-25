<?php

namespace UserFrosting;

/**
 * Expired Authentication Exception.
 */
class AuthExpiredException extends \Exception
{
    /**
     * Public constructor.
     */
    public function __construct()
    {
        $message = 'Authentication has expired';
        $code = 403;
        parent::__construct($message, $code);
    }
}

/**
 * Compromised Authentication Exception.
 */
class AuthCompromisedException extends \Exception
{
    /**
     * Public constructor.
     */
    public function __construct()
    {
        $message = 'Someone else has used your login information to acccess this page!';
        $code = 403;
        parent::__construct($message, $code);
    }
}

/**
 * Account Invalid Exception.
 */
class AccountInvalidException extends \Exception
{
    /**
     * Public constructor.
     */
    public function __construct()
    {
        $message = 'The account you were logged in with no longer exists.  We have logged you out now, so please try again.';
        $code = 403;
        parent::__construct($message, $code);
    }
}

/**
 * Invalid Database Exception.
 */
class DatabaseInvalidException extends \Exception
{
    /**
     * Public constructor.
     */
    public function __construct($message, $code, $previous)
    {
        $message = 'The database is in an invalid state: ' . $message;
        parent::__construct($message, 500, $previous);
    }
}
