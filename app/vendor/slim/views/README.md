# Slim Views

This repository contains custom View classes for the template frameworks listed below. 
You can use any of these custom View classes by either requiring the appropriate class in your 
Slim Framework bootstrap file and initialize your Slim application using an instance of 
the selected View class or using Composer (the recommended way).

Slim Views only officially support the following views listed below.

- Smarty
- Twig

## How to Install

#### using [Composer](http://getcomposer.org/)

Install in your project by running the following composer command:

```bash
$ php composer require slim/views
```

## Smarty

### How to use
    
```php
<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Smarty()
));
```

To use Smarty options do the following:
    
```php
$view = $app->view();
$view->parserDirectory = dirname(__FILE__) . 'smarty';
$view->parserCompileDirectory = dirname(__FILE__) . '/compiled';
$view->parserCacheDirectory = dirname(__FILE__) . '/cache';
```

## Twig

### How to use
    
```php
<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig()
));
```

To use Twig options do the following:
    
```php
$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
    'cache' => dirname(__FILE__) . '/cache'
);
```

In addition to all of this we also have a few helper functions which are included for both view parsers.
In order to start using these you can add them to their respective view parser as stated below:

#### Twig

```php
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);
```

#### Smarty

```php
$view->parserExtensions = array(
    dirname(__FILE__) . '/vendor/slim/views/Slim/Views/SmartyPlugins',
);
```

These helpers are listed below.

- urlFor
- siteUrl
- baseUrl
- currentUrl

#### urlFor

__Twig__

Inside your Twig template you would write:

    {{ urlFor('hello', {"name": "Josh", "age": "19"}) }}

You can easily pass variables that are objects or arrays by doing:

    <a href="{{ urlFor('hello', {"name": person.name, "age": person.age}) }}">Hello {{ name }}</a>

If you need to specify the appname for the getInstance method in the urlFor functions, set it as the third parameter of the function
in your template:

    <a href="{{ urlFor('hello', {"name": person.name, "age": person.age}, 'admin') }}">Hello {{ name }}</a>

__Smarty__

Inside your Smarty template you would write:

    {urlFor name="hello" options="name.Josh|age.26"}

or with the new array syntax:

    {urlFor name="hello" options=["name" => "Josh", "age" => "26"]}

You can easily pass variables that are arrays as normal or using the (.):

    <a href="{urlFor name="hello" options="name.{$person.name}|age.{$person.age}"}">Hello {$name}</a>

If you need to specify the appname for the getInstance method in the urlFor functions, set the appname parameter in your function:

    <a href="{urlFor name="hello" appname="admin" options="name.{$person.name}|age.{$person.age}"}">Hello {$name}</a>

#### siteUrl

__Twig__

Inside your Twig template you would write:

    {{ siteUrl('/about/me') }}

__Smarty__

Inside your Smarty template you would write:

    {siteUrl url='/about/me'}


#### baseUrl

__Twig__

Inside your Twig template you would write:

    {{ baseUrl() }}

__Smarty__

Inside your Smarty template you would write:

    {baseUrl}


#### currentUrl

__Twig__

Inside your Twig template you would write:

    {{ currentUrl() }}

__Smarty__

Inside your Smarty template you would write:

    {currentUrl}

## Authors

[Josh Lockhart](https://github.com/codeguy)

[Andrew Smith](https://github.com/silentworks)

## License

MIT Public License