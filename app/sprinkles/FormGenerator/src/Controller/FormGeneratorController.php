<?php

/*
 * UserFrosting Form Generator
 *
 * @link      https://github.com/lcharette/UF_FormGenerator
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_FormGenerator/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\FormGenerator\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;

/**
 * FormGeneratorController Class.
 *
 * Controller class for /forms/confirm/* URLs.  Handles rendering the confirm dialog
 */
class FormGeneratorController extends SimpleController
{
    /**
     * Display the confirmation dialog.
     * Request type: GET.
     *
     * @param Request  $request
     * @param Response $response
     * @param string[] $args
     */
    public function confirm(Request $request, Response $response, array $args): void
    {
        $this->ci->view->render($response, 'FormGenerator/confirm.html.twig', $request->getQueryParams());
    }
}
