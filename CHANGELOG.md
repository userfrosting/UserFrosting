Change Log
==========

Change Log - v0.2.1 
-------------------

- Implemented db-driven menu system.  Menu items are pulled from the database, and can be modified via plugins.
- Implemented backend templating of forms and tables via [Bootsole](https://github.com/alexweissman/bootsole).

Change Log - v0.2.0 (butterflyknife)
-------------------

- Converted all DB calls to PDO.
- Renamed "permissions" to "groups".  Same concept, but using the word "group" suggests that it can be used for more than just access control.
- Implemented "primary group" membership for users.  A user can belong to multiple groups, but only one of those will be their primary group.
- Implemented DB-driven home pages for groups.  Upon login, a user will be redirected to the `home_page` for their primary group.
- Implemented templated menus.  Every group has a corresponding menu template in `models/menu-templates`.  Upon login, the menu for a user's primary group is automatically loaded and rendered.
- Implemented function-level user authorization.  Whenever a function in `secure_functions` is called, the `user_action_permits` table is checked to see whether or not that user has access to the function (the `action` column), conditional on the boolean functions specified in the `permits` column.
- Organized pages into four categories: account pages, API pages, form pages, and public pages.  Public pages reside in the root directory and can be accessed by anyone.  Account pages are in the `account` directory and are only accessible after logging in.  API pages are in the `api` directory, and consist of all the pages that process or fetch data from the DB and interact with the frontend via AJAX/JSON.  They are accessible by any logged in user, but will only perform a function if the user is authorized.  Form pages are in the `forms` directory, and consist of pages that generate forms (for creating/updating users, groups, etc.)  
- Converted registration page to AJAX.
- Improved installer with site configuration.

Change Log - v0.1.7
-------------------
- Page scrolls back to top after AJAX submit.
- "Website url" is automatically suffixed with "/" if necessary.
- Fixed bad link to forgot_password.php.
- Began implementing action authorization scheme.

Change Log - v0.1.6
-------------------
- Implemented CSRF token checking for creating and updating users
- Moved much of the nuts and bolts for generating the user-create and user-update forms to the server side, so as to streamline rendering process and require fewer requests by the client (see `load_form_user.php`)
- Improved responsive layout for rendering nicely on mobile devices

Change Log - v0.1.5 
-------------------
- More improvements to error-handling/rendering
- HTTPS/SSL compatible
- Fixed bug with different table name prefixes
- Improvements to CSRF tokens

Change Log - v0.1.4 
-------------------
- Updated password hashing from md5 to modern bcrypt (more secure) - thanks to contributor @r3wt
- Included better functions for sanitizing user input, validating user ip, generating csrf (cross-site request forgery) tokens - thanks to contributor @r3wt

Change Log - v0.1.3 
-------------------
- Root account (user id = 1) : created upon installation, cannot be deleted or disabled.  Special color scheme for when logged in as root user.
- Installer now guides user through creation of root account
- Moved common JS and CSS includes to "includes.php"

Change Log - v0.1.2 
-------------------
- Improved error and exception handling
- Added 404 error page
- Standardized JSON interface for backend scripts
- Front-end should now be able to catch virtually any backend error and take an appropriate action (instead of white screen of death)
