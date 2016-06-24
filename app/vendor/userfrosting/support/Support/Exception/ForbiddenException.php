<?php

/**
 * ForbiddenException
 *
 * This exception should be thrown when a user has attempted to perform an unauthorized action.
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @author    Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Support\Exception;

class ForbiddenException extends HttpException
{

    /**
     * @var integer Default HTTP error code associated with this exception.
     */
    protected $http_error_code = 403;
    
    /**
     * @var string Default user-viewable error message associated with this exception.
     */        
    protected $default_message = "ACCESS_DENIED";
}
