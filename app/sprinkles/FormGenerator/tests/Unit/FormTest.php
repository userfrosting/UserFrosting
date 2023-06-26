<?php

/*
 * UserFrosting Form Generator
 *
 * @link      https://github.com/lcharette/UF_FormGenerator
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_FormGenerator/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\FormGenerator\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use UserFrosting\Fortress\RequestSchema\RequestSchemaRepository;
use UserFrosting\Sprinkle\FormGenerator\Element\Input;
use UserFrosting\Sprinkle\FormGenerator\Element\Select;
use UserFrosting\Sprinkle\FormGenerator\Exception\ClassNotFoundException;
use UserFrosting\Sprinkle\FormGenerator\Exception\InputNotFoundException;
use UserFrosting\Sprinkle\FormGenerator\Exception\InvalidClassException;
use UserFrosting\Sprinkle\FormGenerator\Form;
use UserFrosting\Support\Repository\Loader\YamlFileLoader;
use UserFrosting\Tests\TestCase;

/**
 * FormGeneratorTest
 * The FormGenerator unit tests.
 */
class FormTest extends TestCase
{
    /** @var string */
    public $basePath;

    public function setUp(): void
    {
        parent::setUp();
        $this->basePath = __DIR__ . '/data';
    }

    /**
     * Test the Form Class.
     *
     * @param string $file
     * @param mixed  $data
     * @param array  $expected
     * @dataProvider formProvider
     */
    public function testForm(string $file, $data, array $expected): void
    {
        // Get Schema
        $loader = new YamlFileLoader($this->basePath . $file);
        $schema = new RequestSchemaRepository($loader->load());

        // Generate the form
        $form = new Form($schema, $data);

        // Test to make sure the class creation is fine
        $this->assertInstanceof(Form::class, $form);

        // Test the form generation
        $generatedForm = $form->generate();
        $this->assertIsArray($generatedForm);

        // We test the generated result
        $this->assertSame($expected, $generatedForm);
    }

