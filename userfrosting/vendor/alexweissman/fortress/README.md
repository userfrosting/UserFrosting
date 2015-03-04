# Fortress

### By Alex Weissman

Copyright (c) 2015

A PHP library for sanitizing, validating, and canonicalizing HTTP request data against a JSON Schema.

## Dependencies

- PHP 5.4+
- [Valitron (server-side validation)](https://github.com/vlucas/valitron)
- [HTML Purifier](https://github.com/ezyang/htmlpurifier)

## Installation

### To install with composer:

1. If you haven't already, get [Composer](http://getcomposer.org/) and [install it](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) - preferably, globally.
2. Require Fortress, either by running `php composer.phar require alexweissman/fortress`, or by creating a `composer.json` file:

```
{
    "require": {
        "php": ">=5.4.0",
        "alexweissman/fortress": "dev-master"
    }
}
```

and running `composer install`.

3. Include the `vendor/autoload.php` file in your project.  For an example of how this can be done, see `fortress/config-fortress.php`.

## Example

```
require_once("fortress/config-fortress.php");

// Load the request schema
$requestSchema = new Fortress\RequestSchema("fortress/schema/forms/philosophers.json");

// Build a new HTTPRequestFortress, that expects a GET request
$rf = new Fortress\HTTPRequestFortress("get", $requestSchema, "index");
// Remove ajaxMode and csrf_token from the request data
$rf->removeFields(['ajaxMode', 'csrf_token']);

// Sanitize and validate data, halting on errors
$rf->sanitize();
$rf->validate();

// Do something with the filtered data
$data = $rf->data();
if (!yourFunctionHere($data)){
    $rf->raiseFatalError();
}

// If we've made it this far, success!
$rf->raiseSuccess();
```
