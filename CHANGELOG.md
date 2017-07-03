# Change Log

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

## v4.1.1-Alpha
- Fixed missing template in ExceptionHandler and `notFoundHandler`
- Migration rollback will throw a warning if a class is not found instead of aborting
- Temporary fix for trouble with `npm install` in Windows (#742)

## v4.1.0-Alpha
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

## v4.0.24-Alpha
- Fixes to nginx config file, and add location block for LE acme challenge
- Fix JS errors when `#alerts-page` is not present on a page
- Fix hardcoded User class in AdminController (#753)
- Update message PASSWORD.FORGET.REQUEST_SENT (#749)

## v4.0.23-Alpha
- Set module dependency versions to ~4.0.0 instead of ^4.0.0 (since 4.1.x will introduce breaking changes)
- Fix bug in ufCollection

## v4.0.22-Alpha
- Fix issue where 'Change User Password' popup form couldn't handle specifying a new password.
- Display message when there are no results in `ufTable`

## v4.0.21-Alpha
- Implement reflow and column selector for tables (#670)
- Overhauled ufAlerts, improving efficiency, reliability, and fixed a discovered edge case that caused `render` to never complete. (part of #646)
- ufAlerts will only auto-scroll when outside the viewport (even if only partially). Can be overriden with `scrollWhenVisible: true`. (#714)
- Rebased ufCollection, and ufForm with new jQuery plugin template. (part of #646)
- Misc UI update
- Added Twig blocks
- Fix issue with duplicate query logs when using multiple databases

## v4.0.20-Alpha
- Remove pivot columns from pagination subquery in BelongsToManyThrough, to deal with MySQL's `only_full_group_by` warning

## v4.0.19-Alpha
- Explicit column names in new user permissions relations

## v4.0.18-Alpha
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

## v4.0.17-Alpha
- Add IIS config file (#371)
- New ufCollection now supports free text input mode
- New design and layout for user, group, and role summary boxes (also fixes #703)
- Registration page returns 404 when registration disabled (#705)

## v4.0.16-Alpha
- Add Docker configuration files
- Begin work on Bakery, the command-line debug tool
- Lock version of tablesorter due to breaking changes
- Fix bugs in GroupController and RoleController
- Fix bug in URLs for redirect-on-login
- Added UTF-8 as default mail charset

## v4.0.15-Alpha
- Prevent mixed content on demo pages
- Fixed some missing translations
- Fixed error in ufAlerts push method
- Fixed usage of hard coded path
- Fixed default OS option in migration script
- Prevents empty locale's from displaying as empty options in profile form
- Unignore .gitkeeps of directories that need to exist

## v4.0.14-Alpha
- Fix ajax.delay in ufCollection
- Fix missing translations
- Minor fix in French translation
- Fix alert margin when displayed inside a modal

## v4.0.13-Alpha
- Update to RememberMe 2.0 (https://github.com/userfrosting/UserFrosting/issues/635)
- Remove database checks, as they are apparently no longer needed (https://github.com/userfrosting/UserFrosting/issues/655)
- Bump dependencies

## v4.0.12-Alpha
- Separate out the registration and sign-in pages (https://github.com/userfrosting/UserFrosting/issues/657) **BC**
- Slightly change behavior of form validation icons
- Sprunje input validation (https://github.com/userfrosting/UserFrosting/issues/640)
- Sprunje sort/filter fields now must be explicitly listed in a whitelist (https://github.com/userfrosting/UserFrosting/issues/640) **BC**
- Errors from tablesorter now get displayed
- Support for OR expressions using `||` in Sprunje filters (https://github.com/userfrosting/UserFrosting/issues/647)

## v4.0.11-Alpha
- Fix [#663](https://github.com/userfrosting/UserFrosting/issues/663)
- Adding more Twig `blocks`
- ufAlerts now scroll to alert location, if and only if alerts are output.
- Updated Dutch locale
- Minor update in French locale
- Added comments in `.env.example`

## v4.0.10-Alpha
- Move suggestion button outta-da-way
- Add email to registration success message
- Separate out some page content into smaller blocks
- Factor out select2 options in ufCollection, into the 'dropdown' key so that any select2 option can be set

## v4.0.9-Alpha
- Oops, `exists` needs to be static

## v4.0.8-Alpha
- Autogenerate and suggestion features for usernames during account registration (partially addresses https://github.com/userfrosting/UserFrosting/issues/569)
- Restrict username characters to a-z0-9.-_
- Require first name by default
- Throttle registration attempts
- Implement User::exists method
- keyupDelay option in ufForm
- More logging of group and role CRUD
- Implement extra:// stream
- Lots of missing translation keys

## v4.0.7-Alpha
- Separate "profile settings" from "account settings"

## v4.0.6-Alpha
- Fix throttling issue #656
- Other miscellaneous fixes

## v4.0.5-Alpha
- Allow nulling out of throttle rules (to disable)
- Disable Google Analytics by default (but enabled in production)
- Other miscellaneous fixes

## v4.0.4-Alpha
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

## v4.0.3-Alpha
- Add config file for nginx (https://github.com/userfrosting/UserFrosting/issues/373)
- Add Portuguese translations (thanks to @brunomnsilva!)
- Add Arabic (MSA) translations (thanks to @abdullah.seba!)
- Add Dispatcher to db service to allow registering model events.
- Specify foreign keys explicitly in all relationships.
- Use classMapper for admin Sprunjes.

## v4.0.2-Alpha
- Specify foreign key explicitly in `User::activities()` relationship.
- Database checks in installer and Authenticator now respect custom database ports. (See [#628](https://github.com/userfrosting/UserFrosting/issues/628))
- Fixed edge case where `5%C` would appear in generated urls.
- Improved stability and added php version check in `migrations/intall.php`
- Update ClassMapper to throw exception when class is not found
- Fix minor errors in French locale
- Fix translation error on the Legal page

## v4.0.1-Alpha
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

## v4.0.0-Alpha
- Initial release of UserFrosting V4