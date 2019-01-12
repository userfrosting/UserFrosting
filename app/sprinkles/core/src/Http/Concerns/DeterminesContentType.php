<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Http\Concerns;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Trait for classes that need to determine a request's accepted content type(s).
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
trait DeterminesContentType
{
    /**
     * Known handled content types
     *
     * @var array
     */
    protected $knownContentTypes = [
        'application/json',
        'application/xml',
        'text/xml',
        'text/html',
        'text/plain'
    ];

    /**
     * Determine which content type we know about is wanted using Accept header
     *
     * Note: This method is a bare-bones implementation designed specifically for
     * Slim's error handling requirements. Consider a fully-feature solution such
     * as willdurand/negotiation for any other situation.
     *
     * @param  ServerRequestInterface $request
     * @param  bool                   $ajaxDebug
     * @return string
     */
    protected function determineContentType(ServerRequestInterface $request, $ajaxDebug = false)
    {
        // For AJAX requests, if AJAX debugging is turned on, always return html
        if ($ajaxDebug && $request->isXhr()) {
            return 'text/html';
        }

        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);
        $count = count($selectedContentTypes);

        if ($count) {
            $current = current($selectedContentTypes);

            /*
             * Ensure other supported content types take precedence over text/plain
             * when multiple content types are provided via Accept header.
             */
            if ($current === 'text/plain' && $count > 1) {
                return next($selectedContentTypes);
            }

            return $current;
        }

        if (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) {
            $mediaType = 'application/' . $matches[1];
            if (in_array($mediaType, $this->knownContentTypes)) {
                return $mediaType;
            }
        }

        return 'text/html';
    }
}
