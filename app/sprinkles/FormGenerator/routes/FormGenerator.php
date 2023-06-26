<?php

/*
 * UserFrosting Form Generator
 *
 * @link      https://github.com/lcharette/UF_FormGenerator
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_FormGenerator/blob/master/LICENSE (MIT License)
 */

global $app;

$app->get('/forms/confirm', 'UserFrosting\Sprinkle\FormGenerator\Controller\FormGeneratorController:confirm');
