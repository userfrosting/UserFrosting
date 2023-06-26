<?php

/*
 * UserFrosting Form Generator
 *
 * @link      https://github.com/lcharette/UF_FormGenerator
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_FormGenerator/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\FormGenerator\Element;

/**
 * InputInterface.
 *
 * Common Interface for Form elements
 */
interface InputInterface
{
    /**
     * Return the parsed input attributes.
     * This is passed to the Twig template to generate the actual HTML elements.
     *
     * @return array<string,string>
     */
    public function parse(): array;
}
