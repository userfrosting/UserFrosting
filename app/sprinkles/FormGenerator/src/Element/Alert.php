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
 * Alert input type class.
 * Manage the default attributes required to display an alert.
 */
class Alert extends Input
{
    /**
     * {@inheritdoc}
     */
    protected function applyTransformations(): void
    {
        $this->element = array_merge([
            'class' => 'alert-danger',
            'icon'  => 'fa-ban',
            'value' => $this->getValue(),
            'name'  => $this->name,
        ], $this->element);
    }
}
