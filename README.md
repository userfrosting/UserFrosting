
UserFrosting 0.1.6
==================

### By Alex Weissman

Copyright (c) 2014

Welcome to UserFrosting, a secure, modern user management system for web services and applications.  UserFrosting is based on the popular UserCake system, written in PHP.  UserFrosting improves on this system by adding a sleek, intuitive frontend interface based on HTML5 and Twitter Bootstrap.  We've also separated the backend PHP machinery that interacts with the database from the frontend code base.  The frontend and backend talk to each other via AJAX and JSON.

Change Log - v0.1.6
---------------------
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

Why UserFrosting?
-----------------
This project grew out of a need for a simple user management system for my tutoring business, [Bloomington Tutors](http://bloomingtontutors.com).  I wanted something that I could develop rapidly and easily customize for the needs of my business.  Since my [prior web development experience](http://alexanderweissman.com/completed-projects/) was in pure PHP, I decided to go with the PHP-based UserCake system.  Over time I modified and expanded the codebase, turning it into the UserFrosting project. 

Why not use Node/Drupal/Django/RoR/(insert favorite framework here)?
--------------------------------------------------------------------
I chose PHP because PHP is what I know from my prior experience as a web developer. Additionally, PHP remains extremely popular and well-supported.  I chose not to use a framework because I wanted something that I could understand easily and develop rapidly from an existing PHP codebase.

Developer Features
-----------------
- No need to learn a special framework!  The backend of UserFrosting is based on native PHP5, allowing for rapid development and deployment.
- Clean separation of backend and frontend code.  Easily interact with the backend via AJAX calls.
- Automated installation tool for initializing the database.
- Frontend built with jQuery and Twitter Bootstrap.  Javascript components for typical database CRUD operations provided with this distribution!

User Features
--------
UserFrosting offers all of the features of UserCake, plus several new ones:

- Account creation/deletion from the admin interface
- Admin can disable/enable individual accounts
- Admin can disable/enable new account registration
- Dropdown menus for easier account modifications
- Client-side data validation
- Default permissions for new accounts
- Table view for easily editing page permissions

Security Features
-----------------
UserFrosting is designed to address the most common security issues with websites that handle sensitive user data:

#### SSL/HTTPS compatibility
Unsecured ("http") websites exchange data between the user and the server in plain text.  If the connection between the user and server is not secure, this data can be intercepted, and possibly even altered and/or rerouted.  And, even if the sensitive data itself is encrypted, the user's session on the website can be stolen and impersonated unless ALL communication between the user and server is handled over SSL ("https" websites).  If you walk into any coffee shop with an unsecured wireless network, and launch a simple program such as [Firesheep](http://codebutler.com/firesheep/), you will see how huge of a problem this is, and why [Google and other companies are pushing for _everyone_ to use SSL](http://www.wired.com/2014/04/https/).

This is also why there are strict standards about websites that handle sensitive user data such as credit card numbers!  We strongly encourage anyone planning to deploy a website that handles user passwords and sessions (such as ones based on UserFrosting) to purchase an SSL certificate and deploy it on their web server.  [Namecheap](https://www.namecheap.com/support/knowledgebase/article.aspx/794/67/how-to-activate-ssl-certificate) offers basic, inexpensive certs for $9/year (you do not need to have Namecheap hosting or domain registration to use their certificates on your site).  If your web hosting happens to use cPanel, this is easy to [set up yourself](http://docs.cpanel.net/twiki/bin/view/AllDocumentation/WHMDocs/InstallCert) without needing to contact your hosting provider.  Please note that SSL on shared hosting accounts may create false security warnings for end-users with [older browsers](https://en.wikipedia.org/wiki/Server_Name_Indication#No_support).

For __local testing purposes only__ you may create a self-signed certificate.  For instructions on how to do this for XAMPP/Apache in OSX, see [this blog post](http://shahpunyerblog.blogspot.com/2007/10/create-self-signed-ssl-certificate-in.html).

#### Strong password hashing
UserFrosting uses the `password_hash` and `password_verify` functions to hash and validate passwords (new in PHP v5.5.0).  `password_hash` uses the [bcrypt](https://en.wikipedia.org/wiki/Bcrypt) algorithm, based on the Blowfish cipher.  This is stronger than SHA1 (used by UserCake), which has been demonstrated vulnerable to attack.  UserFrosting also appends a 22-character salt to user passwords, protecting against dictionary attacks.

UserFrosting provides backwards compatibility for existing UserCake user databases that have passwords hashed with MD5.  User accounts that have been hashed with MD5 will automatically be updated to the new encryption standard when the user successfully logs in.

#### Protection against cross-site request forgery (CSRF)
CSRF is an attack that relies on a user unwittingly submitting malicious data from another source while logged in to their account.  The malicious data can be embedded in an image, link, or other javascript content, on another website or in an email.  Because the user has a valid session with a website, the external content is accepted and processed.  Thus, attackers can easily change passwords or delete a user's account with this attack.

To guard against this, UserFrosting provides the `csrf_token` function (courtesy of @r3wt).  By generating a new, random CSRF token for users when they log in, inserting it into legitimate forms as a hidden field, and then having the backend form processing links check for this token before taking any action, CSRF attacks can be thwarted.

#### Protection against cross-site scripting (XSS)
XSS is another variety of attack that tricks a user, but instead of tricking the user into submitting malicious data (CSRF), it tricks the user into running malicious scripts.  This vulnerability usually appears when you allow arbitrary content (including javascript and HTML tags) to be processed and then regurgitated back to other users.  Thus, an attacker on a forum could create a new "post" that contains javascript commands.  When anyone else on the site goes to view that post, the javascript commands are executed.  Those commands could easily be instructions to transmit the user's session data to a remote server, where attackers can use it to impersonate the user.

UserFrosting guards against this by sanitizing user input before storing or otherwise acting upon it.  Please let us know if you find a place where input is not properly sanitized.

#### Protection against SQL injection
Whereas XSS tricks the _user_ into executing malicious code, SQL injection tricks the _server_ into executing malicious code; in this case, SQL statements.  Thus, sites vulnerable to SQL injection can end up executing code that, for example, deletes a table or database.

UserFrosting protects against this by using parameterized queries, which do not allow user-supplied data to be executed as code.  However there are always exceptions, and we would be glad to have some contributors test and/or help patch any possible remaining SQL injection vulnerabilities.
        
Screenshots
-----------------
#### Login page
![Login page](/screenshots/login.png "Login page")
#### Admin dashboard (thanks to [Start Bootstrap](http://startbootstrap.com))
![Admin dashboard](/screenshots/dashboard.png "Admin dashboard")
#### User list page
![User list](/screenshots/users.png "User list page")
#### Create user dialog
![Create user](/screenshots/create_user.png "Create user dialog")
#### User details
![User details](/screenshots/user_details.png "User details page")
#### Site settings
![Site settings](/screenshots/site_settings.png "Site settings page")
#### Page management
![Site pages](/screenshots/site_pages.png "Page management")

Installation
--------------
To install, follow these instructions:

1. Create a database on your server / web hosting package. UserFrosting supports MySQLi and requires MySQL server version 4.1.3 or newer, as well as PHP 5.4 or later with PDO database connections enabled.
2. Open up models/db-settings.php and fill out the connection details for your database.
3. Run the UserFrosting installer at install/install.php. UserFrosting will attempt to build the database for you.
4. After the installer successfully completes, delete the install folder.
5. Register your admin account, which will by default be the first account that is registered.

Documentation
-------------

### Backend pages
These are pages which accept a POST or GET request, perform some action, and then return a response in the form of a JSON object.  Success, error, and warning messages are stored in the `$_SESSION['userAlerts']` object, and can be fetched via **user_alerts.php**.

- **activate_account.php**
  + Activates a newly registered user account, using the activation token that was emailed to the user upon account registration.
  + Default permission level: *public*
  + Request method: *POST*
  + Request parameters:
    * `token` : a user's account activation token 
    * `ajaxMode` : Specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`. 
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `FORGOTPASS_INVALID_TOKEN` : Token not specified
    * `ACCOUNT_TOKEN_NOT_FOUND` : Invalid token
    * `SQL_ERROR` : Database error
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * `ACCOUNT_ACTIVATION_COMPLETE` : Account successfully activated.


- **admin_activate_user.php**
  + Manually activates a newly registered user account, using the user's user id (`id` column in `uc_users`).
  + Default permission level: *admin only*
  + Request method: *POST*
  + Request parameters:
    * `user_id` : a user's user id.
    * `ajaxMode` : Specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`. 
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `ACCOUNT_INVALID_USER_ID` : Invalid user id specified
    * `SQL_ERROR` : Database error
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * `ACCOUNT_MANUALLY_ACTIVATED` : Manual account activation successful.


- **admin_load_permissions.php**
  + Load permissions for a specified user, using the user's user id (`id` column in `uc_users`).
  + Default permission level: *admin only*
  + Request method: *GET*
  + Request parameters:
    * `user_id` : a user's user id.
  + Response on failure (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.  Obviously set to 0.
  + Response on success (JSON):
    * A JSON object with permission id's (from `uc_permissions`) as keys, mapping to permission objects for each permission group that the user has access to.  Each permission object contains the following fields:
        - `id` : the unique id for this permission group.
        - `name` : the name of the permission group.
        - `is_default` : specify whether the permission group is automatically added when a new account is registered. (`"1"` or `"0"`).
        - `can_delete` : specify whether the permission group can be deleted via "delete_permission.php". (`"1"` or `"0"`).
        - `user_id` : the id of the queried user.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * User id not specified
    * Database error


- **create_permission.php**
  + Create a new permission group.
  + Default permission level: *admin only*
  + Request method: *POST*
  + Request parameters:
    * `new_permission` : the name of the new permission group.  Must be between 1 and 50 characters long.
    * `ajaxMode` : specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`. 
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `PERMISSION_NAME_IN_USE` : Permission group name already in use.
    * `PERMISSION_CHAR_LIMIT` : Permission group name too short/long.
    * `SQL_ERROR` : Database error
    * `NO_DATA` : No request data was received by the script.
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * `PERMISSION_CREATION_SUCCESSFUL` : Permission group successfully created.


- **create_user.php**
  + Create a new user.
  + Default permission level: *admin only*
  + Request method: *POST*
  + Request parameters:
    * `username` : the username for the new user.  Must be between 1 and 25 characters long, letters and numbers only (no whitespace).
    * `displayname` : the display name for the new user.  Must be between 1 and 50 characters long.
    * `title` : the title for the new user.  Must be between 1 and 150 characters long.
    * `email` : the email address for the new user.  Must be between 1 and 150 characters and a valid email format.
    * `password` : the password for the new user.  Must be between 8 and 50 characters.
    * `confirm_pass` : the password for the new user.  Must match the `password` field.
    * `add_permissions` : a comma-separated string of permission id's to be associated with the new user.
    * `ajaxMode` : specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`.
    * `csrf_token` : the csrf token for the user's session.  Must be set, or the request will automatically fail.    
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `ACCOUNT_USER_CHAR_LIMIT` : `username` too short/long.
    * `ACCOUNT_USER_INVALID_CHARACTERS` : `username` must be letters and numbers only.
    * `ACCOUNT_DISPLAY_CHAR_LIMIT` : `displayname` too short/long.
    * `ACCOUNT_PASS_CHAR_LIMIT` : `password` too short/long.
    * `ACCOUNT_PASS_MISMATCH` : `password` and `confirm_pass` do not match.
    * `ACCOUNT_INVALID_EMAIL` : `email` is not a valid email address format.
    * `ACCOUNT_USERNAME_IN_USE` : `username` already in use.
    * `ACCOUNT_DISPLAYNAME_IN_USE` : `displayname` already in use.  TODO: relax this restriction?
    * `ACCOUNT_EMAIL_IN_USE` : `email` already in use.
    * `MAIL_ERROR` : Error sending activation email (if enabled).
    * `SQL_ERROR` : Database error
    * `NO_DATA` : No request data was received by the script.
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * `ACCOUNT_CREATION_COMPLETE` : User account successfully created.
    * `ACCOUNT_PERMISSION_ADDED` : Permissions successfully added for new user.


- **delete_permission.php**
  + Delete a permission group, and all it's page and user associations.
  + Default permission level: *admin only*
  + Request method: *POST*
  + Request parameters:
    * `permission_id` : the id of the permission group to delete.
    * `ajaxMode` : specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`. 
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `CANNOT_DELETE_PERMISSION_GROUP` : The specified group is undeletable (`can_delete`=0)
    * `NO_DATA` : No request data was received by the script.
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * `PERMISSION_DELETION_SUCCESSFUL_NAME` : Permission group successfully deleted.


- **delete_user.php**
  + Delete a user account, and all their permission associations.
  + Default permission level: *admin only*
  + Request method: *POST*
  + Request parameters:
    * `user_id` : the id of the user to delete.
    * `ajaxMode` : specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`. 
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `SQL_ERROR` : Database error.
    * `ACCOUNT_DELETE_MASTER` : The master (id=1) account cannot be deleted.
    * `NO_DATA` : No request data was received by the script.
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * `ACCOUNT_DELETIONS_SUCCESSFUL` : User account successfully deleted.


- **load_current_user.php**
  + Loads data for the currently logged in user.
  + Default permission level: *public*
  + Request method: *GET*
  + Request parameters: *none*
  + Response on failure (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.  Set to 0.
  + Response on success (JSON):
    * A JSON object containing relevant data for the user's account:
        * `id` : the id of the currently logged in user.
        * `username` : the username of the currently logged in user.
        * `displayname` : the display name of the currently logged in user.
        * `title` : the title of the currently logged in user.
        * `email` : the email address of the currently logged in user.
        * `sign_up_stamp` : the UNIX timestamp (seconds since Jan 1, 1970) of the time when the currently logged in user's account was created.
        * `admin` : specify whether or not this user is an admin.  Useful for frontend rendering purposes.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied.
    * User not logged in.
    * Account data not found.
    * `SQL_ERROR` : Database error.

    
- **load_form_user.php**
  + Loads a customized form/panel for creating, updating, or displaying a user.
  + Default permission level: *admin only*
  + Request method: *GET*
  + Request parameters:
    * `box_id`: the desired name of the div that will contain the form.
    * `render_mode`: `modal` or `panel`
    * `user_id` (optional): if specified, will load the relevant data for the user into the form.  Form will then be in "update" mode.
    * `show_dates` (optional): if set to `"true"`, will show the registered and last signed in date fields (fields will be read-only)
    * `show_passwords` (optional): if set to `"true"`, will show the password creation fields
    * `disabled` (optional): if set to `"true"`, disable all fields
    * `button_submit`: If set to `"true"`, display the submission button for this form.
    * `button_edit`: If set to `"true"`, display the edit button for panel mode.
    * `button_disable`: If set to `"true"`, display the enable/disable button.
    * `button_activate`: If set to `"true"`, display the activate button for inactive users.
    * `button_delete`: If set to `"true"`, display the deletion button for deletable users.
  + Response on failure (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.  Set to 0.
  + Response on success (JSON):
    * A JSON object containing the requested form/panel:
        * `data` : the fully rendered HTML for the form/panel.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied.
    

- **load_permissions.php**
  + Returns a list of data for all permission levels.
  + Default permission level: *admin only*
  + Request method: *GET*
  + Request parameters:
    * `limit` : (optional).  Limit how many results to return.
  + Response on failure (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.  Set to 0.
  + Response on success (JSON):
    * A JSON object with permission id's (from `uc_permissions`) as keys, mapping to permission objects for each permission group.  Each permission object contains the following fields:
        - `id` : the unique id for this permission group.
        - `name` : the name of the permission group.
        - `is_default` : specify whether the permission group is automatically added when a new account is registered. (`"1"` or `"0"`).
        - `can_delete` : specify whether the permission group can be deleted via "delete_permission.php". (`"1"` or `"0"`).
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied.
    * Database error.


- **load_site_pages.php**
  + Scans the root directory for new and missing PHP pages, and updates the database accordingly (new pages are made public by default).  Then, returns a list of data objects corresponding to website pages.
  + Default permission level: *admin only*
  + Request method: *GET*
  + Request parameters: *none*
  + Response on failure (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.  Set to 0.
  + Response on success (JSON):
    * A JSON object with file names as keys, mapping to page objects for each page.  Each page object contains the following fields:
        - `id` : the unique id for this page.
        - `page` : the file name of the page, relative to the root directory.
        - `private` : specify whether the page is public or private (logged in users only). (`"1"` or `"0"`).
        - `status` : returns the status of the page after the most recent call to this function, indicating whether it was created, deleted, or updated. (`"C"`, `"D"`, or `"U"`).
        - `permissions` : A list of permissions associated with this page, as per load_permissions.php.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied.
    * Database error.
    

- **load_site_settings.php**
  + Returns a list of site configuration parameters, indexed by the parameter name.
  + Default permission level: *admin only*
  + Request method: *GET*
  + Request parameters: *none*
  + Response on failure (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.  Set to 0.
  + Response on success (JSON):
    * A JSON object with parameter names as keys, mapping to their values.  The default parameters for UserFrosting are as follows:
        - `website_name` : the name of your site.
        - `website_url` : the root url for your UserFrosting installation.
        - `email` : the email address to use for sending administrative emails.
        - `new_user_title` : The default title for newly registered users.
        - `can_register` : `"1"` if account self-registration is enabled (from "register.php"), `"0"` otherwise.
        - `activation` : `"1"` if email activation for new accounts is enabled, `"0"` otherwise.
        - `resend_activation_threshold` : number of hours required between consecutive requests from a user to resend their account activation link.  Default is 0.
        - `language` : The language template to use for generating site messages, etc.  Default: english.
        - `template` : The default site CSS template.  TODO: expand to include Bootstrap templates.
        - `language_options` : A list of language template options, based on scanning the `models/languages` folder.
        - `template_options` : A list of CSS template options, based on scanning the `models/site-templates` folder.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied.
    * Database error.

        
- **load_user.php**
  + Load data for a specified user, using the user's user id (`id` column in `uc_users`).
  + Default permission level: *admin only*
  + Request method: *GET*
  + Request parameters:
    * `user_id` : a user's user id.
  + Response on failure (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.  Obviously set to 0.
  + Response on success (JSON):
    * A JSON object containing the user's data.  Contains the following fields:
        - `user_id` : the unique id of the specified user.
        - `user_name` : the username of the specified user.
        - `display_name` : the display name of the specified user.
        - `title` : the title of the specified user.
        - `email` : the email address of the specified user.
        - `sign_up_stamp` : the UNIX timestamp (seconds since Jan 1, 1970) of the time when the specified user's account was created.
        - `last_sign_in_stamp` : the UNIX timestamp (seconds since Jan 1, 1970) of the time when the user last logged in.
        - `active` : `"1"` if the user's account has been activated, `"0"` otherwise.
        - `enabled` : `"1"` if the user's account is enabled, `"0"` otherwise.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * Invalid user id specified
    * Database error
    

- **load_users.php**
  + Load data objects for all users.
  + Default permission level: *admin only*
  + Request method: *GET*
  + Request parameters:
    * `limit` : (optional).  Limit how many results to return.
  + Response on failure (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.  Obviously set to 0.
  + Response on success (JSON):
    * A JSON object with user id's (from `uc_users`) as keys, mapping to user objects.  Each user object contains the same fields as specified in "load_user.php".
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * Database error
    

- **process_login.php**
  + Processes a user login request, and creates a session for the user upon success.
  + Default permission level: *public*
  + Request method: *POST*
  + Request parameters:
    * `username` : the username for the login request.
    * `password` : the password for the login request.
    * `ajaxMode` : specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`. 
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `ACCOUNT_SPECIFY_USERNAME` : username not specified.
    * `ACCOUNT_SPECIFY_PASSWORD` : password not specified.
    * `ACCOUNT_USER_OR_PASS_INVALID` : either the username could not be found in the database, or the password was not correct.
    * `ACCOUNT_INACTIVE` : the account has not yet been activated (when email activation is enabled).
    * `ACCOUNT_DISABLED` : the account has been disabled by an administrator.
    * `NO_DATA` : No request data was received by the script.
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * Login successful (welcome message).

    
- **update_page_permission.php**
  + Update permissions for a site page, and private/public status.  TODO: implement "public" pages as just another permission group.
  + Default permission level: *admin only*
  + Request method: *POST*
  + Request parameters:
    * `page_id` : the id of the page to be updated (from `uc_pages`)
    * `permission_id` : the id of the permission group to be associated with this page (from `uc_permissions`)
    * `checked` : `"1"` if the page is to be set as a private page, `"0"` for a public page.
    * `ajaxMode` : specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`. 
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * *none*


- **update_permission.php**
  + Update settings for a permission group.  Not yet implemented.
  + Default permission level: *admin only*
  + Request method: *POST*
  + TODO: Implement this properly.
  

- **update_site_settings.php**
  + Update site configuration settings.
  + Default permission level: *admin only*
  + Request method: *POST*
  + Request parameters:
    * `website_name` : the name of your site.  Must be between 1 and 150 characters.
    * `website_url` : the root url for your UserFrosting installation.  Must be between 1 and 150 characters and end with a '/'.
    * `email` : the email address to use for sending administrative emails.  Must be between 1 and 150 characters and a valid email format.
    * `can_register` : `"1"` if account self-registration is enabled (from "register.php"), `"0"` otherwise.
    * `new_user_title` : the default title for new users.  Must be between 1 and 150 characters.
    * `activation` : `"1"` if email activation for new accounts is enabled, `"0"` otherwise.
    * `resend_activation_threshold` : number of hours required between consecutive requests from a user to resend their account activation link.  Default is 0.
    * `language` : The language template to use for generating site messages, etc.  Default: english.
    * `template` : The default site CSS template.  TODO: expand to include Bootstrap templates.
    * `ajaxMode` : specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`. 
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `CONFIG_NAME_CHAR_LIMIT` : Site name must be between 1 and 150 characters.
    * `CONFIG_URL_CHAR_LIMIT` : Site url must be between 1 and 150 characters.
    * `CONFIG_INVALID_URL_END` : Site url must end with '/'.
    * `CONFIG_EMAIL_CHAR_LIMIT` : Email must be between 1 and 150 characters.
    * `CONFIG_TITLE_CHAR_LIMIT` : Default new user title must be between 1 and 150 characters.
    * `CONFIG_EMAIL_INVALID` : Email must a valid format.
    * `CONFIG_REGISTRATION_TRUE_FALSE` : Registration must have either the value `"true"` or `"false"`. 
    * `CONFIG_ACTIVATION_TRUE_FALSE`: Registration must have either the value `"true"` or `"false"`. 
    * `CONFIG_ACTIVATION_RESEND_RANGE` : Email activation resend wait period must be between 0 and 72 hours.
    * `CONFIG_LANGUAGE_CHAR_LIMIT`: Language template filename must be between 1 and 150 characters.
    * `CONFIG_LANGUAGE_INVALID` : The language template specified could not be found.
    * `CONFIG_TEMPLATE_CHAR_LIMIT` : Site template filename must be between 1 and 150 characters.
    * `CONFIG_TEMPLATE_INVALID` : The site template specified could not be found.
    * `NO_DATA` : No request data was received by the script.
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * `CONFIG_UPDATE_SUCCESSFUL` : Site configuration was successfully updated.

    
- **update_user.php**
  + Update an existing user's account.
  + Default permission level: *admin only*
  + Request method: *POST*
  + Request parameters (all except `user_id` are optional):
    * `user_id` : the id of the user (in `uc_users`).
    * `display_name` : the new display name for the user.  Must be between 1 and 50 characters long.
    * `title` : the new title for the user.  Must be between 1 and 150 characters long.
    * `email` : the new email address for the user.  Must be between 1 and 150 characters and a valid email format.
    * `add_permissions` : a comma-separated string of permission id's to be associated with the user.
    * `remove_permissions` : a comma-separated string of permission id's to be removed from the user.
    * `ajaxMode` : specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`.
    * `csrf_token` : the csrf token for the user's session.  Must be set, or the request will automatically fail.
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `ACCOUNT_USER_CHAR_LIMIT` : `username` too short/long.
    * `ACCOUNT_USER_INVALID_CHARACTERS` : `username` must be letters and numbers only.
    * `ACCOUNT_DISPLAY_CHAR_LIMIT` : `displayname` too short/long.
    * `ACCOUNT_INVALID_EMAIL` : `email` is not a valid email address format.
    * `ACCOUNT_USERNAME_IN_USE` : `username` already in use.
    * `ACCOUNT_DISPLAYNAME_IN_USE` : `displayname` already in use.  TODO: relax this restriction?
    * `ACCOUNT_TITLE_CHAR_LIMIT` : `title` too short/long.
    * `ACCOUNT_EMAIL_IN_USE` : `email` already in use.
    * `SQL_ERROR` : Database error
    * `NO_DATA` : No request data was received by the script.
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * `ACCOUNT_DISPLAYNAME_UPDATED` : User display name successfully updated.
    * `ACCOUNT_EMAIL_UPDATED` : User email successfully updated.
    * `ACCOUNT_TITLE_UPDATED` : User title successfully updated.
    * `ACCOUNT_PERMISSION_ADDED` : Permissions successfully added for the user.
    * `ACCOUNT_PERMISSION_REMOVED` : Permissions successfully removed for the user.
    

- **update_user_enabled.php**
  + Enable or disable a user's account.
  + Default permission level: *admin only*
  + Request method: *POST*
  + Request parameters:
    * `user_id` : the id of the user (in `uc_users`).
    * `enabled` : `"1"` to enable the account, `"0"` to disable the account.
    * `ajaxMode` : specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`. 
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `SQL_ERROR` : Database error
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * Account successfully enabled.
    * Account successfully disabled.


- **user_create_user.php**
  + Allows a newly registered user to create their account.  Sets their title automatically to the `new_user_title` specified in site settings.  Adds all default permission levels (`is_default` set to `1` in `uc_permissions`) to the newly registered account.
  + Default permission level: *public*
  + Request method: *POST*
  + Request parameters:
    * `username` : the username for the new user.  Must be between 1 and 25 characters long, letters and numbers only (no whitespace).
    * `displayname` : the display name for the new user.  Must be between 1 and 50 characters long.
    * `email` : the email address for the new user.  Must be between 1 and 150 characters and a valid email format.
    * `password` : the password for the new user.  Must be between 8 and 50 characters.
    * `confirm_pass` : the password for the new user.  Must match the `password` field.
    * `captcha` : the captcha code displayed on the registration form.
    * `ajaxMode` : specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`. 
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `ACCOUNT_USER_CHAR_LIMIT` : `username` too short/long.
    * `ACCOUNT_USER_INVALID_CHARACTERS` : `username` must be letters and numbers only.
    * `ACCOUNT_DISPLAY_CHAR_LIMIT` : `displayname` too short/long.
    * `ACCOUNT_PASS_CHAR_LIMIT` : `password` too short/long.
    * `ACCOUNT_PASS_MISMATCH` : `password` and `confirm_pass` do not match.
    * `ACCOUNT_INVALID_EMAIL` : `email` is not a valid email address format.
    * `ACCOUNT_USERNAME_IN_USE` : `username` already in use.
    * `ACCOUNT_DISPLAYNAME_IN_USE` : `displayname` already in use.  TODO: relax this restriction?
    * `ACCOUNT_EMAIL_IN_USE` : `email` already in use.
    * `CAPTCHA_FAIL` : captcha did not match.
    * `MAIL_ERROR` : Error sending activation email (if enabled).
    * `SQL_ERROR` : Database error
    * `NO_DATA` : No request data was received by the script.
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * `ACCOUNT_CREATION_COMPLETE` : User account successfully created.
    

- **user_load_permissions.php**
  + Load permissions for the currently logged in user.
  + Default permission level: *logged in users only*
  + Request method: *GET*
  + Request parameters: *none*
  + Response on failure (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.  Obviously set to 0.
  + Response on success (JSON):
    * A JSON object with permission id's (from `uc_permissions`) as keys, mapping to permission objects for each permission group that the user has access to.  Each permission object contains the following fields:
        - `id` : the unique id for this permission group.
        - `name` : the name of the permission group.
        - `is_default` : specify whether the permission group is automatically added when a new account is registered. (`"1"` or `"0"`).
        - `can_delete` : specify whether the permission group can be deleted via "delete_permission.php". (`"1"` or `"0"`).
        - `user_id` : the id of the queried user.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * User is not logged in
    * Database error
    

- **user_resend_activation.php**
  + Resend the activation email for a newly registered account.  User must supply the username and email address used to create the account.
  + Default permission level: *public*
  + Request method: *POST*
  + Request parameters:
    * `username` : the username for the new user account.
    * `email` : the email address for the new user account.
    * `ajaxMode` : specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`. 
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `ACCOUNT_SPECIFY_EMAIL` : no email address specified.
    * `ACCOUNT_INVALID_EMAIL` : email address is not a valid format, or no account with the specified email address was found.
    * `ACCOUNT_SPECIFY_USERNAME` : no username specified.
    * `ACCOUNT_INVALID_USERNAME` : no account with the specified username was found.
    * `ACCOUNT_USER_OR_EMAIL_INVALID` : username and email address are not linked to the same account.
    * `ACCOUNT_ALREADY_ACTIVE` : account has already been activated.
    * `ACCOUNT_LINK_ALREADY_SENT` : activation link cannot be resent until the activation threshold expires.
    * `MAIL_TEMPLATE_BUILD_ERROR` : could not build the mail template.
    * `MAIL_ERROR` : Error sending activation email (if enabled).
    * `SQL_ERROR` : Database error
    * `NO_DATA` : No request data was received by the script.
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * `ACCOUNT_NEW_ACTIVATION_SENT` : Activation email successfully resent.
    

- **user_reset_password.php**
  + Process various types of password reset requests.
  + Default permission level: *public*
  + Request method: *POST* or *GET*
  + TODO: separate this into two pages, one that issues password resets and one that processes confirmation/denial.
  

- **user_update_account_settings.php**
  + Allows a user to update their email and/or password. 
  + Default permission level: *logged in users only*
  + Request method: *POST*
  + Request parameters:
    * `email` : the new email address for the user.  Must be between 1 and 150 characters and a valid email format.
    * `password` : the current password for the user.
    * `passwordc` : the new password for the user.  Must be between 8 and 50 characters.
    * `passwordcheck` : the new password for the user.  Must match the `passwordc` field.
    * `ajaxMode` : specify whether to access in AJAX/JSON mode.  `"true"` or `"false"`. 
  + Response (JSON):
    * `errors` : the number of errors generated by the request.
    * `successes` : the number of successes generated by the request.
  + Possible errors generated (stored in `$_SESSION['userAlerts']`):
    * Page access denied
    * `ACCOUNT_SPECIFY_PASSWORD` : current password not specified.
    * `ACCOUNT_PASSWORD_INVALID` : current password does not match.
    * `ACCOUNT_SPECIFY_EMAIL` : no email address specified.
    * `ACCOUNT_INVALID_EMAIL` : `email` is not a valid email address format.
    * `ACCOUNT_EMAIL_IN_USE` : `email` already in use.
    * `ACCOUNT_SPECIFY_NEW_PASSWORD` : new password not specified.
    * `ACCOUNT_SPECIFY_CONFIRM_PASSWORD` : confirm new password not specified.
    * `ACCOUNT_NEW_PASSWORD_LENGTH` : new password must be between 8 and 50 characters.
    * `ACCOUNT_PASS_MISMATCH` : `passwordc` and `passwordcheck` do not match.
    * `ACCOUNT_PASSWORD_NOTHING_TO_UPDATE` : new password is the same as the old password.
    * `NOTHING_TO_UPDATE` : No data was specified to update.
    * `NO_DATA` : No request data was received by the script.
  + Possible successes generated (stored in `$_SESSION['userAlerts']`): 
    * `ACCOUNT_EMAIL_UPDATED` : User email successfully updated.
    * `ACCOUNT_PASSWORD_UPDATED` : User password successfully updated.
    
  
TODO Tasks
----------

These are features to be added in future releases.

1. Deploy CSRF tokens on all forms and add "bulletproof sessions" as per http://blog.teamtreehouse.com/how-to-create-bulletproof-sessions.
2. Add **OAuth** support, for users to create accounts and log in via Facebook/Google.
3. Associate permission groups with allowed actions, rather than individual pages.  Actions, in turn, are linked to pages (on the backend) and features (on the frontend).  Automatically hide features for which a user does not have permission.
4. Convert to standard REST architecture, implementing updates as PUT and deletes as DELETE.  This could mean that different backend pages that act on the same type of object (users, pages, etc) could be combined into a single page that takes different actions depending on the request method.
5. Add ability for admins to modify permission group names/settings.
6. Filter user list by name, group membership, etc.
7. Full directory/subdirectory management tool for pages.
8. Add ability for admins to add/remove user account fields, without having to modify code.
9. Deploy the `jqueryvalidator` plugin for client-side validation (as opposed to our own, clunkier validator).
10. Admin-side user account creation should bypass activation process.
11. Continue improving error-handling and rendering system.
12. Reduce number of requests necessary to construct forms.

Possible additional features as suggested on UserCake forums:

1. "Remember me" feature
2. Admin control over session timeout
3. Auto-redirect to last visited page on login
4. Admin can allow users to log in via email address instead of username

Creds
-----

The back end account management system is derived from [UserCake 2.0.2](http://usercake.com), while the dashboard and admin account features are based on the SB Admin Template by [Start Bootstrap](http://startbootstrap.com). Other key frameworks and plugins used in this system are:

*  jQuery 1.10.2
*  Twitter Bootstrap 3.0
*  [Font Awesome](http://fontawesome.io)
*  [Handlebars templating](http://handlebarsjs.com/)
*  [Tablesorter 2.0](http://tablesorter.com)
*  [DateJS](http://www.datejs.com)
*  [Bootstrap Switch](http://bootstrap-switch.org) 

All components are copyright of their respective creators.
