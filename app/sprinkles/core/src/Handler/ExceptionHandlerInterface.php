<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Handler;

/**
 * All exception handlers must implement this interface.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
interface ExceptionHandlerInterface
{
    public function ajaxHandler($request, $response, $exception);
    public function standardHandler($request, $response, $exception);
    public function getLogFlag();
}
