# Change Log

## v0.1.3.9

Revert to loose comparison for `user_id`s because of issues with Ubuntu's PDO driver (see http://stackoverflow.com/questions/5323146/mysql-integer-field-is-returned-as-string-in-php#comment41836471_5323169)

## v0.1.3.8

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

### Autoloading with Composer

http://www.userfrosting.com/navigating/#composer

### Front Controllers and the Slim Microframework

http://www.userfrosting.com/navigating/#slim

### MVC Architecture

http://www.userfrosting.com/navigating/#structure

### Templating with Twig

http://www.userfrosting.com/navigating/#twig

### Theming

http://www.userfrosting.com/components/#theming

### Plugins

http://www.userfrosting.com/components/#plugins

## Libraries

- URL Routing and micro-framework: [Slim](http://www.slimframework.com/)
- Templating: [Twig](http://twig.sensiolabs.org/)