    /**
     * Data provider for tests
     */
    public function formProvider(): array
    {
        $stub = $this->createMock(Model::class);
        $stub->method('toArray')->willReturn(['name' => 'Bar project']);

        return [
            // WITH NO DATA
            [
                '/good.json',
                [],
                [
                    'name' => [
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
            ],
            // WITH DATA
            [
                '/good.json',
                [
                    'name' => 'Bar project',
                ],
                [
                    'name' => [
                        'autocomplete' => 'off',
                        'class'        => 'form-control',
                        'value'        => 'Bar project', //Value's here !
                        'name'         => 'name',
                        'id'           => 'field_name',
                        'type'         => 'text',
                        'label'        => 'Project Name',
                        'icon'         => 'fa-flag',
                        'placeholder'  => 'Project Name',
                    ],
                ],
            ],
            // WITH DATA AS COLLECTION
            [
                '/good.json',
                collect([
                    'name' => 'Bar project',
                ]),
                [
                    'name' => [
                        'autocomplete' => 'off',
                        'class'        => 'form-control',
                        'value'        => 'Bar project', //Value's here !
                        'name'         => 'name',
                        'id'           => 'field_name',
                        'type'         => 'text',
                        'label'        => 'Project Name',
                        'icon'         => 'fa-flag',
                        'placeholder'  => 'Project Name',
                    ],
                ],
            ],
            // WITH DATA AS MODEL
            [
                '/good.json',
                $stub,
                [
                    'name' => [
                        'autocomplete' => 'off',
                        'class'        => 'form-control',
                        'value'        => 'Bar project', //Value's here !
                        'name'         => 'name',
                        'id'           => 'field_name',
                        'type'         => 'text',
                        'label'        => 'Project Name',
                        'icon'         => 'fa-flag',
                        'placeholder'  => 'Project Name',
                    ],
                ],
            ],
            // WITH DATA AS REPOSITORY
            [
                '/good.json',
                new \Illuminate\Config\Repository([
                    'name' => 'Bar project',
                ]),
                [
                    'name' => [
                        'autocomplete' => 'off',
                        'class'        => 'form-control',
                        'value'        => 'Bar project', //Value's here !
                        'name'         => 'name',
                        'id'           => 'field_name',
                        'type'         => 'text',
                        'label'        => 'Project Name',
                        'icon'         => 'fa-flag',
                        'placeholder'  => 'Project Name',
                    ],
                ],
            ],
            // With Nested data
            [
                '/nested.json',
                new \Illuminate\Config\Repository([
                    'site' => [
                        'name' => 'Bar project',
                    ],
                ]),
                [
                    'site.name' => [
                        'autocomplete' => 'off',
                        'class'        => 'form-control',
                        'value'        => 'Bar project', //Value's here !
                        'name'         => 'site.name',
                        'id'           => 'field_site.name',
                        'type'         => 'text',
                        'label'        => 'Project Name',
                        'icon'         => 'fa-flag',
                        'placeholder'  => 'Project Name',
                    ],
                ],
            ],
            // WITH BAD DATA
            [
                '/bad.json',
                [],
                [
                    'myField' => [
                        'autocomplete' => 'off',
                        'class'        => 'form-control',
                        'value'        => '',
                        'name'         => 'myField',
                        'id'           => 'field_myField',
                        'type'         => 'foo', // Will still be foo, but the whole element is parsed using the text parser
                    ],
                    'myOtherField' => [
                        'autocomplete' => 'off',
                        'class'        => 'form-control',
                        'value'        => 'Bar',
                        'name'         => 'myOtherField',
                        'id'           => 'field_myOtherField',
                        'type'         => 'text', // Will be added by the FORM class
                    ],
                ],
            ],
        ];
    }

    /**
     * Test the Form Class with an custom element input
     * @depends testForm
     */
    public function testFormWithCustomType(): void
    {
        // Get Schema & form
        $loader = new YamlFileLoader($this->basePath . '/good.json');
        $schema = new RequestSchemaRepository($loader->load());
        $form = new Form($schema);

        // Get elements before registered
        $elementsBefore = $form->getTypes();

        // Register our custom element
        $form->registerType('text', CustomInputStub::class);

        // Make sure it registered properly
        $this->assertSame(CustomInputStub::class, $form->getType('text'));

        // Get element after and inspect the changes
        $elementsAfter = $form->getTypes();
        $intersection = array_diff_assoc($elementsAfter, $elementsBefore);
        $this->assertSame(['text' => CustomInputStub::class], $intersection);

        // Test the form generation
        $generatedForm = $form->generate();
        $this->assertSame([
            'name' => [
                'autocomplete' => 'off',
                'class'        => 'form-control',
                'value'        => '',
                'name'         => 'name',
                'id'           => 'field_name',
                'foo'          => 'bar', // Added by our custom input
                'type'         => 'text',
                'label'        => 'Project Name',
                'icon'         => 'fa-flag',
                'placeholder'  => 'Project Name',
            ],
        ], $generatedForm);
    }

    /**
     * @depends testFormWithCustomType
     */
    public function testFormForInvalidClassException(): void
    {
        // Get Schema & form
        $loader = new YamlFileLoader($this->basePath . '/good.json');
        $schema = new RequestSchemaRepository($loader->load());
        $form = new Form($schema);

        $form->registerType('text', FakeElementStub::class);

        // Set expectations and act
        $this->expectException(InvalidClassException::class);
        $form->generate();
    }

    /**
     * @depends testFormWithCustomType
     */
    public function testFormForClassNotFoundException(): void
    {
        // Get Schema & form
        $loader = new YamlFileLoader($this->basePath . '/good.json');
        $schema = new RequestSchemaRepository($loader->load());
        $form = new Form($schema);

        // Set expectations and act
        $this->expectException(ClassNotFoundException::class);
        $form->registerType('text', '/Foo');
    }

    /**
     * @depends testFormWithCustomType
     */
    public function testFormRemoveType(): void
    {
        // Get Schema & form
        $loader = new YamlFileLoader($this->basePath . '/good.json');
        $schema = new RequestSchemaRepository($loader->load());
        $form = new Form($schema);

        // Get elements before registered
        $elementsBefore = $form->getTypes();

        // Remove select element
        $form->removeType('select');

        // Get element after and inspect the changes
        $elementsAfter = $form->getTypes();
        $intersection = array_diff_assoc($elementsBefore, $elementsAfter);
        $this->assertSame(['select' => Select::class], $intersection);

        // Make sure it unregistered properly
        $this->expectException(InputNotFoundException::class);
        $form->getType('select');
    }

    /**
     * @depends testForm
     */
    public function testFormWithNamespace(): void
    {
        // Get Schema & form
        $loader = new YamlFileLoader($this->basePath . '/good.json');
        $schema = new RequestSchemaRepository($loader->load());
        $form = new Form($schema);

        // Set form namespace
        $form->setFormNamespace('foo');

        // Test the form generation
        $this->assertSame([
            'foo[name]' => [
                'autocomplete' => 'off',
                'class'        => 'form-control',
                'value'        => '',
                'name'         => 'foo[name]',
                'id'           => 'field_foo[name]',
                'type'         => 'text',
                'label'        => 'Project Name',
                'icon'         => 'fa-flag',
                'placeholder'  => 'Project Name',
            ],
        ], $form->generate());
    }

    /**
     * @depends testForm
     */
    public function testFormForSetValue(): void
    {
        // Get Schema & form
        $loader = new YamlFileLoader($this->basePath . '/good.json');
        $schema = new RequestSchemaRepository($loader->load());
        $form = new Form($schema, [
            'name' => 'The Foo', // Will be overwritten by setValue
        ]);

        // Set form namespace
        $form->setValue('name', 'The Bar');

        // Test the form generation
        $this->assertSame([
            'name' => [
                'autocomplete' => 'off',
                'class'        => 'form-control',
                'value'        => 'The Bar',
                'name'         => 'name',
                'id'           => 'field_name',
                'type'         => 'text',
                'label'        => 'Project Name',
                'icon'         => 'fa-flag',
                'placeholder'  => 'Project Name',
            ],
        ], $form->generate());
    }

    /**
     * @depends testForm
     */
    public function testFormForSetInputArgument(): void
    {
        // Get Schema & form
        $loader = new YamlFileLoader($this->basePath . '/good.json');
        $schema = new RequestSchemaRepository($loader->load());
        $form = new Form($schema);

        // Set form namespace
        $form->setInputArgument('name', 'data-foo', 'Bar')
             ->setInputArgument('name', 'bar', 123);

        // Test the form generation
        $this->assertSame([
            'name' => [
                'autocomplete' => 'off',
                'class'        => 'form-control',
                'value'        => '',
                'name'         => 'name',
                'id'           => 'field_name',
                'type'         => 'text',
                'label'        => 'Project Name',
                'icon'         => 'fa-flag',
                'placeholder'  => 'Project Name',
                'data-foo'     => 'Bar',
                'bar'          => 123,
            ],
        ], $form->generate());
    }

    /**
     * @depends testForm
     */
    public function testFormWithSelect(): void
    {
        // Get Schema & form
        $loader = new YamlFileLoader($this->basePath . '/select.json');
        $schema = new RequestSchemaRepository($loader->load());
        $form = new Form($schema);

        // Test the form generation
        $this->assertSame([
            'status' => [
                'class'        => 'form-control js-select2',
                'value'        => '',
                'name'         => 'status',
                'id'           => 'field_status',
                'type'         => 'select',
                'label'        => 'Status',
                'options'      => [
                    '0' => 'Closed',
                    '1' => 'Open',
                ],
            ],
            'color' => [
                'class'             => 'form-control js-select2',
                'value'             => '',
                'name'              => 'color',
                'id'                => 'field_color',
                'type'              => 'select',
                'label'             => 'Color',
                'data-placeholder'  => 'Select color',
            ],
        ], $form->generate());
    }

    /**
     * @depends testFormWithSelect
     * @depends testFormForSetValue
     */
    public function testFormForSetOptions(): void
    {
        // Get Schema & form
        $loader = new YamlFileLoader($this->basePath . '/select.json');
        $schema = new RequestSchemaRepository($loader->load());
        $form = new Form($schema);

        // Make sure status won't change value
        $form->setValue('status', '1');

        // Set form namespace
        $form->setOptions('status', [
            'closed' => 'Fermé',
            'open'   => 'Ouvert',
        ])->setOptions('color', [
            'red'   => 'Rouge',
            'blue'  => 'Bleu',
            'black' => 'Noir',
            'white' => 'Blanc',
        ], 'black');

        // Test the form generation
        $this->assertSame([
            'status' => [
                'class'        => 'form-control js-select2',
                'value'        => '1',
                'name'         => 'status',
                'id'           => 'field_status',
                'type'         => 'select',
                'label'        => 'Status',
                'options'      => [
                    'closed' => 'Fermé',
                    'open'   => 'Ouvert',
                ],
            ],
            'color' => [
                'class'        => 'form-control js-select2',
                'value'        => 'black',
                'name'         => 'color',
                'id'           => 'field_color',
                'type'         => 'select',
                'label'        => 'Color',
                'options'      => [
                    'red'   => 'Rouge',
                    'blue'  => 'Bleu',
                    'black' => 'Noir',
                    'white' => 'Blanc',
                ],
                'data-placeholder'  => 'Select color',
            ],
        ], $form->generate());
    }

    /**
     * @depends testForm
     */
    public function testFormWithNewDefaultType(): void
    {
        // Get Schema & form
        $loader = new YamlFileLoader($this->basePath . '/bad.json');
        $schema = new RequestSchemaRepository($loader->load());
        $form = new Form($schema);

        // Set form namespace
        $form->setDefaultType('select');
        $this->assertSame('select', $form->getDefaultType());

        // Test the form generation
        $this->assertSame([
            'class'        => 'form-control js-select2',
            'value'        => 'Bar',
            'name'         => 'myOtherField',
            'id'           => 'field_myOtherField',
            'type'         => 'select', // Added by default type
        ], $form->generate()['myOtherField']);
    }

    /**
     * @depends testForm
     */
    public function testFormForInvalidArgumentException(): void
    {
        // Get Schema & form
        $loader = new YamlFileLoader($this->basePath . '/good.json');
        $schema = new RequestSchemaRepository($loader->load());
        $form = new Form($schema);

        // Set expectations
        $this->expectException(InvalidArgumentException::class);
        $form->setData('foo');
    }
}

class FakeElementStub
{
}

class CustomInputStub extends Input
{
    /**
     * {@inheritdoc}
     */
    protected function applyTransformations(): void
    {
        $this->element = array_merge([
            'autocomplete' => 'off',
            'class'        => 'form-control',
            'value'        => $this->getValue(),
            'name'         => $this->name,
            'id'           => 'field_' . $this->name,
            'foo'          => 'bar',
        ], $this->element);
    }
}
