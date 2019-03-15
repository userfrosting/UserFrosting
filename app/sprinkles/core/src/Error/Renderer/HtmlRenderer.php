<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Renderer;

class HtmlRenderer extends ErrorRenderer
{
    /**
     * Render HTML error report.
     *
     * @return string
     */
    public function render()
    {
        $title = 'UserFrosting Application Error';

        if ($this->displayErrorDetails) {
            $html = '<p>The application could not run because of the following error:</p>';
            $html .= '<h2>Details</h2>';
            $html .= $this->renderException($this->exception);

            $html .= '<h2>Your request</h2>';
            $html .= $this->renderRequest();

            $html .= '<h2>Response headers</h2>';
            $html .= $this->renderResponseHeaders();

            $exception = $this->exception;
            while ($exception = $exception->getPrevious()) {
                $html .= '<h2>Previous exception</h2>';
                $html .= $this->renderException($exception);
            }
        } else {
            $html = '<p>A website error has occurred. Sorry for the temporary inconvenience.</p>';
        }

        $output = sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            '<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,' .
            'sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{' .
            'display:inline-block;width:65px;}table,th,td{font:12px Helvetica,Arial,Verdana,' .
            'sans-serif;border:1px solid black;border-collapse:collapse;padding:5px;text-align: left;}' .
            'th{font-weight:600;}' .
            '</style></head><body><h1>%s</h1>%s</body></html>',
            $title,
            $title,
            $html
        );

        return $output;
    }

    /**
     * Render a summary of the exception.
     *
     * @param  \Exception $exception
     * @return string
     */
    public function renderException(\Exception $exception)
    {
        $html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));

        if (($code = $exception->getCode())) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        if (($message = $exception->getMessage())) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($message));
        }

        if (($file = $exception->getFile())) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }

        if (($line = $exception->getLine())) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }

        if (($trace = $exception->getTraceAsString())) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', htmlentities($trace));
        }

        return $html;
    }

    /**
     * Render HTML representation of original request.
     *
     * @return string
     */
    public function renderRequest()
    {
        $method = $this->request->getMethod();
        $uri = $this->request->getUri();
        $params = $this->request->getParams();
        $requestHeaders = $this->request->getHeaders();

        $html = '<h3>Request URI:</h3>';

        $html .= sprintf('<div><strong>%s</strong> %s</div>', $method, $uri);

        $html .= '<h3>Request parameters:</h3>';

        $html .= $this->renderTable($params);

        $html .= '<h3>Request headers:</h3>';

        $html .= $this->renderTable($requestHeaders);

        return $html;
    }

    /**
     * Render HTML representation of response headers.
     *
     * @return string
     */
    public function renderResponseHeaders()
    {
        $html = '<h3>Response headers:</h3>';
        $html .= '<em>Additional response headers may have been set by Slim after the error handling routine.  Please check your browser console for a complete list.</em><br>';

        $html .= $this->renderTable($this->response->getHeaders());

        return $html;
    }

    /**
     * Render HTML representation of a table of data.
     *
     * @param  mixed[] $data the array of data to render.
     * @return string
     */
    protected function renderTable($data)
    {
        $html = '<table><tr><th>Name</th><th>Value</th></tr>';
        foreach ($data as $name => $value) {
            $value = print_r($value, true);
            $html .= "<tr><td>$name</td><td>$value</td></tr>";
        }
        $html .= '</table>';

        return $html;
    }
}
