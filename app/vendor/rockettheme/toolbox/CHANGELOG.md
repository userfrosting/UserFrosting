# v1.3.1
## 04/25/2016

1. [](#new)
    * Add new function File::rename()
    * Add new function UniformResourceLocator::fillCache()
3. [](#bugfix)
    * Fix collections support in BluprintSchema::extra()
    * Fix exception in stream wrapper when scheme is not defined in locator 
    * Prevent UniformResourceLocator from resolving paths outside of defined scheme paths (#8)
    * Fix breaking YAML files which start with three dashes (#5) 

# v1.3.0
## 03/07/2016

1. [](#new)
    * Add new function UniformResourceLocator::isStream()
    * Add new class BlueprintForm
    * Renamed Blueprints class into BlueprintSchema
    * Add new function BlueprintSchema::extra() to return data fields which haven't been defined in blueprints
    * Add support to unset and replace blueprint fields and properties
    * Allow arbitrary dynamic fields in Blueprints (property@)
    * Add default properties support for form field types
    * Remove dependency on ircmaxell/password-compat
    * Add support for Symfony 3
    * Add a few unit tests
2. [](#improved)
    * UniformResourceLocator::addPath(): Add option to add path after existing one (falls back to be last if path is not found)
3. [](#bugfix)
    * Fix blueprint without a form
    * Fix merging data with empty blueprint

# v1.2.0
## 10/24/2015

1. [](#new)
    * **Backwards compatibility break**: Blueprints class needs to be initialized with `init()` if blueprints contain `@data-*` fields 
    * Renamed NestedArrayAccess::remove() into NestedArrayAccess::undef() to avoid name clashes

# v1.1.4
## 10/15/2015

1. [](#new)
    * Add support for native YAML parsing option to Markdown and YAML file classes

# v1.1.3
## 09/14/2015

3. [](#bugfix)
    * Fix regression: Default values for collections were broken
    * Fix Argument 1 passed to `RocketTheme\Toolbox\Blueprints\Blueprints::mergeArrays()` must be of the type array
    * Add exception on Blueprint collection merging; only overridden value should be used
    * File locking truncates contents of the file
    * Stop duplicate Messages getting added to the queue

# v1.1.2
## 08/27/2015

1. [](#new)
    * Creation of Changelog
