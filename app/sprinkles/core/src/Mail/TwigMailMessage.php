<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
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
     * @var \Slim\Views\Twig The view object, used to render mail templates.
     */
    protected $view;
    
    /**
     * @var Twig_Template The Twig template object, to source the content for this message.
     */
    protected $template;
    
    /**
     * Create a new TwigMailMessage instance.
     *
     * @param Slim\Views\Twig $view The view object used to render mail templates.
     */
    public function __construct($view, $filename = null)
    {
        $this->view = $view;
        
        if ($filename !== null) {
            $twig = $this->view->getEnvironment();
            $this->template = $twig->loadTemplate($filename);
        }
    }
    
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function setParams($params = [])
    {
        // Must manually merge in global variables for block rendering
        // TODO: should we keep this separate from the local parameters?
        $twig = $this->view->getEnvironment();
        $this->params = array_merge($twig->getGlobals(), $params);
    }
    
    public function renderSubject()
    {
        return $this->template->renderBlock('subject', $this->params);
    }
    
    public function renderBody()
    {
        return $this->template->renderBlock('body', $this->params);
    }
}
