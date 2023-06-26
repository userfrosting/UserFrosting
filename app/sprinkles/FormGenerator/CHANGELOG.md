# Change Log

## [4.0.1]
- Fixed issue where the value of an element whose name is using dot notation woudn't be found in the corresponding multidimensional array.

## [4.0.0]
- Form nows register element class, instead of using hardcoded string.
- Form has new `registerType` method to register new custom elements. `getType(string $name)` and `getTypes()` can be used to get the class for a type (eg. 'text', 'select', etc.) abd a list of types. `removeType` will unregister a type.
- Form still fallback to `Text` type if type is not found, but this can now be changed with `getDefaultType` and `setDefaultType`.
- Form uses `RequestSchemaRepository` instead of `RequestSchemaInterface`.
- Stricter PHP7 type throughout.
- Renamed `BaseInput` to `Input`.
- `Input` has new `setValue`, `getElement`, `setElement`, `getName` & `setName` methods.
- Checkbox element now pass the value to non-binary checkbox.
- `FormGeneratorController` uses strict typing.
- Finished tests and fix tests namespace. 100% coverage!
- Added PHP-CS-Fixer, PHPStan, StyleCI configuration.
- Updated Travis setup.

## [3.0.0]
- Added `successCallback` option
- Support for UserFrosting 4.2

## [2.2.10]
- Added support for Repository

## [2.2.9]
- Fix issue when setting data that is a collection
- `formSuccess` and `confirmSuccess` events now include the request data as a second argument

## [2.2.8]
- Fix icon in textarea macro

## [2.2.7]
- Added `modal-large` template file.

## [2.2.6]
- Fix issue with `binary` checkbox tests.
- Fix Text input style when no icon is added

## [2.2.5]
- Added `binary` option for checkbox to disable UF binary checkbox system (bool; default true).

## [2.2.4]
- Add necessary HTML to disable submit and cancel button in modal form.

## [2.2.3]
- New `$form->setOptions` function to set options of a select element. Shortcut for using `setInputArgument` and `setValue`.

## [2.2.2]
- Fix issue with error alert no displaying on confirmation dialog

## [2.2.1]
- Initialize ufAlert if not already done
- Autofocus first form field when modal is displayed

## [2.2.0]
- Refactored the javascript plugin
- Added new events
- Added new `redirectAfterSuccess` boolean option

## [2.1.2]
- Fix warning with select macro

## [2.1.1]
- Fix issue with the select macro
- Renamed macro templates with the `*.html.twig` extension

## [2.1.0]
- Completely refactored how form fields are parsed, including how default value are defined. Each input type now defines it's own class for defining default values and transforming some input.
- Twig templates updated to reflect the new parser.
- Twig macros changed from `*.generate(name, value)` to `*.generate(input)`.
- **`Bool` type changed to `checkbox`**.
- Removed the `number` Twig template (Will use the text input one).
- Added unit tests.
- Support for any attributes in the schema. For example, if you need to add a data attribute to a field, your schema would be:
```
"myField" : {
    "form" : {
        "type" : "text",
        "label" : "My Field",
        "icon" : "fa-pencil",
        "data-something" : "blah"
    }
}
```

## [2.0.0]
- Updated for UserFrosting v4.1.x

The custom `RequestSchema` have been removed. Instead of building the form directly on the schema using `$schema->initForm()`, you now create a new Form using `$form = new Form($schema)` and go on from there. Handling complex schema can now be done using the new loader system from UF 4.1.

`$schema->generateForm();` has also been changed to `$form->generate();`.

## [1.0.1]
- Bug fixes

## 1.0.0
- Initial release

<!--
## [Unreleased]

### Added

### Changed

### Deprecated

### Removed

### Fixed

### Security
-->

[4.0.1]: https://github.com/lcharette/UF_FormGenerator/compare/4.0.0...4.0.1
[4.0.0]: https://github.com/lcharette/UF_FormGenerator/compare/3.0.0...4.0.0
[3.0.0]: https://github.com/lcharette/UF_FormGenerator/compare/2.2.10...3.0.0
[2.2.10]: https://github.com/lcharette/UF_FormGenerator/compare/2.2.9...2.2.10
[2.2.9]: https://github.com/lcharette/UF_FormGenerator/compare/2.2.8...2.2.9
[2.2.8]: https://github.com/lcharette/UF_FormGenerator/compare/2.2.7...2.2.8
[2.2.7]: https://github.com/lcharette/UF_FormGenerator/compare/2.2.6...2.2.7
[2.2.6]: https://github.com/lcharette/UF_FormGenerator/compare/2.2.5...2.2.6
[2.2.5]: https://github.com/lcharette/UF_FormGenerator/compare/2.2.4...2.2.5
[2.2.4]: https://github.com/lcharette/UF_FormGenerator/compare/2.2.3...2.2.4
[2.2.3]: https://github.com/lcharette/UF_FormGenerator/compare/2.2.2...2.2.3
[2.2.2]: https://github.com/lcharette/UF_FormGenerator/compare/2.2.1...2.2.2
[2.2.1]: https://github.com/lcharette/UF_FormGenerator/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/lcharette/UF_FormGenerator/compare/2.1.2...2.2.0
[2.1.2]: https://github.com/lcharette/UF_FormGenerator/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/lcharette/UF_FormGenerator/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/lcharette/UF_FormGenerator/compare/2.0.0...2.1.0
[2.0.0]: https://github.com/lcharette/UF_FormGenerator/compare/1.0.1...2.0.0
[1.0.1]: https://github.com/lcharette/UF_FormGenerator/compare/1.0.0...1.0.1
