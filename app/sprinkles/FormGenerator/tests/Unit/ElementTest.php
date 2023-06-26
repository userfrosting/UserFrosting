<?php

/*
 * UserFrosting Form Generator
 *
 * @link      https://github.com/lcharette/UF_FormGenerator
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_FormGenerator/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\FormGenerator\Tests\Unit;

use UserFrosting\Fortress\RequestSchema\RequestSchemaRepository;
use UserFrosting\Sprinkle\FormGenerator\Element;
use UserFrosting\Sprinkle\FormGenerator\Element\InputInterface;
use UserFrosting\Support\Repository\Loader\YamlFileLoader;
use UserFrosting\Tests\TestCase;

/**
 * ElementTest
 *
 * Perform test for UserFrosting\Sprinkle\FormGenerator\Element\*
 */
class ElementTest extends TestCase
{
    /**
     * Run the test for each Element using the dataprovider
     *
     * @param string               $elementName The element in `good.json` needs to be tested
     * @param string               $class       The Element class
     * @param string|int|null      $value
     * @param array<string,string> $expected    Expected result
     *
     * @dataProvider elementsProvider
     */
    public function testElement(string $elementName, string $class, $value, array $expected): void
    {
        // Get Schema
        $loader = new YamlFileLoader(__DIR__ . '/data/elements.json');
        $schema = new RequestSchemaRepository($loader->load());

        // Get InputInterface from the `$elementName` in the schema
        $inputSchema = $schema[$elementName]['form'];

        /** @var InputInterface */
        $input = new $class($elementName, $inputSchema, $value);

        // Test instanceof $input
        $this->assertInstanceof(InputInterface::class, $input);

        // Test getters
        $this->assertSame($elementName, $input->getName());
        $this->assertSame($inputSchema, $input->getElement());

        // Parse the input
        $text = $input->parse();

        // We test the generated result
        $this->assertSame($expected, $text);
    }

