# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Unreleased

### Security
- Added placeholder URL for `site.uri.public` in configuration to guard against Host Header Injection attacks by default in production.

## [v4.6.2]

### Changes
- Updated Portuguese translation ([#1178](https://github.com/userfrosting/UserFrosting/pull/1178)).

### Fix
- Fix `UF_MODE` not being loaded by Config ([#1177](https://github.com/userfrosting/UserFrosting/issues/1177)).

## [v4.6.1]

### Fix
- Fix error when building assets. Updated `@yarnpkg/shell` from `^2.4.1` to `^3.0.0` ([#1168](https://github.com/userfrosting/UserFrosting/issues/1168)).
- Set `psr/log` in `composer.json` to avoid conflict with dependencies.

## [v4.6.0]

### Changed Requirements
- Drop PHP 7.2 support. Gain PHP 8.0 support. PHP 8.0 is now recommended.

### Dependencies upgrade
- Replaced individual UserFrosting Assets, Cache, Config, Fortress, i18n, Session, Support and UniformResouceLocator repos with monolitic `userfrosting/framework` repo.
- Upgrade all Laravel packages to ^8.x from ^5.8.
- Upgrade `vlucas/phpdotenv`to ^5.3 from ^3.4.
- Upgrade `symfony/console` to ^5.1 from ^4.3.
- Upgrade `phpunit/phpunit` to ^9.5

### New Feature
- Added support for built-in PHP Server.

### Changes
- Per user theme (`$user->theme`) is now deprecated and disabled by default. To enable back, change `per_user_theme` config to `true` ([#1131](https://github.com/userfrosting/UserFrosting/issues/1131)). This feature will be removed in future version.
- Bakery command `execute` method now requires to return an int (Symfony 4.4 upgrade : https://symfony.com/blog/new-in-symfony-4-4-console-improvements).
- `UserFrosting\Sprinkle\Core\Database\EloquentBuilder` now uses `Illuminate\Database\Eloquent\Concerns\QueriesRelationships` Trait instead of manually implementing `withSum`, `withAvg`, `withMin`, `withMax` & `withAggregate`. See Laravel documentation for usage change.
- Migrate `uf-modal.js` to jQuery Boilerplate ([#740](https://github.com/userfrosting/UserFrosting/issues/740))

## [v4.5.1]

### Fixed
- Fix `php bakery route:list` error on procedural routes ([#1162](https://github.com/userfrosting/UserFrosting/issues/1162)).
- Fix `NO_DATA` alert when editing a User Role ([#1163](https://github.com/userfrosting/UserFrosting/issues/1163)).
- [Vagrant/Homestead] Force use of PHP 7.4 for CLI (since default is now PHP 8).
- Fix integration with [filp/whoops 2.14](https://github.com/filp/whoops/compare/2.13.0...2.14.0)

### Changed
- Updated Docker development images (PHP 7.2 to 7.4, NodeJS 12.x to 14.x) ([#1085]).

## [v4.5.0]

### Changed Requirements
- Drop PHP 7.1 support. PHP 7.4 is now recommended.
- Raised NodeJS version requirement from `>=10.12.0` to `^12.17.0 || >=14.0.0` ([#1138]).
- Raised NPM version requirement from `>=6.0.0` to `>=6.14.4` ([#1138]).

### Changed Composer Dependencies
- Updated `wikimedia/composer-merge-plugin` from `^1.4.0` to `^2.1.0` ([#1117]).

### Added
- Composer 2 support ([#1117]).
- [Lando](https://lando.dev) support.
- Added more SMTP options in env and setup:smtp bakery command ([#1077]),
- Added new `MAIL_MAILER` environment variable to set mailer type.
- Added "Native mail" to `setup:mail` bakery command.

### Changed
- Implement findInt ([#1117]).
- Replace `getenv()` with `env()` ([#1121]). 
- Replaced `UserFrosting\Sprinkle\Core\Bakery\Helper\NodeVersionCheck` with new `UserFrosting\Sprinkle\Core\Util\VersionValidator` class.
- Bakery command `setup:smtp` renamed to `setup:mail`. The old command is still available as an alias for backward compatibility. 
- Changed `.php_cs` to `.php_cs.dist`.
- Changed `phpunit.xml` to `phpunit.xml.dist`.

### Fixed
- Replaced AdminLTE credit in default footer (old link was dead).
- Issue with path slashes on Windows ([#1133]).

### Removed
- Removed deprecated `UserFrosting\System\Bakery\Migration` (deprecated in 4.2.0).
- Removed deprecated `UserFrosting\Tests\DatabaseTransactions` (deprecated in 4.2.0).
- Removed deprecated `UserFrosting\Sprinkle\Core\Tests\ControllerTestCase` (deprecated in 4.2.2).
- Removed deprecated `UserFrosting\Sprinkle\Core\Model\UFModel` (deprecated in 4.1).
- Removed deprecated `UserFrosting\Sprinkle\Core\Sprunje\Sprunje::getResults` (deprecated in 4.1.7).
- Removed deprecated `UserFrosting\Sprinkle\Account\Database\Models\User::exists` (deprecated in 4.1.7).
- Removed deprecated `UserFrosting\Sprinkle\Core\Database\Models\Model::export` (deprecated in 4.1.8).
- Removed deprecated `UserFrosting\Sprinkle\Core\Database\Models\Model::queryBuilder` (deprecated in 4.1.8).
- Removed deprecated `UserFrosting\Sprinkle\Core\Database\Relations\Concerns\Unique::withLimit` (deprecated in 4.1.7).
- Removed deprecated `UserFrosting\Sprinkle\Core\Database\Relations\Concerns\Unique::withOffset` (deprecated in 4.1.7).
- Removed deprecated `UserFrosting\Sprinkle\Core\Error\RendererWhoopsRenderer::getResourcesPath`.
- Removed deprecated `UserFrosting\Sprinkle\Core\Error\RendererWhoopsRenderer::setResourcesPath`.
- Removed deprecated Handlebar `ifCond` (Deprecated in 4.1).
- Removed migration seed.
- Removed support for migration with non static `$dependencies` properties.
- Removed support for deprecated `determineRedirectOnLogin` service (deprecated in 4.1.10).

## [v4.4.5]

### Changed
- Replaced Travis with Github Actions
- Force Composer 1.0 for Docker ([#1126])
- Update error.html.twig - add container ([#1128])
- Update some tests
- Update Vagrant doc & config

## [v4.4.4]

### Fixed
- Replaced AdminLTE credit in default footer (old link was dead).
- Lock Travis to Composer V1 to avoid error until Composer V2 support can be fixed.
- Remove whitespace at top of page templates ([#1107])
- Deep extend when adding global query params in ufTable ([#1114])
- Check for null key in Unique::buildTertiaryDictionary ([#1109])
- Client-side assets containing glob characters causing crashes when building assets.
- Fixed issue where merging of package dependencies would show as "undefined", resulting in debugging challenges where there are issues.

## [v4.4.3]

### Fixed
- Changed some private methods to protected in tests for easier extension.
- Tentative fix for duplication of user_name in user Factories.
- Factories now uses `::class` reference instead of string.
- Fix `ExceptionHandlerTest` test to speed things up.

## [v4.4.2]

### Fixed
- Locale matcher can fail when client provided locale identifier has incorrect casing ([#1087])
- Sprunje applyTransformations method not returning the $collections object ([#1068])
- Old assets in `app/assets/browser_modules` not being deleted during install ([#1092])
- Added `SKIP_PERMISSION_CHECK` env to skip check for local directories that needs to be write protected. This can be used for local production env testing.

## [v4.4.1]

### Fixed
- Fixed issue where incompatible NPM packages would be browserified, resulting in install failures.
- Replaced deprecated Twig class.
- Fixed issue when compiling assets for production ([#1078]).
- Migration dependencies should work with and without leading `\` ([#1023])
- Throttler don't count successful logins ([#1073])

## [v4.4.0]

### Changed Requirements
- PHP 7.3 is now the recommended version, as 7.2 is already security fixes only.

### Changed Composer Dependencies
- Reset Slim version to ^3.12
- Updated PHPUnit to 8.5 (Version 7.5 still used for PHP 7.1)

### Added
- PHP 7.4 Support & Travis environment.
- New `Locale` service. Provides a list of available locales in diffeent form.
- New `BaseServicesProvider` abstract class added as base for all ServiceProvider class.
- Sprinkle Services Provider can now be autoloaded using the `$servicesproviders` property in the sprinkle bootstrapper class.
- Current locale code can now be accessed from Twig using the `currentLocale` global variable ([#1056]).
- Locale now have config & metadata file ([#850])
- Added `locale:compare`, `locale:dictionary` and `locale:info` Bakery commands.
- New `cli` service. Returns true if current app is a CLI envrionement, false otherwise.

### Changed
- `Interop\Container\ContainerInterface` has been replaced with `Psr\Container\ContainerInterface`.
- `\UserFrosting\I18n\MessageTranslator` is now `\UserFrosting\I18n\Translator`.
- Translator service moved to it's own `UserFrosting\Sprinkle\Core\I18n\TranslatorServicesProvider` class.
- Travis now uses Xenial instead of Trusty.
- `site.locales.available` config now accept `(string) identifier => (bool) enabled`. Set identifier to false or null to remove it from the list.
- Locale plural rules moved from the keys file to the new metadata files.

### Fixed
- When internationalizing, the lang attribute value of the Twig template is not set to follow changes ([#982])
- `pt_Br` locale identifier renamed to `pt_BR`.
- Improved Docker support ([#1057])
- Improved Bakery debug command output
- Improve ordering by activity date ([#1061] & [#1062]; Thanks @ktecho!)
- Updated Vagrant config and documentation
- Fixed a bug where `withTrashed` in `findUnique` was not available when `SoftDeletes` trait is not included in a model.
- CSRF global middleware is not loaded anymore if in a CLI environment. This will avoid sessions to be created for bakery and tests by default.
- Browserified node modules not being correctly loaded.
- Browserified node modules potentially colliding with real entrypoints.

### Removed
- `localePathBuilder` service removed. Task now handled by the `locale` and `translator` services.

## [v4.3.3]

### Fixed
- Fixed wrong version number in define
- Locked Slim version to 3.12.2 until UF 4.4 can fix `container-interop/container-interop` replacement with `psr/container`

## [v4.3.2]

### Added
- Add translation for Brazilian Portuguese (locale pt_BR) - Thanks @maxwellkenned ! ([#1036])
- Add translation for Serbian (sr_RS) - Thanks @zbigcheese ! ([#1035])

### Changed
- Updates to the French Locales ([#1027])
- Updates to the German Locales ([#1039])
- Updates to the Thai Locales ([#1041])
- Updates to the Greek Locales ([#1042])
- Updates to the Persian Locales ([#1045])

### Fixed
- Fix issue with hidden fields in group modal ([#1033])
- User cache not flushed on model save ([#1050])
- Fix "the passwords don't match" error when editing a user password ([#1034], [#1038])

### Deprecated
`UserController:updateField` now expect the new value as `$_PUT[$fieldName]` (where `$fieldName` is the name of the field you want to update, eg. `$_PUT['password']` for editing `password`) instead of `$_PUT['value']`. This will only affect your code if you're **not** using the [user widjet](https://github.com/userfrosting/UserFrosting/blob/master/app/sprinkles/admin/assets/userfrosting/js/widgets/users.js).

## [v4.3.1]

### Changed
- Update Arabic Locales ([#1028])

### Fixed
- Fix typos in user creation with initial password ([#1030])

## [v4.3.0]

### Changed Requirements
- Changed minimum PHP Version to 7.1

### Changed Composer Dependencies
- Updated Laravel Illuminate packages to 5.8
- Updated Twig to 2.11
- Updated PHPUnit to 7.5
- Updated Mockery to 1.2
- Updated nikic/php-parser to 4.2.2
- Updated PHPMailer/PHPMailer to 6.0.7
- Updated league/csv to 9.2.1
- Updated symfony/console to 4.3
- Updated vlucas/phpdotenv to 3.4.0

### Changed Frontend Dependencies
- Updated handlebar from 3.0.x to 4.1.2
- Updated AdminLTE theme to 2.4.15 ([#994]; [#1014]; [#1015])
- Updated Font Awesome to 5.9 ([#957]; [#870])

### Added
- Separated `BakeCommand` class into multiple methods to make it easier for sprinkle to add custom command to the `bake` command.
- Allow null group assignment for users ([#867]; [#964])
- Password can now be set manually when creating new user ([#1017]; [#763])
- Icon picker for user and group form ([#713]; [#1019])

### Fix
- `bake` command return error if account sprinkle is not included ([#944])
- Email is case-sensitive ([#881]; [#1012])
- Update vulnerable handlebars@^3.0.3 to ^4.0.12 ([#921])
- Moved `handlebars-helpers.js` from `core` to `admin` sprinkle ([#897])
- Remove `Package guzzle/guzzle is abandoned, you should avoid using it. Use guzzlehttp/guzzle instead` message ([#1016])

### Changed
- Account sprinkle now extend the Core `BakeCommand` class to add the `create-admin` to the general bake command. Any sprinkle already extending the Core `BakeCommand` might need adjustments.
- Updated custom Eloquent relations (`belongsToManyThrough`, `morphToManyUnique`, `belongsToManyUnique`, `morphToManyUnique`, etc.) to support Laravel 5.8. See [The `belongsToMany` Method](https://laravel.com/docs/5.5/upgrade#upgrade-5.5.0).

### Removed
- Removed `belongsToManyConstrained` (deprecated in 4.1.6)
- Remove `league/flysystem-aws-s3-v3` and `league/flysystem-rackspace` as core dependencies ([#1018])

## [v4.2.3]

### Added
- Config to set Domain of RememberMe Cookie ([#990], [#991]; Thanks @xrobau !)
- Config settings for password min/max length ([#993])
- `migrate:clean` bakery command ([#1007])

### Fixed
- [PHPMailer] Turn off opportunistic TLS when disabled ([#986], [#987])
- Migrator now ignore files that don't end in `.php` ([#965], [#998])
- Respects CSRF_ENABLED environment variable ([#976]; Thanks @Poldovico !)
- Checkbox bug on password change form ([#1008])
- On role page, users table action buttons not working ([#1010])

## [v4.2.2]

### Added
- New group factory (`'UserFrosting\Sprinkle\Account\Database\Models\Group'`)
- New `withController` Trait, as an alternative for deprecated `ControllerTestCase`
- StyleCI config
- [Travis] SQLite in-memory DB testing
- [Travis] enabled memcache & Redis service

### Fixed
- DefaultPermissions seed results in SQL errors ([#981]; [#983])
- Make group & role schema consistent between creation and edition. Prevents group and role without a name or slug to be created during edition.
- Factories changed to make sure slugs are unique
- Fix `WithTestUser` Trait returning a user with id of zero or reserve master id when a non-master user was asked. If master user already exist, will return it instead of trying to create a new one (with the same id)
- Force close db connection on test `tearDown` procedure

### Changed
- Recommended PHP version is now 7.2, as 7.1 will be EOL in less than 6 months
- Added tests coverage for all build-in controllers
- Applied styling rules from StyleCI & updated php-cs-fixer rules to match StyleCI config

### Deprecated
- `ControllerTestCase`. Use `withController` Trait instead.


## [v4.2.1]

### Added
- `UserFrosting\Sprinkle\Core\Database\Models\Session` model for the `sessions` db table.
- `TEST_SESSION_HANDLER` environment variable to set the session save handler to use for Testing.
- `withDatabaseSessionHandler` Trait for testing. Use `$this->useDatabaseSessionHandler()` to use database session handler in tests.

### Fixed
- Italian translation ([#950])
- User Registration failing when trying to register two accounts with the same email address ([#953])
- Bad test case for `CoreController::getAsset`.
- User Model `forceDelete` doesn't remove the record from the DB ([#951])
- Fix PHP Fatal error that can be thrown when registering a new User
- Session not working with database handler ([#952])
- Remove any persistences when forceDeleting user to prevent Foreign Key Constraints issue ([#963])
- More helpful error message in checkEnvironment.php (Thanks @amosfolz; [#958])
- Hide locale select from UI if only one locale is available (Thanks @avsdev-cw; [#968])
- Download CSV filename error ([#893])

## [v4.2.0]
### Changed Requirements
- Changed minimum Node.js version to **v10.12.0**
- Changed minimum NPM version to **6.0.0**

### Added
- Use locale requested by browser when possible for guests ([#718])
- Add locale drop down to registration page, with the currently applied locale selected ([#718])
- Added new `filesystem` service ([#869])
- Greek locale (Thanks @lenasterg!; [#940])
- Add cache facade (Ref [#838])
- Added new `Seeder`
- `NoCache` middleware to prevent caching of routes with dynamic content
- Bakery :
    - Added `sprinkle:list` Bakery Command
    - Added `migrate:status` Bakery Command
    - Added `test:mail` Bakery Command
    - Added `seed` Bakery command
    - New `isProduction` method for Bakery command to test if app is in production mode
    - Added `database` option for `migrate` and `migrate:*` Bakery commands
    - Added arguments to the `create-admin` and `setup` Bakery commands so it can be used in a non-interactive way ([#808])
    - Extended `bakery test` to add Test Scope and sprinkle selection argument ([#919], Thanks @ssnukala !)
- Testing :
    - Added `RefreshDatabase` test Trait to use a fresh database for a test
    - Added `TestDatabase` test Trait to use the in memory database for a test
    - Added tests for migrator and it's components
    - Added tests for `migrate` Bakery command and sub-commands
    - Added `withTestUser` trait for helper methods when running tests requiring a user
    - Added `ControllerTestCase` special test case to help testing controllers
    - Improved overall test coverage and added coverage config to `phpunit.xml`
- Assets :
    - Added support for npm dependencies on the frontend with auditting for known vulnerabilities
- Database :
    - Implement `withRaw`, `withSum`, `withAvg`, `withMin`, `withMax` (see https://github.com/laravel/framework/pull/16815)
- Vagrant / Docker :
    - Include Vagrant integration directly inside UF ([#829])
    - Sample test environment for Docker
- Misc :
    - Integrated improvements from [v4.0.25-Alpha](#v4025-alpha)
    - Added code style config (`.php_cs`) and instructions for PHP-CS-Fixer in Readme
    - Add support for other config['mailer'] options ([#872]; Thanks @apple314159 !)

### Changed
- Move User registration out of the `AccountController` ([#793])
- Rewritten the `locator` service so it's better suited for sprinkle system ([#853])
- Bakery :
    - Moved Bakery commands from `app/System/Bakery` to the `Core` sprinkle and `UserFrosting\Sprinkle\Core\Bakery` namespace.
    - Improved `route:list` Bakery command from [v4.1.20](#v4.1.20)
    - Sprinkle list in the bakery `debug` command to uses the new `sprinkle:list` table
- Migrations & Database :
    - `migrate` and `migrate:*` Bakery command now require confirmation before execution when in production mode.
    - Re-written the migrator. It is now detached from the console and Bakery and is now included in the Core Sprinkle ServicesProvider ([#795])
    - Makes the `semantic versioning` part of a migration class optional. Migrations classes can now have the `UserFrosting\Sprinkle\{sprinkleName}\Database\Migrations` namespace, or any other sub-namespace
    - Uncomment foreign keys in core migrations ([#833])
    - Move default groups, roles & permissions creation to seeds
- Assets
    - Rewrote asset processing to minimise file sizes, drastically reduce IO, and improve maintainability
    - Rewrote frontend dependency installation to prevent duplication and detect incompatibilities
    - Rewrite `AssetLoader` to act as a wrapper for `Assets`
- Misc :
    - Updated Docker integration
    - Moved some constants from `app/defines.php` to `app/sprinkles/core/defines.php`
    - Move route initialization from system to core sprinkle as router service is located in the core sprinkle
    - `dev` environment changed to `debug`  ([#653])
    - Changed deprecations to `warning`, and suppressed them in tests
    - `routerCacheFile` config now only contains filename. Locator is used to find the full path

### Fixed
- Sprinkle without a `template/` folder won't cause error anymore
- Fixed routes not available in Tests and Bakery ([#854])
- redirect failing in UserController::pageInfo when user not found ([#888])
- Fix WhoopsRenderer integration, resolving a temp fix in [v4.1.21](#v4.1.21).
- Fix Travis not running tests with the env database
- Ignore existing `package-lock.json` which caused incorrect dependencies to be installed when upgrading from older versions of UserFrosting.
- Testing :
    - Added `coverage-format` and `coverage-path` options to `test` Bakery command
    - Sprinkle Testscope is now case insensitive
    - **Class testscope now relative to `/` instead of `/UserFrosting/Sprinkle/` for more intuitive usage and to enable testing of non sprinkle tests**
    - Detect and use the sprinkle `phpunit.xml` config when testing a specific sprinkle
- SprinkleManager Improvements :
    - Added public `getSprinklePath` method to get path to the sprinkle directory
    - Added public `getSprinkleClassNamespace` method to get sprinkle base namespace
    - Added public `getSprinkle` method. Returns the sprinkle name as formatted in `sprinkles.json` file, independent of the case of the search argument.
    - Public `isAvailable` method now case insensitive.
    - Added public `getSprinklesPath` & `setSprinklesPath` to return or set the path to the sprinkle dir (`app/sprinkles/`)
    - Added `JsonException` if `sprinkles.json` doesn't contain valid json.
    - Added specific tests for sprinkleManager with 100% test coverage

### Deprecated
- Migrations should now extends `UserFrosting\Sprinkle\Core\Database\Migration` instead of `UserFrosting\System\Bakery\Migration`
- Migrations dependencies property should now be a static property
- Deprecated migration `seed` method. Database seeding should now be done using the new Seeder
- Trait `\UserFrosting\Tests\DatabaseTransactions` has been deprecated. Tests should now use the `\UserFrosting\Sprinkle\Core\Tests\DatabaseTransactions` trait instead. ([#826])

### Removed
- The console IO instance is not available anymore in migrations. Removed the `io` property from migration classes
- Removed Bakery `projectRoot` property. Use the `\UserFrosting\ROOT_DIR` constant instead
- Removed `pretend` option from Bakery `migrate:refresh` and `migrate:reset` commands
- Removed `UserFrosting\System\Bakery\DatabaseTest` trait, use `UserFrosting\Sprinkle\Core\Bakery\Helper\DatabaseTest` instead.
- Removed `UserFrosting\System\Bakery\ConfirmableTrait` trait, use `UserFrosting\Sprinkle\Core\Bakery\Helper\ConfirmableTrait` instead.


## v4.1.22
- Updated Docker `README.md`.
- Replaced `libpng12-dev` which has been dropped since Ubuntu 16.04 with `libpng-dev` in PHP `Dockerfile`.
- Avoid twig deprecation warning ([#911](https://github.com/userfrosting/UserFrosting/pull/911); Thanks @silvioq !)

## v4.1.21
- Locked Whoops to version 2.2.1 until they fix that [`[internal]` issue](https://github.com/filp/whoops/issues/598).

## v4.1.20
- Added `route:list` command to list all registered routes ([#903](https://github.com/userfrosting/UserFrosting/pull/903); Thanks @apple314159 !)
- Added warning in configuration file regarding disabling registration and email verification ([#900](https://github.com/userfrosting/UserFrosting/pull/900); Thanks @linkhousemedia !)

## v4.1.19
- Prevent setup to run again if already configured when using `bake`
- Fix `Unique::getPaginatedQuery` to call to `addSelect` instead of `select` during the pre-paginated query
- Updated Spanish Translation (Thanks @silvioq !)
- Fix error template in WhoopsRenderer (#885; Thanks @silvioq !)

## v4.1.18
- Bakery setup wizard for SMTP config + separate SMTP setup in it's own command (https://github.com/userfrosting/UserFrosting/issues/874)
- Update Italian translations (https://github.com/userfrosting/UserFrosting/pull/875)
- Return deleted `row` in `ufCollection` event (https://github.com/userfrosting/UserFrosting/pull/873)

## v4.1.17-alpha
- Lock `gulp-uf-bundle-assets` at v2.28.0 until userfrosting/gulp-uf-bundle-assets#5 is resolved (see #859)
- Add missing getInfo methods for GroupController and RoleController (#837)

## v4.1.16-alpha
- Fix for `merge` bundling rule (#660)
- Fix for undefined variable exception under strict mode in `ufAlerts` (#809)
- Fix for site cache reset upon login (#828)
- Changed global cache tag to proper prefix
- Fix broken alert message in registration process (#843)
- Add partial Turkish translation

## v4.1.15-alpha
- Refactor `Password` into a instantiable `Hasher` class, service, and `Password` facade (#827)
- Change default hash cost back to 10 and fix legacy hash detection issue

## v4.1.14-alpha
- Fix issue with scopes being applied twice in `Unique::getPaginatedQuery` (https://github.com/userfrosting/extend-user/issues/2)
- Update Bower dependencies in core Sprinkle
- Refactor the `Password` class to use `hash_equals` for legacy passwords (prevent timing-based attacks) and factor out the default cost (#814)
- Check if `require_email_verification` is set in `Authenticator` and sign-in page (#815)
- Factor out hardcoded `sprinkles.json` filename (partially addresses #813)
- Add Farsi translations (#816)
- `ufTable`: Make `tableElement` configurable, and check for existence (#824)
- Put AdminLTE menu toggle button back (Revert e8a26fb and part of a46205f)

## v4.1.13-alpha
- `ufTable`: Implement `rowTemplate` for customizing how rows are rendered (#787)
- `ufTable`: Support for passing callbacks for column templates instead of Handlebars templates
- Remove explicit references to `id` columns (#803)
- Fix Docker
- Append full name to User API

## v4.1.12-alpha
- Separate out user action column from user status column
- Improve table row menus in mobile views (#724)
- Hide side menu toggle button in desktop sizes
- Add chevron to user menu
- Change "remember me" text
- Improve table tool buttons
- Twig extensions now implement `Twig_Extension_GlobalsInterface` as required by https://twig.symfony.com/doc/2.x/advanced.html#id1 (#788)
- Display element based on permissions for group list/info pages
- Factor the admin user creation out of migrations and into its own Bakery command (See #778)
- Bakery `clear-cache` command now clears Twig and router cache (Fix #750)
- Add Russian translations
- Add Travis for automated test/build on push

## v4.1.11-alpha
- Updated `composer/installers` dependency.
- Patch `composer.json` to fix `illuminate/*` dependencies at 5.4 for now

## v4.1.10-alpha
- Add support for PHP7 runtime errors to be handled in the same way as Exceptions
- Implement NotFoundExceptionHandler and pass through all NotFoundExceptions to this handler.
- Implement `redirect.onAlreadyLoggedIn` service (fixes #680)
- Deprecate `determineRedirectOnLogin` and replace with `redirect.onLogin` service
- Fix some PSR-2 compliance issues

## v4.1.9-alpha
- Fixes #780, and more efficient way to collect ids in Unique::getPaginatedQuery
- Show "user deleted" in activities table (#782)
- Patched version of `widget-sort2Hash.js` to prevent writing extraneous browser history entries (#712)
- Improve handling of fatal/parse errors

## v4.1.8-alpha
- Normalize paths to always have a leading slash when comparing against the CSRF blacklist (#775) (possible breaking change for some environments - please see updated docs at https://learn.userfrosting.com/routes-and-controllers/client-input/csrf-guard#blacklisting-routes)
- Set `display_errors` to `true` for development configs (#762), move php settings into a common `php` subkey in config files
- `ShutdownHandler` no longer responsible for logging fatal errors
- Set up PHP config values in `Core.php` instead of inside the `config` service definition.
- Reimplement `Builder::exclude` to maintain a list of excluded columns, and then automatically update list of columns to fetch in `get()`
- Deprecate `Model::queryBuilder` and `Model::export`
- Update nginx config file from spdy to http2
- Add Pagespeed block (commented out) to nginx config file
- Make fpm-php7.0 the default CGI nginx config file

## v4.1.7-alpha
- Add the `withTernary` method to the `Unique` trait to allow loading nested child models on ternary relationships.
- Add skip, take, limit, offset and deprecate withLimit and withOffset in `Unique` trait
- Support for `withPivot` on `Unique` relationships (in tertiary models)
- Factor out common code from `PermissionUserSprunje` into `UserSprunje`
- Rework internals of `Sprunje` to make it more testable.  Filters, sorts, and paginations are now applied to a clone of the original queriable object.  Deprecated `getResults` and added `getArray` and `getModels`.  Result keys can now be customized.
- Table of user permissions on user info page
- Simplify by combining `permission-users.html.twig` into options on `users.html.twig`
- Add Chinese translations
- Deprecate User::exists() (#771)
- Add 'password' to hidden fields for User model
- Replace hardcoded `Builder` with classMapper reference

## v4.1.6-alpha
- Fix missing permission check when `uri_account_settings` is not in role (#768)
- Add `getLastRow` method and `transformDropdownSelection` option to `ufCollection`
- Fix missing slug for permissions in "manage permissions" dropdown
- Add "manage permissions" to role page menu
- Factor out custom relation methods into `HasRelationships` trait on `Model`
- Add `withoutGlobalScopes` to `Syncable::sync`
- Add option to use `forceCreate` in `Syncable::sync`
- Add option to use custom key in `Syncable::sync`
- Complete redesign of `BelongsToManyThrough` - possible BC for a few people, as you now need to load the "via" models explicitly using `withVia`.  This fixes a lot of issues with `BelongsToManyThrough`.
- Deprecate `BelongsToManyConstrained`
- Add `MorphToManyUnique`
- Integration tests now use an in-memory sqlite database (`test_integration`) by default

## v4.1.5-alpha
- Spanish language support (#770)
- Show current filter in select-menu filters (#744)
- Cursor styling for ufCopy
- Transition overlay for ufTables
- Minor fix to ufTable.cleanHash
- Correctly target pager container in Tablesorter options
- Add table of users to role pages
- Fix issue with links in permission users table

## v4.1.4-alpha
- Permissions rows get duplicated when upgrading from 4.0 to 4.1 (fix #759)
- Fix migrate:rollback not running down the migration in the correct order
- Updated type in `composer.json` for default sprinkles
- Added missing french translations & more default validation messages
- Bump Fortress version (fix #766)
- Support SQLite in Bakery setup
- Fix for PostgreSQL charset in Bakery (#745)

## v4.1.3-alpha
- Add Italian translations
- Add `data-priority` attributes to built-in tables (#752)
- Use `this.slug` to avoid conflict with helper names (#748)
- Add block `table_search` to `table-paginated.html.twig`

## v4.1.2-alpha
- Remove call to setFilters that was causing problems with pagination (#688)
- Update German translations and factor out some hardcoded text (#725)
- Update French translations
- Update Arabic translations (#733, #734, #735)

## v4.1.1-alpha
- Fixed missing template in ExceptionHandler and `notFoundHandler`
- Migration rollback will throw a warning if a class is not found instead of aborting
- Temporary fix for trouble with `npm install` in Windows (#742)

## v4.1.0-alpha
- Switch from pagination "plugin" to "widget" for Tablesorter.  Allows us to update to the latest version of TS (fix #688, #715)
- Implement `WhoopsRenderer` for pretty debug pages.  See (#674)
- Refactor error handling.  Move responsibility for displayErrorDetails to handlers, and factor our ErrorRenderers.  Addresses (#702)
- Move `composer.json` to root directory to allow installing UF via composer create-project
- Move `sprinkles.json` to app directory to make it easier to find
- Eliminate the `root` theme Sprinkle.  Custom styling for the root user is now handled with some Twig logic in the `admin` Sprinkle (#726)
- Rename bundle.config.json -> asset-bundles.json (#726)
- Reorganize assets (#726)
- Heavily reorganize templates (#726)
- Move request schema from `schema/` to `schema/requests/` (#726)
- Factor out "system" classes from core Sprinkle
- Refactor overall application lifecycle; move main lifecycle into UserFrosting\System\UserFrosting
- SprinkleManager now better focused on a single responsibility
- Sprinkle initializer classes now use events to hook into application lifecycle
- Support for allowing Sprinkles to register middleware (#617)
- Automatically load Sprinkle service providers (see #636)
- Get rid of "implicit loading" for core Sprinkle - core is now just an ordinary Sprinkle like any other.
- The `sprinkles://` stream now represents a virtual filesystem for the root directory of each loaded sprinkle, rather than the `sprinkles/` directory itself.
- Separate out `localePathBuilder` from the `translator` service.  Makes it easier to add/remove paths before actually loading the translations.
- Only present locale options with non-null names.
- Rebased ufTable and ufModal with new jQuery plugin template. (part of #646)
- Removed the search bar from the Dashboard layout
- Added Tablesorter pagination translation
- New Translator Facade
- New CLI tool (Bakery).
- New migration system based on bakery CLI
- Listable sprunjing
- Refactor groups and user routes (Fix #721)
- Added the `config` alert stream to save ufAlerts to the cache instead of sessions. Fix #633. The old `session` is still the default alertStream in 4.1.
- Added support for the Redis cache driver and refactored the cache config values.
- Added user and session cache.
- Common log file for db queries, auth checks, smtp, errors, and debug messages (#709).
- Use YAML as default format for request schema (#690)

See [http://learn.userfrosting.com/upgrading/40-to-41](Upgrading 4.0.x to 4.1.x documentation) for complete list of changes and breaking changes.

## v4.0.25-Alpha
- Support npm for frontend vendor assets, and deprecation of bower (#737)
- Duplicate frontend vendor assets are no longer downloaded (#727)
- Detect incompatibilites between frontend vendor assets (related to #727)
- Improved reliability of generated base URL, especially when using docker
- Fixed syntax error in Portugese translations
- Minimise verbosity of assets build scripts when not in 'dev' mode
- Fix to stop bower complaining about sudo when using docker
- The `assetLoader` service has been deprecated, and may be removed in the future.
- **Potential breaking change:** Some packages like `Handlebars` are organised differently at npm. If referencing vendor assets introduced by UF, make sure they are still correct.

## v4.0.24-alpha
- Fixes to nginx config file, and add location block for LE acme challenge
- Fix JS errors when `#alerts-page` is not present on a page
- Fix hardcoded User class in AdminController (#753)
- Update message PASSWORD.FORGET.REQUEST_SENT (#749)

## v4.0.23-alpha
- Set module dependency versions to ~4.0.0 instead of ^4.0.0 (since 4.1.x will introduce breaking changes)
- Fix bug in ufCollection

## v4.0.22-alpha
- Fix issue where 'Change User Password' popup form couldn't handle specifying a new password.
- Display message when there are no results in `ufTable`

## v4.0.21-alpha
- Implement reflow and column selector for tables (#670)
- Overhauled ufAlerts, improving efficiency, reliability, and fixed a discovered edge case that caused `render` to never complete. (part of #646)
- ufAlerts will only auto-scroll when outside the viewport (even if only partially). Can be overriden with `scrollWhenVisible: true`. (#714)
- Rebased ufCollection, and ufForm with new jQuery plugin template. (part of #646)
- Misc UI update
- Added Twig blocks
- Fix issue with duplicate query logs when using multiple databases

## v4.0.20-alpha
- Remove pivot columns from pagination subquery in BelongsToManyThrough, to deal with MySQL's `only_full_group_by` warning

## v4.0.19-alpha
- Explicit column names in new user permissions relations

## v4.0.18-alpha
- Permission info page (#638)
- New custom relationships 'BelongsToManyThrough', 'BelongsToManyUnique', 'BelongsToManyConstrained', 'HasManySyncable', 'MorphManySyncable'
- Change implementation of User::permissions() to use BelongsToManyThrough
- New ufForm options: setting reqParams, encType, submittingText
- ufCollection now triggers a check for virgin rows when _any_ control is touched
- Fix issue with Sprunje when generating CSV with empty child collections (#697)
- Authorizer now correctly interprets string literals (#482)
- Authorizer now correctly interprets numeric types in access conditions.  **Caution**: this causes the `equals()` callback to return true in situations where it would have (incorrectly) returned false before.  For example, `equals(self.group_id,2)` would have returned false for users in group 2, because it was interpreting `2` as a string and then performing its strict comparison.  It now (correctly) returns true.  Notice that `equals(self.group_id,'2')`, on the other hand, will now return `false`.
- User object caches permissions loaded from DB to reduce number of queries (#612)
- Type declarations in authorization classes (#652)
- Fix issue with Twig debug (#356)
- Show disabled/unactivated badges on user info page

## v4.0.17-alpha
- Add IIS config file (#371)
- New ufCollection now supports free text input mode
- New design and layout for user, group, and role summary boxes (also fixes #703)
- Registration page returns 404 when registration disabled (#705)

## v4.0.16-alpha
- Add Docker configuration files
- Begin work on Bakery, the command-line debug tool
- Lock version of tablesorter due to breaking changes
- Fix bugs in GroupController and RoleController
- Fix bug in URLs for redirect-on-login
- Added UTF-8 as default mail charset

## v4.0.15-alpha
- Prevent mixed content on demo pages
- Fixed some missing translations
- Fixed error in ufAlerts push method
- Fixed usage of hard coded path
- Fixed default OS option in migration script
- Prevents empty locale's from displaying as empty options in profile form
- Unignore .gitkeeps of directories that need to exist

## v4.0.14-alpha
- Fix ajax.delay in ufCollection
- Fix missing translations
- Minor fix in French translation
- Fix alert margin when displayed inside a modal

## v4.0.13-alpha
- Update to RememberMe 2.0 (https://github.com/userfrosting/UserFrosting/issues/635)
- Remove database checks, as they are apparently no longer needed (https://github.com/userfrosting/UserFrosting/issues/655)
- Bump dependencies

## v4.0.12-alpha
- Separate out the registration and sign-in pages (https://github.com/userfrosting/UserFrosting/issues/657) **BC**
- Slightly change behavior of form validation icons
- Sprunje input validation (https://github.com/userfrosting/UserFrosting/issues/640)
- Sprunje sort/filter fields now must be explicitly listed in a whitelist (https://github.com/userfrosting/UserFrosting/issues/640) **BC**
- Errors from tablesorter now get displayed
- Support for OR expressions using `||` in Sprunje filters (https://github.com/userfrosting/UserFrosting/issues/647)

## v4.0.11-alpha
- Fix [#663](https://github.com/userfrosting/UserFrosting/issues/663)
- Adding more Twig `blocks`
- ufAlerts now scroll to alert location, if and only if alerts are output.
- Updated Dutch locale
- Minor update in French locale
- Added comments in `.env.example`

## v4.0.10-alpha
- Move suggestion button outta-da-way
- Add email to registration success message
- Separate out some page content into smaller blocks
- Factor out select2 options in ufCollection, into the 'dropdown' key so that any select2 option can be set

## v4.0.9-alpha
- Oops, `exists` needs to be static

## v4.0.8-alpha
- Autogenerate and suggestion features for usernames during account registration (partially addresses https://github.com/userfrosting/UserFrosting/issues/569)
- Restrict username characters to a-z0-9.-_
- Require first name by default
- Throttle registration attempts
- Implement User::exists method
- keyupDelay option in ufForm
- More logging of group and role CRUD
- Implement extra:// stream
- Lots of missing translation keys

## v4.0.7-alpha
- Separate "profile settings" from "account settings"

## v4.0.6-alpha
- Fix throttling issue #656
- Other miscellaneous fixes

## v4.0.5-alpha
- Allow nulling out of throttle rules (to disable)
- Disable Google Analytics by default (but enabled in production)
- Other miscellaneous fixes

## v4.0.4-alpha
- UfAlert style customization (See [#634](https://github.com/userfrosting/UserFrosting/issues/634))
- Translation function can now display raw placeholder using the `|raw` filter in the placeholder name. Other Twig filters are also avaiable. Requires latest version of the [i18n](https://github.com/userfrosting/i18n) component (See [#621](https://github.com/userfrosting/UserFrosting/issues/621)).
- Fix the "Root account" message breaking the UI on smaller screens (See [#641](https://github.com/userfrosting/UserFrosting/issues/641)) - Thanks @brunomnsilva !
- Added `DB_DRIVER` and `DB_PORT` as environment variables to allow better out-of-box database configuration support, and to provide additional protection by obscurity.
- Normalised default values to `null` for environment variables in configuration.
- Added `getCallbacks` public method to `AuthorizationManager` to enable drop-in extensions to `AuthorizationManager`.
- Fixed broken links in generated asset bundles.
- Introduced `clean` gulp task to act as a shotcut for removing all frontend vendor packages, all generated asset bundles, and copied assets. Accessible via `npm run uf-clean`.
- Merged `copy` task with `bundle-build`.
- Fixed missing translations
- Added Thai translation - Thanks @popiazaza !

## v4.0.3-alpha
- Add config file for nginx (https://github.com/userfrosting/UserFrosting/issues/373)
- Add Portuguese translations (thanks to @brunomnsilva!)
- Add Arabic (MSA) translations (thanks to @abdullah.seba!)
- Add Dispatcher to db service to allow registering model events.
- Specify foreign keys explicitly in all relationships.
- Use classMapper for admin Sprunjes.

## v4.0.2-alpha
- Specify foreign key explicitly in `User::activities()` relationship.
- Database checks in installer and Authenticator now respect custom database ports. (See [#628](https://github.com/userfrosting/UserFrosting/issues/628))
- Fixed edge case where `5%C` would appear in generated urls.
- Improved stability and added php version check in `migrations/intall.php`
- Update ClassMapper to throw exception when class is not found
- Fix minor errors in French locale
- Fix translation error on the Legal page

## v4.0.1-alpha
- Bump min version of PHP to 5.6
- Added German translation (See [#625](https://github.com/userfrosting/UserFrosting/issues/625)) - Thanks @X-Anonymous-Y
- Improved Gulp Build task
- Remove site-dev from example sprinkles.json
- Fix some styling issues on the Dashboard and footer
- Display group link in menu for group admins
- Keep dashboard sidebar collapsed across page load (See [#616](https://github.com/userfrosting/UserFrosting/issues/616))
- Fixed missing translation keys inside Handlebar tables (See [#624](https://github.com/userfrosting/UserFrosting/issues/624))
- Admin panel link style in main dropdown menu (See [#627](https://github.com/userfrosting/UserFrosting/issues/627))
- Implement AuthGuard middleware
- Handling of redirect after login (See [#627#issuecomment-275607492](https://github.com/userfrosting/UserFrosting/issues/627#issuecomment-275607492))
- Directly check database in installer using PDO
- Refactor installer and how version are displayed in system info panel. Added notice when a migration is available for a sprinkle
- Etc.

## v4.0.0-alpha

**Initial release of UserFrosting V4**

- The [Sprinkle](https://learn.userfrosting.com/sprinkles) system, which keeps your code completely separate from the core UF codebase;
- We're upgraded from Slim 2 to Slim 3, which is significantly different;
- Completely redesigned [database structure](https://learn.userfrosting.com/database/default-tables);
- Initialization is now achieved through [services](https://learn.userfrosting.com/services), with the Pimple dependency injection container;
- [Composer](https://learn.userfrosting.com/installation/requirements/essential-tools-for-php#composer) is now a mandatory part of the installation process;
- [Bower](https://learn.userfrosting.com/sprinkles/contents#-bower-json) is now used to install third-party client-side assets (Javascript/CSS packages);
- "Groups" and "Primary Group" have been replaced by "Roles" and "Group", respectively;
- Tables no longer need to be "registered" in any kind of initialization routine.  Simply set the table names directly in your data models;
- Twig templates have been [reorganized and redesigned](https://learn.userfrosting.com/templating-with-twig/sprinkle-templates);
- SB Admin has been removed and we now use the [AdminLTE](https://adminlte.io/) front-end theme;
- Client-side code has been heavily refactored into reusable [components](https://learn.userfrosting.com/client-side-code/components).

## v0.3.1.23
- Also fix the `occurred_at` timestamp in the `user_event` table to allow null, for newer versions of MySQL that don't allow a zero date (see #605).

## v0.3.1.22
- Use `nullableTimestamps` instead of `timestamps` in installer, to prevent conflict with MySQL modes 'NO_ZERO_IN_DATE' and 'NO_ZERO_DATE'.

## v0.3.1.21
- Use Laravel's Schema interface to create tables and default rows, instead of constructing them with SQL

## v0.3.1.20
- Added `pushAlert()`,`clearAlerts()` in `public/js/userfrosting.js` and updated `flashAlerts()`
- Revert changes to User::fresh() but leave comment regarding upgrading Eloquent

## v0.3.1.19
- Fix some minor error screen layout issues
- Make User::fresh() compatible with Eloquent\Model v5.2.40+
- Update composer require to allow for Fortress 1.x bugfixes
- Allow database port definitions in config-userfrosting.php
- Fix fatal error when evaluateCondition is called before the router populates current route information

## v0.3.1.18
- Add check for logging being enabled but log file not existing yet

## v0.3.1.17
- Fix occasional bug when end-of-file is reached on log file before requested number of lines is reached
- Roll back database connection checking to fix installer routines (frostbitten)
- UI fixes for smaller screens (frostbitten)
- Update Gitter references to Rocket.chat
- Clarify hotfix branch procedure for contributions

## v0.3.1.16
- Fix comment reference to \Fortress\JqueryValidationAdaptor
- CONTRIBUTING.md - Add note about proper Pull Requests
- French language file fixes (#565) (lcharette)
- Add HTTP status codes to 404 errors and database errors (frostbitten)
- Change database errors to use BaseController instead of DatabaseController (frostbitten)

## v0.3.1.15
- Fix unattached submitHandler bug in Group and Auth edit interfaces (#465)
- Remove references to nonexistent `formUserView` and `formGroupView` (#478)
- Gracefully handle session destruction due to missing or disabled accounts (#510)
- Add `attributeExists` and `relationExists` for models (#520)

## v0.3.1.14
- Stop reading entire log files to avoid out-of-memory errors (#497)
- Deploy [league/csv](https://github.com/thephpleague/csv) to properly generate CSV files (#557)
- Fix typos in language files

## v0.3.1.13
- Bump dependencies
- userfrosting/fortress now has a release version

## v0.3.1.12
- Add sendmail support in Notification class
- Fix problem with strict comparison in Handlebars templates and inconsistent data types among different database technologies
- Override paths to font files for Bootstrap Glyphicons to support the UserFrosting directory structure
- Added missing lines of Thai language (popiazaza)
- Fix a vulnerability where users still logged in wouldn't automatically be logged out if they were disabled
- Add option for HTTPS in `.htaccess`, commented out by default
- Minor syntax fixes in `public/js/userfrosting.js`, `widget-auth.js`, `widget-groups.js`, and `widget-users.js`

## v0.3.1.11
- Composer can now include composer.json files from plugin folders (added "wikimedia/composer-merge-plugin" to composer)

## v0.3.1.10
- Select correct versions (PHP 5.x compatible) of packages in `composer.json`
- Turkish language translation
- Return `User` object created in `AccountController::register`

## v0.3.1.9
- Revert to loose comparison for `user_id`s because of issues with Ubuntu's PDO driver (see http://stackoverflow.com/questions/5323146/mysql-integer-field-is-returned-as-string-in-php#comment41836471_5323169)

## v0.3.1.8
- Finish replacing all usages of `*Loader` classes with Eloquent syntax
- Installer warning for missing `imagepng`
- Fix bug in CSV generation for user table

## v0.3.1.7
- Change "default theme" to "guest theme" and fix loading issues (#463).  What used to be called "default theme" is now base theme, i.e. the theme to fall back to when a template file cannot be found in the current theme (user group or guest theme)
- New public template for "nyx" theme
- Remove trailing slash from configuration JS/CSS paths to make uniform with site.uri.public
- Make routes for config.js and theme.css dynamically generated from configuration variables (#461)
- Make cookie name for "remember me" use session name
- Fix potential bug in configuration user_id's for guest, master accounts

## v0.3.1.6
- Fix exception-handling for mail server errors
- Notify if account creation was successful, even if mail server failed.

## v0.3.1.5
- Add Romanian translation
- Upgrade Tablesorter and pretty URLs for searched/sorted/paginated tables
- Fix bug in default value for user `secret_token`

## v0.3.1.4
- .htaccess redirect trailing slash: change to only redirect GET requests
- Natural sort order in API
- Fix bug in table pagination
- Fix bug in loading user primary group properties as user properties
- Fix mailto link bug in tables
- Warn if config file missing (#445)
- Fix dutch error (#447)

## v0.3.1.3
- Implement CSV download feature

## v0.3.1.2
- Implement `no_leading_whitespace` and `no_trailing_whitespace` rules

## v0.3.1
- Improved initialization routine as middleware
- Implemented "remember me" for persistent sessions - see https://github.com/gbirke/rememberme
- Converted page templates to inheritance architecture, using Twig `extends`
- Start using the `.twig` extension for template files
- All content is now part of a theme, and site can be configured so that one theme is the default theme for unauthenticated users
- User session stored via `user_id`, rather than the entire User object
- Data model is now built on Eloquent, instead of in-house
- Cleaned up some of the per-page Javascript, refactoring repetitive code
- Implement server-side pagination
- Upgrade to Tablesorter v2.23.4
- Switch from DateJS to momentjs
- Switch to jQueryValidation from FormValidation
- Implement basic interface for modifying group authorization rules
- User events - timestamps for things like sign-in, sign-up, password reset, etc are now stored in a `user_event` table
- Wrapper class Notification for sending emails, other notifications to users
- Remove username requirement for password reset.  It is more likely that an attacker would know the user's username, than the user themselves.  For the next version, we can try to implement some real multi-factor authentication.
- When a user creates another user, they don't need to set a password.  Instead, an email is sent out to the new user, with a token allowing them to set their own password.
- Admins can manually generate a password reset request for another user, or directly change the user's password.

## v0.3.0
- [Autoloading with Composer](https://v3.userfrosting.com/navigating/#composer)
- [MVC Architecture](https://v3.userfrosting.com/navigating/#structure)
- [Front Controllers and the Slim Microframework](https://v3.userfrosting.com/navigating/#slim)
- [Twig - Templating](http://twig.sensiolabs.org/)
- [Theming](https://v3.userfrosting.com/components/#theming)
- [Plugins](https://v3.userfrosting.com/components/#plugins)

## v0.2.1
- Implemented db-driven menu system.  Menu items are pulled from the database, and can be modified via plugins.
- Implemented backend templating of forms and tables via [Bootsole](https://github.com/alexweissman/bootsole).

## v0.2.0 (butterflyknife)
- Converted all DB calls to PDO.
- Renamed "permissions" to "groups".  Same concept, but using the word "group" suggests that it can be used for more than just access control.
- Implemented "primary group" membership for users.  A user can belong to multiple groups, but only one of those will be their primary group.
- Implemented DB-driven home pages for groups.  Upon login, a user will be redirected to the `home_page` for their primary group.
- Implemented templated menus.  Every group has a corresponding menu template in `models/menu-templates`.  Upon login, the menu for a user's primary group is automatically loaded and rendered.
- Implemented function-level user authorization.  Whenever a function in `secure_functions` is called, the `user_action_permits` table is checked to see whether or not that user has access to the function (the `action` column), conditional on the boolean functions specified in the `permits` column.
- Organized pages into four categories: account pages, API pages, form pages, and public pages.  Public pages reside in the root directory and can be accessed by anyone.  Account pages are in the `account` directory and are only accessible after logging in.  API pages are in the `api` directory, and consist of all the pages that process or fetch data from the DB and interact with the frontend via AJAX/JSON.  They are accessible by any logged in user, but will only perform a function if the user is authorized.  Form pages are in the `forms` directory, and consist of pages that generate forms (for creating/updating users, groups, etc.)
- Converted registration page to AJAX.
- Improved installer with site configuration.

## v0.1.7
- Page scrolls back to top after AJAX submit.
- "Website url" is automatically suffixed with "/" if necessary.
- Fixed bad link to forgot_password.php.
- Began implementing action authorization scheme.

## v0.1.6
- Implemented CSRF token checking for creating and updating users
- Moved much of the nuts and bolts for generating the user-create and user-update forms to the server side, so as to streamline rendering process and require fewer requests by the client (see load_form_user.php)
- Improved responsive layout for rendering nicely on mobile devices

## v0.1.5
- More improvements to error-handling/rendering
- HTTPS/SSL compatible
- Fixed bug with different table name prefixes
- Improvements to CSRF tokens

## v0.1.4
- Updated password hashing from md5 to modern bcrypt (more secure) - thanks to contributor @r3wt
- Included better functions for sanitizing user input, validating user ip, generating csrf (cross-site request forgery) tokens - thanks to contributor @r3wt

## v0.1.3
- Root account (user id = 1) : created upon installation, cannot be deleted or disabled.
- Special color scheme for when logged in as root user.
- Installer now guides user through creation of root account
- Moved common JS and CSS includes to "includes.php"

## v0.1.2
- Improved error and exception handling
- Added 404 error page
- Standardized JSON interface for backend scripts
- Front-end should now be able to catch virtually any backend error and take an appropriate action (instead of white screen of death)

[#653]: https://github.com/userfrosting/UserFrosting/issues/653
[#713]: https://github.com/userfrosting/UserFrosting/issues/713
[#718]: https://github.com/userfrosting/UserFrosting/issues/718
[#763]: https://github.com/userfrosting/UserFrosting/issues/763
[#793]: https://github.com/userfrosting/UserFrosting/issues/793
[#795]: https://github.com/userfrosting/UserFrosting/issues/795
[#808]: https://github.com/userfrosting/UserFrosting/issues/808
[#826]: https://github.com/userfrosting/UserFrosting/issues/826
[#829]: https://github.com/userfrosting/UserFrosting/issues/829
[#833]: https://github.com/userfrosting/UserFrosting/issues/833
[#838]: https://github.com/userfrosting/UserFrosting/issues/838
[#850]: https://github.com/userfrosting/UserFrosting/issues/850
[#853]: https://github.com/userfrosting/UserFrosting/issues/853
[#854]: https://github.com/userfrosting/UserFrosting/issues/854
[#867]: https://github.com/userfrosting/UserFrosting/issues/867
[#869]: https://github.com/userfrosting/UserFrosting/issues/869
[#870]: https://github.com/userfrosting/UserFrosting/issues/870
[#872]: https://github.com/userfrosting/UserFrosting/issues/872
[#881]: https://github.com/userfrosting/UserFrosting/issues/881
[#888]: https://github.com/userfrosting/UserFrosting/issues/888
[#893]: https://github.com/userfrosting/UserFrosting/issues/893
[#897]: https://github.com/userfrosting/UserFrosting/issues/897
[#919]: https://github.com/userfrosting/UserFrosting/issues/919
[#921]: https://github.com/userfrosting/UserFrosting/issues/921
[#940]: https://github.com/userfrosting/UserFrosting/issues/940
[#950]: https://github.com/userfrosting/UserFrosting/issues/950
[#951]: https://github.com/userfrosting/UserFrosting/issues/951
[#952]: https://github.com/userfrosting/UserFrosting/issues/952
[#953]: https://github.com/userfrosting/UserFrosting/issues/953
[#957]: https://github.com/userfrosting/UserFrosting/issues/957
[#958]: https://github.com/userfrosting/UserFrosting/issues/958
[#963]: https://github.com/userfrosting/UserFrosting/issues/963
[#964]: https://github.com/userfrosting/UserFrosting/issues/964
[#965]: https://github.com/userfrosting/UserFrosting/issues/965
[#968]: https://github.com/userfrosting/UserFrosting/issues/968
[#976]: https://github.com/userfrosting/UserFrosting/issues/976
[#981]: https://github.com/userfrosting/UserFrosting/issues/981
[#982]: https://github.com/userfrosting/UserFrosting/issues/982
[#983]: https://github.com/userfrosting/UserFrosting/issues/983
[#986]: https://github.com/userfrosting/UserFrosting/issues/986
[#987]: https://github.com/userfrosting/UserFrosting/issues/987
[#990]: https://github.com/userfrosting/UserFrosting/issues/990
[#991]: https://github.com/userfrosting/UserFrosting/issues/991
[#993]: https://github.com/userfrosting/UserFrosting/issues/993
[#994]: https://github.com/userfrosting/UserFrosting/issues/994
[#998]: https://github.com/userfrosting/UserFrosting/issues/998
[#1007]: https://github.com/userfrosting/UserFrosting/issues/1007
[#1008]: https://github.com/userfrosting/UserFrosting/issues/1008
[#1010]: https://github.com/userfrosting/UserFrosting/issues/1010
[#1012]: https://github.com/userfrosting/UserFrosting/issues/1012
[#1014]: https://github.com/userfrosting/UserFrosting/issues/1014
[#1015]: https://github.com/userfrosting/UserFrosting/issues/1015
[#1016]: https://github.com/userfrosting/UserFrosting/issues/1016
[#1017]: https://github.com/userfrosting/UserFrosting/issues/1017
[#1018]: https://github.com/userfrosting/UserFrosting/issues/1018
[#1019]: https://github.com/userfrosting/UserFrosting/issues/1019
[#1023]: https://github.com/userfrosting/UserFrosting/issues/1023
[#1027]: https://github.com/userfrosting/UserFrosting/issues/1027
[#1028]: https://github.com/userfrosting/UserFrosting/issues/1028
[#1030]: https://github.com/userfrosting/UserFrosting/issues/1030
[#1033]: https://github.com/userfrosting/UserFrosting/issues/1033
[#1034]: https://github.com/userfrosting/UserFrosting/issues/1034
[#1035]: https://github.com/userfrosting/UserFrosting/issues/1035
[#1036]: https://github.com/userfrosting/UserFrosting/issues/1036
[#1038]: https://github.com/userfrosting/UserFrosting/issues/1038
[#1039]: https://github.com/userfrosting/UserFrosting/issues/1039
[#1041]: https://github.com/userfrosting/UserFrosting/issues/1041
[#1042]: https://github.com/userfrosting/UserFrosting/issues/1042
[#1045]: https://github.com/userfrosting/UserFrosting/issues/1045
[#1050]: https://github.com/userfrosting/UserFrosting/issues/1050
[#1056]: https://github.com/userfrosting/UserFrosting/issues/1056
[#1057]: https://github.com/userfrosting/UserFrosting/issues/1057
[#1061]: https://github.com/userfrosting/UserFrosting/issues/1061
[#1062]: https://github.com/userfrosting/UserFrosting/issues/1062
[#1068]: https://github.com/userfrosting/UserFrosting/issues/1068
[#1073]: https://github.com/userfrosting/UserFrosting/issues/1073
[#1078]: https://github.com/userfrosting/UserFrosting/issues/1078
[#1087]: https://github.com/userfrosting/UserFrosting/issues/1087
[#1092]: https://github.com/userfrosting/UserFrosting/issues/1092
[#1107]: https://github.com/userfrosting/UserFrosting/pull/1107
[#1109]: https://github.com/userfrosting/UserFrosting/pull/1109
[#1114]: https://github.com/userfrosting/UserFrosting/pull/1114
[#1126]: https://github.com/userfrosting/UserFrosting/pull/1126
[#1128]: https://github.com/userfrosting/UserFrosting/pull/1128
[#1117]: https://github.com/userfrosting/UserFrosting/issues/1117
[#1138]: https://github.com/userfrosting/UserFrosting/pull/1138
[#1124]: https://github.com/userfrosting/UserFrosting/pull/1124
[#1121]: https://github.com/userfrosting/UserFrosting/pull/1121
[#1077]: https://github.com/userfrosting/UserFrosting/pull/1077
[#1133]: https://github.com/userfrosting/UserFrosting/issues/1133
[#1085]: https://github.com/userfrosting/UserFrosting/pull/1085

[v4.2.0]: https://github.com/userfrosting/UserFrosting/compare/v4.1.22...v4.2.0
[v4.2.1]: https://github.com/userfrosting/UserFrosting/compare/v4.2.0...v.4.2.1
[v4.2.2]: https://github.com/userfrosting/UserFrosting/compare/v.4.2.1...v4.2.2
[v4.2.3]: https://github.com/userfrosting/UserFrosting/compare/v4.2.2...v4.2.3
[v4.3.0]: https://github.com/userfrosting/UserFrosting/compare/v4.2.3...v4.3.0
[v4.3.1]: https://github.com/userfrosting/UserFrosting/compare/v4.3.0...v4.3.1
[v4.3.2]: https://github.com/userfrosting/UserFrosting/compare/v4.3.1...v4.3.2
[v4.3.3]: https://github.com/userfrosting/UserFrosting/compare/v4.3.2...v4.3.3
[v4.4.0]: https://github.com/userfrosting/UserFrosting/compare/v4.3.3...v4.4.0
[v4.4.1]: https://github.com/userfrosting/UserFrosting/compare/v4.4.0...v4.4.1
[v4.4.2]: https://github.com/userfrosting/UserFrosting/compare/v4.4.1...v4.4.2
[v4.4.3]: https://github.com/userfrosting/UserFrosting/compare/v4.4.2...v4.4.3
[v4.4.4]: https://github.com/userfrosting/UserFrosting/compare/v4.4.3...v4.4.4
[v4.4.5]: https://github.com/userfrosting/UserFrosting/compare/v4.4.4...v4.4.5
[v4.5.0]: https://github.com/userfrosting/UserFrosting/compare/v4.4.5...v4.5.0
[v4.5.1]: https://github.com/userfrosting/UserFrosting/compare/v4.5.0...v4.5.1
[v4.6.0]: https://github.com/userfrosting/UserFrosting/compare/v4.5.0...v4.6.0
[v4.6.1]: https://github.com/userfrosting/UserFrosting/compare/v4.6.0...v4.6.1
[v4.6.2]: https://github.com/userfrosting/UserFrosting/compare/v4.6.1...v4.6.2
