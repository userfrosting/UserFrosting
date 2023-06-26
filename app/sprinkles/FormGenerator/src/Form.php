<?php

/*
 * UserFrosting Form Generator
 *
 * @link      https://github.com/lcharette/UF_FormGenerator
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_FormGenerator/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\FormGenerator;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use UserFrosting\Fortress\RequestSchema\RequestSchemaRepository;
use UserFrosting\Sprinkle\FormGenerator\Element\Alert;
use UserFrosting\Sprinkle\FormGenerator\Element\Checkbox;
use UserFrosting\Sprinkle\FormGenerator\Element\Hidden;
use UserFrosting\Sprinkle\FormGenerator\Element\InputInterface;
use UserFrosting\Sprinkle\FormGenerator\Element\Select;
use UserFrosting\Sprinkle\FormGenerator\Element\Text;
use UserFrosting\Sprinkle\FormGenerator\Element\Textarea;
use UserFrosting\Sprinkle\FormGenerator\Exception\ClassNotFoundException;
use UserFrosting\Sprinkle\FormGenerator\Exception\InputNotFoundException;
use UserFrosting\Sprinkle\FormGenerator\Exception\InvalidClassException;

/**
 * Form Class.
 *
 * The FormGenerator class, which is used to process the `form` part from a Fortress
 * schema into an html form element for Twig.
 */
class Form
{
    /**
     * @var RequestSchemaRepository The form fields definition
     */
    protected $schema;

    /**
     * @var array<string,string|int> The form values
     */
    protected $data = [];

    /**
     * @var string Used to wrap form fields in top-level array
     */
    protected $formNamespace = '';

    /**
     * @var array<string,string> List of input type classes registered
     */
    protected $types = [];

    /**
     * @var string Default input type for element without one.
     */
    protected $defaultType = 'text';

    /**
     * Class constructor.
     *
     * @param RequestSchemaRepository                          $schema
     * @param array<string>|Collection<mixed>|Model|Repository $data   (default: [])
     */
    public function __construct(RequestSchemaRepository $schema, $data = [])
    {
        $this->setSchema($schema);
        $this->setData($data);

        // Register default types
        $this->registerDefaultType();
    }

    /**
     * Set the form current values.
     *
     * @param array<string>|Collection<mixed>|Model|Repository $data The form values
     *
     * @return self
     */
    public function setData($data)
    {
        if ($data instanceof Collection || $data instanceof Model) {
            $this->data = $data->toArray();
        } elseif ($data instanceof Repository) {
            $this->data = $data->all();
        } elseif (is_array($data)) {
            $this->data = $data;
        } else {
            throw new InvalidArgumentException('Data must be an array, a Collection, a Model or a Repository');
        }

        return $this;
    }

    /**
     * Set the schema for this validator.
     *
     * @param RequestSchemaRepository $schema A RequestSchemaRepository object, containing the form definition.
     *
     * @return self
     */
    public function setSchema(RequestSchemaRepository $schema)
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * Get all registered type classes.
     *
     * @return array<string,string>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Get the class for a specific name.
     *
     * @throws InputNotFoundException if type name not defined
     *
     * @return string The type name to return
     */
    public function getType(string $name): string
    {
        if (!isset($this->types[$name])) {
            throw new InputNotFoundException("Input type $name not found");
        }

        return $this->types[$name];
    }

    /**
     * Register a custom input type class.
     * Will overwrite any previously defined clas with the same name.
     *
     * @param string $name  The type name (eg. select, text, phone, mytext, etc.)
     * @param string $class The class uses
     */
    public function registerType(string $name, string $class): void
    {
        if (!class_exists($class)) {
            throw new ClassNotFoundException("Class $class not found.");
        }

        $this->types[$name] = $class;
    }

    /**
     * Remove input type from registered list.
     *
     * @param string $name
     */
    public function removeType(string $name): void
    {
        unset($this->types[$name]);
    }

    /**
     * Define the value of a specific form input.
     *
     * @param string     $inputName The input name
     * @param string|int $value     Form value
     *
     * @return self
     */
    public function setValue(string $inputName, $value)
    {
        $this->data[$inputName] = $value;

        return $this;
    }

