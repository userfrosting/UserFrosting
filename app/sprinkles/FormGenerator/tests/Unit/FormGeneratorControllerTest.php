<?php

/*
 * UserFrosting Form Generator
 *
 * @link      https://github.com/lcharette/UF_FormGenerator
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_FormGenerator/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\FormGenerator\Tests\Unit;

use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Sprinkle\FormGenerator\Controller\FormGeneratorController;
use UserFrosting\Tests\TestCase;

/**
 * FormGeneratorControllerTest
 * The FormGenerator unit tests for supplied controllers.
 */
class FormGeneratorControllerTest extends TestCase
{
    use withController;

    /**
     * @return FormGeneratorController
     */
    public function testConstructor(): FormGeneratorController
    {
        $controller = new FormGeneratorController($this->ci);
        $this->assertInstanceOf(FormGeneratorController::class, $controller);

        return $controller;
    }

    /**
     * @depends testConstructor
     * @param FormGeneratorController $controller
     */
    public function testConfirm(FormGeneratorController $controller): void
    {
        $request = $this->getRequest();
        $result = $this->getResponse();
        $args = [];
        $controller->confirm($request, $result, $args);

        // Perform asertions
        $body = (string) $result->getBody();
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertNotSame('', $body);
    }
}
