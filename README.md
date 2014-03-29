
UserFrosting 0.1.2
===================

### By Alex Weissman

Copyright (c) 2014

Welcome to UserFrosting, a secure, modern user management system for web services and applications.  UserFrosting is based on the popular UserCake system, written in PHP.  UserFrosting improves on this system by adding a sleek, intuitive frontend interface based on HTML5 and Twitter Bootstrap.  We've also separated the backend PHP machinery that interacts with the database from the frontend code base.  The frontend and backend talk to each other via AJAX and JSON.

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
