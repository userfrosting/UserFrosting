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
 * Hidden input type class.
 * Manage the default attributes required to display an hidden input type.
 */
class Hidden extends Input
{
    /**
     * {@inheritdoc}
     */
    protected function applyTransformations(): void
    {
        $this->element = array_merge([
            'value' => $this->getValue(),
            'name'  => $this->name,
            'id'    => 'field_' . $this->name,
        ], $this->element);
    }
}
