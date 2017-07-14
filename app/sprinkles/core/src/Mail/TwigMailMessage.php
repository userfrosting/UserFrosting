<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Mail;

/**
 * MailMessage Class
 *
 * Represents a basic mail message, containing a static subject and body.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class TwigMailMessage extends MailMessage
{
    /**
     * @var mixed[] A list of Twig placeholder values to use when rendering this message.
     */
    protected $params;

    /**
     * @var Twig_Template The Twig template object, to source the content for this message.
     */
    protected $template;

    /**
     * @var \Slim\Views\Twig The view object, used to render mail templates.
     */
    protected $view;

    /**
     * Create a new TwigMailMessage instance.
     *
     * @param Slim\Views\Twig $view The Twig view object used to render mail templates.
     * @param string $filename optional Set the Twig template to use for this message.
     */
    public function __construct($view, $filename = null)
    {
        $this->view = $view;

        $twig = $this->view->getEnvironment();
        // Must manually merge in global variables for block rendering
        // TODO: should we keep this separate from the local parameters?
        $this->params = $twig->getGlobals();

        if ($filename !== null) {
            $this->template = $twig->loadTemplate($filename);
        }
    }

    /**
     * Merge in any additional global Twig variables to use when rendering this message.
     *
     * @param mixed[] $params
     */
    public function addParams($params = [])
    {
        $this->params = array_replace_recursive($this->params, $params);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function renderSubject($params = [])
    {
        $params = array_replace_recursive($this->params, $params);
        return $this->template->renderBlock('subject', $params);
    }

    /**
     * {@inheritDoc}
     */
    public function renderBody($params = [])
    {
        $params = array_replace_recursive($this->params, $params);
        return $this->template->renderBlock('body', $params);
    }

    /**
     * Sets the Twig template object for this message.
     *
     * @param Twig_Template $template The Twig template object, to source the content for this message.
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
}
