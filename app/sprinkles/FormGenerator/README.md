# Form Generator Sprinkle for [UserFrosting 4](https://www.userfrosting.com)

[![Donate][kofi-badge]][kofi]
[![Latest Version][releases-badge]][releases]
[![UserFrosting Version][uf-version]][uf]
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Travis][travis-badge]][travis]
[![Codecov][codecov-badge]][codecov]
[![StyleCI][styleci-badge]][styleci]

[kofi]: https://ko-fi.com/A7052ICP
[kofi-badge]: https://img.shields.io/badge/Donate-Buy%20Me%20a%20Coffee-blue.svg
[releases]: https://github.com/lcharette/UF_FormGenerator/releases
[releases-badge]: https://img.shields.io/github/release/lcharette/UF_FormGenerator.svg
[uf-version]: https://img.shields.io/badge/UserFrosting->=%204.3-brightgreen.svg
[uf]: https://github.com/userfrosting/UserFrosting
[travis]: https://travis-ci.org/lcharette/UF_FormGenerator
[travis-badge]: https://travis-ci.org/lcharette/UF_FormGenerator.svg?branch=master
[codecov]: https://codecov.io/gh/lcharette/UF_FormGenerator
[codecov-badge]: https://codecov.io/gh/lcharette/UF_FormGenerator/branch/master/graph/badge.svg
[styleci]: https://styleci.io/repos/68563337
[styleci-badge]: https://styleci.io/repos/68563337/shield?branch=master&style=flat

