<?php

/**
 * BadRequestException
 *
 * This exception should be thrown when a user has submitted an ill-formed request, or other incorrect data.
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @author    Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Support\Exception;

class BadRequestException extends HttpException
{

    /**
     * @var integer Default HTTP error code associated with this exception.
     */
    protected $http_error_code = 400;
    
    /**
     * @var string Default user-viewable error message associated with this exception.
     */    
    protected $default_message = "NO_DATA";
}
