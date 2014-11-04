
# UserFrosting 0.2.2
## http://www.userfrosting.com

### By Alex Weissman

Copyright (c) 2014

Welcome to UserFrosting, a secure, modern user management system for web services and applications.  UserFrosting is based on the popular UserCake system, written in PHP.  UserFrosting improves on this system by adding fine-grained authorization rules and a sleek, intuitive frontend interface based on HTML5 and Twitter Bootstrap.  We've also separated the backend PHP machinery that interacts with the database from the frontend code base.  The frontend and backend talk to each other via AJAX and JSON.
        
Screenshots
-----------------
#### Login page
![Login page](/screenshots/login.png "Login page")
#### Admin dashboard (thanks to [Start Bootstrap](http://startbootstrap.com))
![Admin dashboard](/screenshots/dashboard.png "Admin dashboard")
#### User list page
![User list](/screenshots/users.png "User list page")
#### Create/update user dialog with validation
![Create/update user user](/screenshots/update_user.png "Create/update user dialog")
#### User details
![User details](/screenshots/user_details.png "User details page")
#### Group management
![Group management](/screenshots/groups.png "Group management page")
#### Site settings
![Site settings](/screenshots/site_settings.png "Site settings page")
#### Action authorization
![Action authorization](/screenshots/action_auth.png "Action authorization")
#### Page authorization
![Page authorization](/screenshots/page_auth.png "Page authorization")

Why UserFrosting?
-----------------
This project grew out of a need for a simple user management system for my tutoring business, [Bloomington Tutors](http://bloomingtontutors.com).  I wanted something that I could develop rapidly and easily customize for the needs of my business.  Since my [prior web development experience](http://alexanderweissman.com/completed-projects/) was in pure PHP, I decided to go with the PHP-based UserCake system.  Over time I modified and expanded the codebase, turning it into the UserFrosting project. 

Why not use Node/Drupal/Django/RoR/(insert favorite framework here)?
--------------------------------------------------------------------
I chose PHP because PHP is what I know from my prior experience as a web developer. Additionally, PHP remains extremely popular and well-supported.  I chose not to use a framework because I wanted something that I could understand easily and develop rapidly from an existing PHP codebase.

Developer Features
------------------
- No need to learn a special framework!  The backend of UserFrosting is based on native PHP5, allowing for rapid development and deployment.
- Clean separation of backend and frontend code.  Easily interact with the backend via [AJAX calls](http://www.userfrosting.com/backend-api.html).
- Automated installation tool for initializing the database.
- Frontend built with jQuery and Twitter Bootstrap.  Javascript components for typical database CRUD operations provided with this distribution!

User Features
-------------
UserFrosting offers all of the features of UserCake, plus several new ones:

- Fine-grained, [rule-based authorization](http://www.userfrosting.com/features.html#authorization) for different users and groups.  Use our preloaded rules, or write your own and assign them to users and groups with our easy-to-use interface.
- Account creation/deletion from the admin interface
- Admin can disable/enable individual accounts
- Admin can disable/enable new account registration
- Admin can enable/disable logging in with email address
- Dropdown menus for easier account modifications
- Client-side [data validation](http://www.userfrosting.com/features.html#validation)
- Primary group for each user.  Primary group can be used to determine authorization, site rendering, custom menus, etc.
- Default groups for new accounts
- Table view for easily editing page authorization.
- New, more secure "lost password" feature.

Security Features
-----------------
UserFrosting is designed to address the most common security issues with websites that handle sensitive user data:

- SSL/HTTPS compatibility
- Strong password hashing
- Protection against cross-site request forgery (CSRF)
- Protection against cross-site scripting (XSS)
- Protection against SQL injection

See the [security](http://www.userfrosting.com/security.html) section of our website for more details.

Language Support
----------------
Database and data-handling functions are compliant with UTF8 character set.

#### Current languages offered:

- English
- Internationalized Spanish

Installation
--------------

UserFrosting comes with an easy-to-use installer.  Simply download the code to a directory on your server, and navigate to the <code>/install</code> subdirectory.  UserFrosting will guide you through setting up the database, configuring settings, and creating the master account.

Change Log - v0.2.2 
-------------------

- Implemented upgrade system, will pull new version list from github and automatically grab update files as well.
- Moved file list from config.php to the database to be easier to add and remove file paths
- Added version to the configuration table to aid in the upgrading of Userfrosting
- Added dev_env to config.php as well as new setting to db-setting.php when set to true UF will no longer check for the install or upgrade directory (good for development defaults to FALSE)
- Removal of models/captcha.php and replace with base64 captcha function.

[Older changes](CHANGELOG.md)   

Creds
-----

Thanks to user @r3wt for help with the CSRF and improved hashing, and @lilfade for significant contributions in getting `butterflyknife` ready and tested for release.

Thanks to @arochwer for translating system messages into internationalized Spanish!

The back end account management system is derived from [UserCake 2.0.2](http://usercake.com), while the dashboard and admin account features are based on the SB Admin Template by [Start Bootstrap](http://startbootstrap.com). Other key frameworks and plugins used in this system are:

*  jQuery 1.10.2
*  Twitter Bootstrap 3.0
*  [Font Awesome](http://fontawesome.io)
*  [Handlebars templating](http://handlebarsjs.com/)
*  [Tablesorter 2.17.7](http://tablesorter.com)
*  [Typeahead addon for Bootstrap](https://github.com/twitter/typeahead.js)
*  [DateJS](http://www.datejs.com)
*  [Bootstrap Switch](http://bootstrap-switch.org) 
*  [Bootsole PHP templating](https://github.com/alexweissman/bootsole)

All components are copyright of their respective creators.

Upcoming Features
-----------------

Please see the [wiki](https://github.com/alexweissman/UserFrosting/wiki/Upcoming-features) for a list of potential upcoming features.  If you would like to see a new feature implemented (or you would like to implement it!) please [open an issue](https://github.com/alexweissman/UserFrosting/issues?direction=desc&sort=updated&state=open).