This Sprinkle provides helper classes, Twig template and JavaScript plugins to generate HTML forms, modals and confirm modal bases on UserFrosting [validation schemas](https://learn.userfrosting.com/routes-and-controllers/client-input/validation).

# Help and Contributing

If you need help using this sprinkle or found any bug, feels free to open an issue or submit a pull request. You can also find me on the [UserFrosting Chat](https://chat.userfrosting.com/) most of the time for direct support.

# Versions and UserFrosting support

| UserFrosting Version | FormGenerator Version |
| :------------------: | :-------------------: |
|        4.4.x         |         4.x.x         |
|        4.3.x         |    [3.0.x] & 4.0.x    |
|        4.2.x         |        [3.0.x]        |
|        4.1.x         |        [2.0.x]        |
|        4.0.x         |      No Support       |

[3.0.x]: https://github.com/lcharette/UF_FormGenerator/tree/3.0#form-generator-sprinkle-for-userfrosting-4
[2.0.x]: https://github.com/lcharette/UF_FormGenerator/tree/2.2#form-generator-sprinkle-for-userfrosting-4

# Installation
Edit UserFrosting `app/sprinkles.json` file and add the following to the `require` list : `"lcharette/uf_formgenerator": "^4.0.0"`. Also add `FormGenerator` to the `base` list. For example:

```json
{
    "require": {
        "lcharette/uf_formgenerator": "^4.0.0"
    },
    "base": [
        "core",
        "account",
        "admin",
        "FormGenerator"
    ]
}
```

Run `composer update` then `php bakery bake` to install the sprinkle.

# Working example

See the [UF_FormGeneratorExample](https://github.com/lcharette/UF_FormGeneratorExample) repo for an example of the FormGenerator full code.

# Features and usage
Before starting with _FormGenerator_, you should read the main UserFrosting guide to familiarize yourself with _validation schemas_: (https://learn.userfrosting.com/routes-and-controllers/client-input/validation).

## Form generation
### Defining the fields in the schema
This sprinkle uses the `schemas` used by UserFrosting to validate form data to build form. To achieve this, a new `form` key is simply added to the fields found in a `schema` file.

For example, here's a simple `schema` used to validate a form used to create a `project`. The form will contain a `name`, `description` and `status` fields.

```json
{
    "name" : {
        "validators" : {
            "length" : {
                "min" : 1,
                "max" : 100
            },
            "required" : {
                "message" : "PROJECT.VALIDATE.REQUIRED_NAME"
            }
        }
    },
    "description" : {
        "validators" : {}
    },
    "status" : {
        "validators" : {
            "member_of" : {
                "values" : [
                    "0", "1"
                ]
            },
            "required" : {
                "message" : "PROJECT.VALIDATE.STATUS"
            }
        }
    }
}
```
> Note: FormGenerator works with json and YAML schemas.

At this point, with typical UserFrosting setup, you would be going into your controller and Twig files to manually create your HTML form. This can be easy if you have a two or three fields, but can be a pain with a dozen fields and more. This is where FormGenerator steps in with the use of a new `form` attribute. Let's add it to our `project` form :

```json
{
    "name" : {
        "validators" : {
            "length" : {
                "min" : 1,
                "max" : 100
            },
            "required" : {
                "message" : "VALIDATE.REQUIRED_NAME"
            }
        },
        "form" : {
            "type" : "text",
            "label" : "NAME",
            "icon" : "fa-flag",
            "placeholder" : "NAME"
        }
    },
    "description" : {
        "validators" : {},
        "form" : {
            "type" : "textarea",
            "label" : "DESCRIPTION",
            "icon" : "fa-pencil",
            "placeholder" : "DESCRIPTION",
            "rows" : 5
        }
    },
    "status" : {
        "validators" : {
            "member_of" : {
                "values" : [
                    "0", "1"
                ]
            },
            "required" : {
                "message" : "VALIDATE.STATUS"
            }
        },
        "form" : {
            "type" : "select",
            "label" : "STATUS",
            "options" : {
                "0" : "Active",
                "1" : "Disabled"
            }
        }
    }
}
```

Let's look closer at the `name` field :

```json
"form" : {
    "type" : "text",
    "label" : "PROJECT.NAME",
    "icon" : "fa-flag",
    "placeholder" : "PROJECT.NAME"
}
```

Here you can see that we define the `type`, `label`, `icon` and `placeholder` value for this `name` field. You can define any standard [form attributes](http://www.w3schools.com/html/html_form_attributes.asp), plus the `icon`, `label` and `default` attributes. `data-*` attributes can also be defined in your schema if you need them. For the `select` element, a special `options` attribute containing an array of `key : value` can be used to define the dropdown options. The select options (as any other attributes) can also be set in PHP (see further below).

And of course, the values of the `label` and `placeholder` attributes can be defined using _translation keys_.

Currently, FormGenerator supports the following form elements :
- text (and any input supported by the HTML5 standard : number, tel, password, etc.)
- textarea
- select
- checkbox
- hidden
- alert (Display a static alert box in the form)

### The controller part
Once your fields defined in the `schema` json or yaml file, you need to load that schema in your controller.

First thing to do is add FormGenerator's `Form` class to your "use" list :
`use UserFrosting\Sprinkle\FormGenerator\Form;`

Next, where you load the schema and setup the `validator`, you simply add the new Form creation:
```php
// Load validator rules
$schema = new RequestSchema("schema://project.json");
$validator = new JqueryValidationAdapter($schema, $this->ci->translator);

// Create the form
$form = new Form($schema, $project);
```

In this example, `$project` can contain the default (or current value) of the fields. A data collection fetched from the database with eloquent can also be passed directly. That second argument can also be omitted to create an empty form.

Last thing to do is send the fields to Twig. In the list of retuned variables to the template, add the `fields` variable:
```php
$this->ci->view->render($response, "pages/myPage.html.twig", [
    "fields" => $form->generate(),
    "validators" => $validator->rules('json', true)
]);

```

### The Twig template part

Now it's time to display the form in `myPage.html.twig` !

```html
<form name="MyForm" method="post" action="/Path/to/Controller/Handling/Form">
    {% include "forms/csrf.html.twig" %}
    <div id="form-alerts"></div>
    <div class="row">
        <div class="col-sm-8">
            {% include 'FormGenerator/FormGenerator.html.twig' %}
        </div>
    </div>
    <div class="row">
      <button type="submit" class="btn btn-block btn-lg btn-success">Submit</button>
    </div>
</form>
```

That's it! No need to list all the field manually. The ones defined in the `fields` variable will be displayed by `FormGenerator/FormGenerator.html.twig`. Note that this will only load the fields, not the form itself. The `<form>` tag and `submit` button needs to be added manually.

## Modal form
What if you want to show a form in a modal window? Well, FormGenerator makes it even easier! It's basically three steps:
1. Setup your form schema (as described above)
2. Setup the form in your controller
3. Call the modal from your template

### Setup the form in your controller
With your schema in hand, it's time to create a controller and route to load your modal. The controller code will be like any basic UserFrosting modal, plus the `$form` part above and one changes in the `render` part. For example :

```php
$this->ci->view->render($response, "FormGenerator/modal.html.twig", [
    "box_id"        => $get['box_id'],
    "box_title"     => "PROJECT.CREATE",
    "submit_button" => "CREATE",
    "form_action"   => '/project/create',
    "fields"        => $form->generate(),
    "validators"    => $validator->rules('json', true)
]);
```

As you can see, instead of rendering your own Twig template, you simply have to specify FormGenerator's modal template. This template requires the following variables:
1. `box_id`: This should always be `$get['box_id']`. This is used by the JavaScript code to actually display the modal.
2. `box_title`: The title of the modal.
3. `submit_button`: The label of the submit button. Optional. Default to `SUBMIT` (localized).
4. `form_action`: The route where the form will be sent
5. `fields`: The fields. Should always be `$form->generate()`
6. `validators`: Client side validators

### Call the modal from your template
So at this point you have a controller that displays the modal at a `/path/to/controller` route. Time to show that modal. Again, two steps:

First, define a link or a button that will call the modal when clicked. For example :
```html
<button class="btn btn-success js-displayForm" data-formUrl="/path/to/controller">Create</button>
```

The important part here is the `data-formUrl` attribute. This is the route that will load your form. `js-displayForm` is used here to bind the button to the action.

Second, load the FormGenerator JavaScript widget. Add this to your Twig file:
```
{% block scripts_page %}
    {{ assets.js('js/FormGenerator') | raw }}
{% endblock %}
```

By default, the `formGenerator` plugin will bind a **form modal** to every element with the `js-displayForm` class.

## Modal confirmation

One side features of FormGenerator is the ability to add a confirmation modal to your pages with simple HTML5 attributes. The process is similar to adding a modal form, without the need to create any controller or route.

Let's look at a delete button / confirmation for our `project` :
```html
<a href="#" class="btn btn-danger js-displayConfirm"
  data-confirm-title="Delete project ?"
  data-confirm-message="Are you sure you want to delete this project?"
  data-confirm-button="Yes, delete project"
  data-post-url="/project/delete"><i class="fa fa-trash-o"></i> Delete</a>
```
(Note that content of data attributes can be translation keys)

If not aready done, make sure the FormGenerator assets are included in your template.
```
{% block scripts_page %}
    {{ assets.js('js/FormGenerator') | raw }}
{% endblock %}
```

By default, the `formGenerator` plugin will bind a **confirmation modal** to every element with the `js-displayConfirm` class.

## Advance usage

### Defining attributes in PHP

#### setInputArgument

Form field input attributes can also be added or edited from PHP. This can be useful when dynamically defining a Select input options. To do this, simply use the `setInputArgument($inputName, $property, $data)` method. For example, to add a list to a `clients` select :

```php
// Get clients from the db model
$clients = Clients::all();

$form = new Form($schema);
$form->setInputArgument('clients', 'options', $clients);
```

#### setData

If you want to set the form values once the form instance is created, you can use the `setData($data)` method:

```php
$form = new Form($schema);
$form->setData($clients, $project);
```

#### setValue

Similar to the `setData` method, you can set a specific input value using the `setValue($inputName, $value)` method :

```php
$currentClient = ...

$form = new Form($schema, $project);
$form->setValue('clients', $currentClient);
```

#### setFormNamespace

When dealing with multiple form on the same page or a dynamic number of input (you can use the new `Loader` system in 4.1 to build dynamic schemas!), it can be useful to wrap form elements in an array using the `setFormNamespace($namespace)` method. This can also your the input names [to contains dot syntaxt](http://stackoverflow.com/a/20365198/445757).

For example, `$form->setFormNamespace("data");` will transform all the input names from `<input name="foo" [...] />` to `<input name="data[foo]" [...] />`.

#### registerType

If you want to overwrite or add a new element type,


First, you need to create the element itself. This class needs to extends the `UserFrosting\Sprinkle\FormGenerator\Element\Input` class. In there you can define the default attributes, and do other transformation. For example, to define a new `Date` element type :

```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Element;

use UserFrosting\Sprinkle\FormGenerator\Element\Input;

class Date extends Input
{
    protected function applyTransformations(): void
    {
        $this->element = array_merge([
            'class' => 'myDateElement',
            'value' => $this->getValue(),
            'name'  => $this->name,
            'id'    => 'field_' . $this->name,
            'date-foo' => //...
        ], $this->element);
    }
}
```

Next, you need to register your `Date` element type. If the `date` type is already registered, it will be overwritten by your custom class.

```php
$form = new Form($schema, $project);
$form->registerType('date', UserFrosting\Sprinkle\MySprinkle\Element\Date::class);
```

### Javascript Plugin

By default, the `formGenerator` plugin will bind a **form modal** to every element with the `js-displayForm` class and will bind a **confirmation modal** to every element with the `js-displayConfirm` class. You can

#### Options
The following options are available:

Just pass an object with those
 - `mainAlertElement` (jQuery element). The element on the main page where the main alerts will be displayed. Default to `$('#alerts-page')`.
 - `redirectAfterSuccess` (bool). If set to true, the page will reload when the form submission or confirmation is successful. Default to `true`.

Example:
```js
$(".project-edit-button").formGenerator({redirectAfterSuccess: false});
```

#### Events
You can listen for some events returned by FormGenerator. Those events can be used to apply some actions when the modal is displayed or the form is successfully sent. For example, this is can be used with `redirectAfterSuccess` on `false` to refresh the data on the page when the form is submitted successfully.

- `formSuccess.formGenerator`
- `displayForm.formGenerator`
- `displayConfirmation.formGenerator`
- `confirmSuccess.formGenerator`
- `error.formGenerator`

Example:
```js
$(".project-edit-button").on("formSuccess.formGenerator", function () {
    // Refresh data
});
```

# Running tests

FormGenerator comes with some unit tests. Before submitting a new Pull Request, you need to make sure all tests are a go. With the sprinkle added to your UserFrosting installation, simply execute the `php bakery test FormGenerator` command to run the tests.

# License

By [Louis Charette](https://bbqsoftwares.com). Copyright (c) 2020, free to use in personal and commercial software as per the MIT license.
