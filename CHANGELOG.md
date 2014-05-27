Change Log
==========

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