    /**
     * Data provider for testElement.
     *
     * @return array<string|null|array>
     */
    public function elementsProvider(): array
    {
        return [
            // TEXT - With Null value
            [
                'name',
                Element\Text::class,
                null,
                [
                    'autocomplete' => 'off',
                    'class'        => 'form-control',
                    'value'        => '',
                    'name'         => 'name',
                    'id'           => 'field_name',
                    'type'         => 'text',
                    'label'        => 'Project Name',
                    'icon'         => 'fa-flag',
                    'placeholder'  => 'Project Name',
                ],
            ],
            // TEXT - With string value
            [
                'name',
                Element\Text::class,
                'The Bar project',
                [
                    'autocomplete' => 'off',
                    'class'        => 'form-control',
                    'value'        => 'The Bar project',
                    'name'         => 'name',
                    'id'           => 'field_name',
                    'type'         => 'text',
                    'label'        => 'Project Name',
                    'icon'         => 'fa-flag',
                    'placeholder'  => 'Project Name',
                ],
            ],
            // TEXT - With int value
            [
                'name',
                Element\Text::class,
                123,
                [
                    'autocomplete' => 'off',
                    'class'        => 'form-control',
                    'value'        => '123', // NOTE Converted to string here
                    'name'         => 'name',
                    'id'           => 'field_name',
                    'type'         => 'text',
                    'label'        => 'Project Name',
                    'icon'         => 'fa-flag',
                    'placeholder'  => 'Project Name',
                ],
            ],
            // TEXT - With empty value
            [
                'owner',
                Element\Text::class,
                '',
                [
                    'autocomplete' => 'off',
                    'class'        => 'form-control',
                    'value'        => '', //Shoudn't be a value here ! "" is overwritting "Foo"
                    'name'         => 'owner',
                    'id'           => 'owner',
                    'type'         => 'text',
                    'label'        => 'Project Owner',
                    'icon'         => 'fa-user',
                    'placeholder'  => 'Project Owner',
                    'default'      => 'Foo',
                ],
            ],
            // TEXTAREA - With Null value
            [
                'description',
                Element\Textarea::class,
                null,
                [
                    'autocomplete' => 'off',
                    'class'        => 'form-control',
                    'value'        => '',
                    'name'         => 'description',
                    'rows'         => 5,
                    'id'           => 'field_description',
                    'type'         => 'textarea',
                    'label'        => 'Project Description',
                    'icon'         => 'fa-pencil',
                    'placeholder'  => 'Project Description',
                ],
            ],
            // TEXTAREA - With string value
            [
                'description',
                Element\Textarea::class,
                'Lorem ipsum dolor sit amet',
                [
                    'autocomplete' => 'off',
                    'class'        => 'form-control',
                    'value'        => 'Lorem ipsum dolor sit amet',
                    'name'         => 'description',
                    'rows'         => 5,
                    'id'           => 'field_description',
                    'type'         => 'textarea',
                    'label'        => 'Project Description',
                    'icon'         => 'fa-pencil',
                    'placeholder'  => 'Project Description',
                ],
            ],
            // ALERT - With null value
            [
                'alert',
                Element\Alert::class,
                null,
                [
                    'class'        => 'alert-success',
                    'icon'         => 'fa-check',
                    'value'        => "You're awesome!",
                    'name'         => 'alert',
                    'type'         => 'alert',
                    'default'      => "You're awesome!",
                ],
            ],
            // ALERT - With string value
            [
                'alert',
                Element\Alert::class,
                'The Bar project',
                [
                    'class'        => 'alert-success',
                    'icon'         => 'fa-check',
                    'value'        => 'The Bar project',
                    'name'         => 'alert',
                    'type'         => 'alert',
                    'default'      => "You're awesome!",
                ],
            ],
            // CHECKBOX - With null value
            [
                'active',
                Element\Checkbox::class,
                null,
                [
                    'class'        => 'js-icheck',
                    'name'         => 'active',
                    'id'           => 'field_active',
                    'binary'       => '1',
                    'type'         => 'checkbox',
                    'label'        => 'Active',
                    // No value for binary
                ],
            ],
            // CHECKBOX - With true value
            [
                'active',
                Element\Checkbox::class,
                true,
                [
                    'class'        => 'js-icheck',
                    'name'         => 'active',
                    'id'           => 'field_active',
                    'binary'       => '1',
                    'type'         => 'checkbox',
                    'label'        => 'Active',
                    'checked'      => 'checked',
                    // No value for binary
                ],
            ],
            // CHECKBOX - non-binary - With null value
            [
                'pretty',
                Element\Checkbox::class,
                null,
                [
                    'class'        => 'js-icheck',
                    'name'         => 'pretty',
                    'id'           => 'field_pretty',
                    'binary'       => false,
                    'type'         => 'checkbox',
                    'label'        => 'Pretty',
                    'value'        => '',
                ],
            ],
            // CHECKBOX - non-binary - With true value
            [
                'pretty',
                Element\Checkbox::class,
                true,
                [
                    'class'        => 'js-icheck',
                    'name'         => 'pretty',
                    'id'           => 'field_pretty',
                    'binary'       => false,
                    'type'         => 'checkbox',
                    'label'        => 'Pretty',
                    'value'        => '1',
                ],
            ],
            // CHECKBOX - non-binary - With default value
            [
                'open',
                Element\Checkbox::class,
                null,
                [
                    'class'        => 'js-icheck',
                    'name'         => 'open',
                    'id'           => 'field_open',
                    'binary'       => false,
                    'type'         => 'checkbox',
                    'label'        => 'Open ?',
                    'default'      => 'yes',
                    'value'        => 'yes',
                ],
            ],

            // HIDDEN - With null value
            [
                'hidden',
                Element\Hidden::class,
                null,
                [
                    'value'        => 'Something',
                    'name'         => 'hidden',
                    'id'           => 'field_hidden',
                    'type'         => 'hidden',
                    'default'      => 'Something',
                ],
            ],
            // HIDDEN - With string value
            [
                'hidden',
                Element\Hidden::class,
                'Foo',
                [
                    'value'        => 'Foo',
                    'name'         => 'hidden',
                    'id'           => 'field_hidden',
                    'type'         => 'hidden',
                    'default'      => 'Something',
                ],
            ],

            // HIDDEN - With null value (No default)
            [
                'secret',
                Element\Hidden::class,
                null,
                [
                    'value'        => '',
                    'name'         => 'secret',
                    'id'           => 'field_secret',
                    'type'         => 'hidden',
                ],
            ],
            // HIDDEN - With string value (No default)
            [
                'secret',
                Element\Hidden::class,
                'Foo',
                [
                    'value'        => 'Foo',
                    'name'         => 'secret',
                    'id'           => 'field_secret',
                    'type'         => 'hidden',
                ],
            ],

            // SELECT - With null value
            [
                'status',
                Element\Select::class,
                null,
                [
                    'class'        => 'form-control js-select2',
                    'value'        => '',
                    'name'         => 'status',
                    'id'           => 'field_status',
                    'type'         => 'select',
                    'label'        => 'Project Status',
                    'options'      => [
                        '0' => 'Closed',
                        '1' => 'Open',
                    ],
                ],
            ],
            // SELECT - With string value
            [
                'status',
                Element\Select::class,
                '1',
                [
                    'class'        => 'form-control js-select2',
                    'value'        => '1',
                    'name'         => 'status',
                    'id'           => 'field_status',
                    'type'         => 'select',
                    'label'        => 'Project Status',
                    'options'      => [
                        '0' => 'Closed',
                        '1' => 'Open',
                    ],
                ],
            ],
            // SELECT - With null value & placeholder & default
            [
                'color',
                Element\Select::class,
                null,
                [
                    'class'        => 'form-control js-select2',
                    'value'        => 'blue',
                    'name'         => 'color',
                    'id'           => 'field_color',
                    'type'         => 'select',
                    'label'        => 'Color',
                    'default'      => 'blue',
                    'options'      => [
                        'red'   => 'Rouge',
                        'blue'  => 'Bleu',
                        'white' => 'Blanc',
                    ],
                    'data-placeholder'  => 'Select color',
                ],
            ],
            // SELECT - With string value & placeholder & default
            [
                'color',
                Element\Select::class,
                'white',
                [
                    'class'        => 'form-control js-select2',
                    'value'        => 'white',
                    'name'         => 'color',
                    'id'           => 'field_color',
                    'type'         => 'select',
                    'label'        => 'Color',
                    'default'      => 'blue',
                    'options'      => [
                        'red'   => 'Rouge',
                        'blue'  => 'Bleu',
                        'white' => 'Blanc',
                    ],
                    'data-placeholder'  => 'Select color',
                ],
            ],
        ];
    }
}
