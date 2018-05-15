# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

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
