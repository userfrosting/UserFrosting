<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Log;

use Monolog\Formatter\LineFormatter;

/**
 * Monolog formatter for pretty-printing arrays and objects.
 *
 * This class extends the basic Monolog LineFormatter class, and provides basically the same functionality but with one exception:
 * if the second parameter of any logging method (debug, error, info, etc) is an array, it will print it as a nicely formatted,
 * multi-line JSON object instead of all on a single line.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class MixedFormatter extends LineFormatter
{
    /**
     * Return the JSON representation of a value
     *
     * @param  mixed             $data
     * @param  bool              $ignoreErrors
     * @throws \RuntimeException if encoding fails and errors are not ignored
     * @return string
     */
    protected function toJson($data, $ignoreErrors = false)
    {
        // suppress json_encode errors since it's twitchy with some inputs
        if ($ignoreErrors) {
            return @$this->jsonEncodePretty($data);
        }

        $json = $this->jsonEncodePretty($data);

        if ($json === false) {
            $json = $this->handleJsonError(json_last_error(), $data);
        }

        return $json;
    }

    /**
     * @param  mixed  $data
     * @return string JSON encoded data or null on failure
     */
    private function jsonEncodePretty($data)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return json_encode($data);
    }
}
