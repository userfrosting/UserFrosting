# Style guide for contributing to UserFrosting

## PHP

All PHP contributions must adhere to [PSR-1](http://www.php-fig.org/psr/psr-1/) and [PSR-2](http://www.php-fig.org/psr/psr-2/) specifications.

In addition:

### Documentation

- All documentation blocks must adhere to the [PHPDoc](https://phpdoc.org/) format and syntax.
- All PHP files MUST contain the following documentation block immediately after the opening `<?php` tag:

```
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */
 ```

### Classes

- All classes MUST be prefaced with a documentation block containing a description and the author(s) of that class.  You SHOULD add other descriptive properties as well.
- All class members and methods MUST be prefaced with a documentation block.  Any parameters and return values MUST be documented.
- The contents of a class should be organized in the following order: constants, member variables, constructor, other magic methods, public methods, protected methods, private methods, and finally, deprecated methods (of any type or visibility).
- Setter methods SHOULD return the parent object.

### Routes

- Front controller (Slim) routes should be alphabetized, first by route type and then by route URL.  If you have route groups, those should come first and be alphabetized as well.

### Variables

 - All class member variables and local variables MUST be declared in `camelCase`.

### Arrays

 - Array keys MUST be defined using `snake_case`.  This is so they can be referenced in Twig and other templating languages.
 - Array keys MUST NOT contain `.`.  This is because `.` is a reserved operator in Laravel and Twig's [dot syntax](https://medium.com/@assertchris/dot-notation-3fd3e42edc61).
 - Multidimensional arrays SHOULD be referenced using dot syntax whenever possible.  So, instead of doing `$myArray['person1']['email']`, you should use `$myArray['person1.email']` if your array structure supports it.

### Tools

[php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) can be used to automatically fix styling. See [Contributing](.github/CONTRIBUTING.md) for more info.

### Twig Templates

[TODO]