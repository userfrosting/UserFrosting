
# UserFrosting

## Libraries

- URL Routing and micro-framework: [Slim](http://www.slimframework.com/)
- Templating: [Twig](http://twig.sensiolabs.org/)
- Database layer / ORM: [RedBeanPHP](http://redbeanphp.com/)

## Features

### Sessions

The old version of UF suffers from PHP's native sessions randomly expiring.  This may be an issue related to server configuration, rather than a problem with UF itself.  More research is needed.
http://board.phpbuilder.com/showthread.php?10313632-Sessions-randomly-dropped!
https://stackoverflow.com/questions/1327351/session-should-never-expire-by-itself
http://jaspan.com/improved_persistent_login_cookie_best_practice

### Authentication

UserFrosting 0.3.0 will use the same robust authentication system, with Blowfish password hashing.  Password resets will be done via a short-term expiring token.

### Authorization

UserFrosting will control access of three types of resources:
 - uri's
 - database objects
 - custom functions

### Data Sanitization and Validation

UserFrosting provides a schema-based system for sanitizing and validating user data.  This schema consists of a simple JSON file, with rules for how each user-submitted field should be processed.  The `HTTPRequestFortress` class handles backend sanitization and validation, while the `ClientSideValidator` class generates client-side validation rules compatible with the [FormValidation](http://formvalidation.io) Javascript plugin.

 
We need a better interface for modifying permissions:
https://github.com/alexweissman/UserFrosting/issues/127
 
### Theming

We need a theming system.  See https://github.com/alexweissman/UserFrosting/issues/132.

Themes allow custom css and layouts for different groups and users.  Twig templates, in essence, already support this via an elegant system of template directories.

Our theming system consists of a separate folder for each theme, which contains one or more HTML template files and a theme stylesheet, `css/theme.css`.  This stylesheet is imported into the public folder via a special route.  The default theme is "default", and other themes work by overriding this content.  UF will by default look in the "default" theme for template files if if cannot find them in the current theme.

Menus should be automatically built based on a users' permissions.  So, a menu item should show up if and only if a user has permission to access that item.
 
### Plugins


### Internationalization (i18n)


### Alerts

UserFrosting pushes all alerts (warnings, errors, success messages) to a session `MessageStream` object.  This object can be accessed by calling `getAndClearMessages()` on the `alerts` member of the Slim app.  Thus, a typical way to fetch alerts on the server side would be:

```
$alerts = $app->alerts->getAndClearMessages();
```

Session alerts can be retrieved on the client side through the /alerts route.  Messages are sent back to the client in this manner, rather than directly through the HTTP response body, because in some cases we will want to persist messages across one or more requests.  For example, after an AJAX request, you may want to refresh the page **and then** display the alerts.  If the messages were directly part of the HTTP response from the AJAX request, they would be lost after the page refresh.  