    /**
     * Function used to overwrite the input argument from a schema file.
     * Can also be used to overwrite an argument hardcoded in the Twig file.
     *
     * @param string $inputName The input name where the argument will be added
     * @param string $property  The argument name. Example "data-color"
     * @param mixed  $value     The value of the argument
     *
     * @return self
     */
    public function setInputArgument(string $inputName, string $property, $value)
    {
        if ($this->schema->has($inputName)) {
            // Get the element and force set the property
            $element = $this->schema->get($inputName);
            $element['form'][$property] = $value;

            // Push back the modifyed element in the schema
            $this->schema->set($inputName, $element);
        }

        return $this;
    }

    /**
     * Function used to set options of a select element. Shortcut for using
     * `setInputArgument` and `setValue`.
     *
     * @param string               $inputName The select name to add options to
     * @param array<string,string> $data      An array of `value => label` options
     * @param string               $selected  The selected key
     *
     * @return self
     */
    public function setOptions(string $inputName, array $data = [], ?string $selected = null)
    {
        // Set opdations
        $this->setInputArgument($inputName, 'options', $data);

        // Set the value
        if (!is_null($selected)) {
            $this->setValue($inputName, $selected);
        }

        return $this;
    }

    /**
     * Function to set the form namespace.
     * Use the form namespace to wrap the fields name in a top level array.
     * Useful when using multiple schemas at once or if the names are using dot syntaxt.
     * See : http://stackoverflow.com/a/20365198/445757.
     *
     * @param string $namespace
     *
     * @return self
     */
    public function setFormNamespace(string $namespace)
    {
        $this->formNamespace = $namespace;

        return $this;
    }

    /**
     * Get default input type for element without one.
     *
     * @return string
     */
    public function getDefaultType(): string
    {
        return $this->defaultType;
    }

    /**
     * Set default input type for element without one.
     *
     * @param string $defaultType Default type name (eg. select, text, phone, mytext, etc.)
     *
     * @return self
     */
    public function setDefaultType(string $defaultType)
    {
        $this->defaultType = $defaultType;

        return $this;
    }

    /**
     * Generate an array contining all nececerry value to generate a form
     * with Twig.
     *
     * @return array<string,string> The form fields data
     */
    public function generate(): array
    {
        $form = collect([]);

        // Loop all the the fields in the schema
        foreach ($this->schema->all() as $name => $input) {

            // Skip if it doesn't have a `form` definition
            if (!isset($input['form'])) {
                continue;
            }

            // Alias the schema input
            $element = $input['form'];

            // Get the value from the data
            $value = $this->getValueForName($name);

            // Add the namespace to the name if it's defined
            $name = $this->getNamespacedName($name);

            // If element doesn't have a type, inject default (text)
            if (!isset($element['type'])) {
                $element['type'] = $this->defaultType;
            }

            // Get the class FQN for the given type.
            // Fallback to default if not found
            try {
                $elementClass = $this->getType($element['type']);
            } catch (InputNotFoundException $e) {
                $elementClass = $this->getType($this->getDefaultType());
            }

            // Create the actual class
            $class = new $elementClass($name, $element, $value);

            // Make sure we have an instance of InputInterface.
            // An error will be thrown otherwise
            if (!$class instanceof InputInterface) {
                throw new InvalidClassException('Input class needs to implement InputInterface');
            }

            // Put parsed data from the InputInterface to `$form`
            // Collection put is used here, because of the namespace, which will put it at the right place
            $form->put($name, $class->parse());
        }

        return $form->toArray();
    }

    /**
     * Gets the defined value for the specified field name.
     *
     * @param string $name
     *
     * @return string|null
     */
    protected function getValueForName(string $name): ?string
    {
        return Arr::get($this->data, $name);
    }

    /**
     * Get the name of the field, wrapped by the global form namespace.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getNamespacedName(string $name): string
    {
        if ($this->formNamespace !== '') {
            return $this->formNamespace . '[' . $name . ']';
        }

        return $name;
    }

    /**
     * Register the default built-in types.
     */
    protected function registerDefaultType(): void
    {
        $this->registerType('alert', Alert::class);
        $this->registerType('checkbox', Checkbox::class);
        $this->registerType('hidden', Hidden::class);
        $this->registerType('select', Select::class);
        $this->registerType('text', Text::class);
        $this->registerType('textarea', Textarea::class);
    }
}
