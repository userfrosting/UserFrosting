# Change Log

## v4.0.16-Alpha

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
