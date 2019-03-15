<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Renderer;

/**
 * Plain Text Error Renderer
 */
class PlainTextRenderer extends ErrorRenderer
{
    public function render()
    {
        if ($this->displayErrorDetails) {
            return $this->formatExceptionBody();
        }

        return $this->exception->getMessage();
    }

    /**
     * Format Exception Body
     * @return string
     */
    public function formatExceptionBody()
    {
        $e = $this->exception;

        $text = 'UserFrosting Application Error:' . PHP_EOL;
        $text .= $this->formatExceptionFragment($e);

        while ($e = $e->getPrevious()) {
            $text .= PHP_EOL . 'Previous Error:' . PHP_EOL;
            $text .= $this->formatExceptionFragment($e);
        }

        return $text;
    }

    /**
     * @param  \Exception|\Throwable $e
     * @return string
     */
    public function formatExceptionFragment($e)
    {
        $text = sprintf('Type: %s' . PHP_EOL, get_class($e));

        if ($code = $e->getCode()) {
            $text .= sprintf('Code: %s' . PHP_EOL, $code);
        }
        if ($message = $e->getMessage()) {
            $text .= sprintf('Message: %s' . PHP_EOL, htmlentities($message));
        }
        if ($file = $e->getFile()) {
            $text .= sprintf('File: %s' . PHP_EOL, $file);
        }
        if ($line = $e->getLine()) {
            $text .= sprintf('Line: %s' . PHP_EOL, $line);
        }
        if ($trace = $e->getTraceAsString()) {
            $text .= sprintf('Trace: %s', $trace);
        }

        return $text;
    }
}
