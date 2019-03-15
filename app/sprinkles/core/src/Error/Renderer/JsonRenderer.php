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
 * Default JSON Error Renderer
 */
class JsonRenderer extends ErrorRenderer
{
    /**
     * @return string
     */
    public function render()
    {
        $message = $this->exception->getMessage();

        return $this->formatExceptionPayload($message);
    }

    /**
     * @param  string $message
     * @return string
     */
    public function formatExceptionPayload($message)
    {
        $e = $this->exception;
        $error = ['message' => $message];

        if ($this->displayErrorDetails) {
            $error['exception'] = [];
            do {
                $error['exception'][] = $this->formatExceptionFragment($e);
            } while ($e = $e->getPrevious());
        }

        return json_encode($error, JSON_PRETTY_PRINT);
    }

    /**
     * @param  \Exception|\Throwable $e
     * @return array
     */
    public function formatExceptionFragment($e)
    {
        return [
            'type'    => get_class($e),
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ];
    }
}
