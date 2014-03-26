
UserFrosting 0.1
===================

### By Alex Weissman
Copyright (c) 2014
Based on the UserCake user management system, v2.0.2.  http://usercake.com
Copyright (c) 2009-2012

Welcome to UserFrosting!  The back end account management system is derived from [UserCake 2.0.2](http://usercake.com), while the dashboard and admin account features are based on the SB Admin Template by [Start Bootstrap](http://startbootstrap.com). Other key frameworks and plugins used in this system are:

*  jQuery 1.10.2
*  Twitter Bootstrap 3.0
*  [Font Awesome](http://fontawesome.io)
*  [Handlebars templating](http://handlebarsjs.com/)
*  [Tablesorter 2.0](http://tablesorter.com)
*  [DateJS](http://www.datejs.com)
*  [Bootstrap Switch](http://bootstrap-switch.org) component by Mattia Larentis,Peter Stein, and Emanuele Marchi

All components are copyright of their respective creators.
           
Screenshots
-----------------
#### Login page
![Login page](/screenshots/login.png "Login page")
#### Admin dashboard
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
