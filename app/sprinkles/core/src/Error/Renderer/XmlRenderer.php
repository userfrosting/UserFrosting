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
 * Default XML Error Renderer
 */
class XmlRenderer extends ErrorRenderer
{
    /**
     * @return string
     */
    public function render()
    {
        $e = $this->exception;
        $xml = "<error>\n  <message>UserFrosting Application Error</message>\n";
        if ($this->displayErrorDetails) {
            do {
                $xml .= "  <exception>\n";
                $xml .= '    <type>' . get_class($e) . "</type>\n";
                $xml .= '    <code>' . $e->getCode() . "</code>\n";
                $xml .= '    <message>' . $this->createCdataSection($e->getMessage()) . "</message>\n";
                $xml .= '    <file>' . $e->getFile() . "</file>\n";
                $xml .= '    <line>' . $e->getLine() . "</line>\n";
                $xml .= "  </exception>\n";
            } while ($e = $e->getPrevious());
        }
        $xml .= '</error>';

        return $xml;
    }

    /**
     * Returns a CDATA section with the given content.
     *
     * @param  string $content
     * @return string
     */
    private function createCdataSection($content)
    {
        return sprintf('<![CDATA[%s]]>', str_replace(']]>', ']]]]><![CDATA[>', $content));
    }
}
