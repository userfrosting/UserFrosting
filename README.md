# UserFrosting v0.3.1

http://www.userfrosting.com

If you simply want to show that you like this project, or want to remember it for later, you should **star**, not **fork**, this repository.  Forking is only for when you are ready to create your own copy of the code to work on.

[![Join the chat at https://gitter.im/alexweissman/UserFrosting](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/alexweissman/UserFrosting?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Click here to lend your support to: UserFrosting: A secure, modern user management system for PHP and make a donation at pledgie.com !](https://pledgie.com/campaigns/29583.png?skin_name=chrome)](https://pledgie.com/campaigns/29583)

## By [Alex Weissman](http://alexanderweissman.com)

Copyright (c) 2015, free to use in personal and commercial software as per the [license](licenses/UserFrosting.md).

UserFrosting is a secure, modern user management system written in PHP and built on top of the [Slim Microframework](http://www.slimframework.com/) and the [Twig](http://twig.sensiolabs.org/) templating engine.

## Installation

Please see our [installation guide](http://www.userfrosting.com/installation/).

## Troubleshooting

If you are having trouble installing UserFrosting, please read our [troubleshooting guide](http://www.userfrosting.com/troubleshooting) first!

If you are generally confused about the structure and layout of the code, or it doesn't look like the kind of PHP code that you're used to, please read [Navigating UserFrosting](http://www.userfrosting.com/navigating).  

If you want a good tour of the code base, we recommend going through our [tutorials](http://www.userfrosting.com/tutorials).

## Features

#### Login page
![Login page](/screenshots/login.png "Login page")
#### Dashboard (thanks to [Start Bootstrap](http://startbootstrap.com))
![Admin dashboard](/screenshots/dashboard.png "Admin dashboard")
#### User list page
![User list](/screenshots/users.png "User list page")
#### Create/update user dialog with validation
![Create/update user user](/screenshots/update_user.png "Create/update user dialog")
#### Group management
![Group management](/screenshots/groups.png "Group management page")
#### Site settings
![Site settings](/screenshots/site_settings.png "Site settings page")

## Mission Objectives

UserFrosting seeks to balance modern programming principles, like DRY and MVC, with a shallow learning curve for new developers.  Our goals are to:

- Create a fully-functioning user management script that can be set up in just a few minutes
- Make it easy for users to quickly adapt the code for their needs
- Introduce novice developers to best practices such as separation of concerns and DRY programming
- Introduce novice developers to modern constructs such as front-end controllers, RESTful URLs, namespacing, and object-oriented modeling
- Build on existing, widely used server- and client-side components
- Clean, consistent, and well-documented code

## What's new in 0.3.1

- Implement CSV download feature
- Improved initialization routine as middleware
- Implemented "remember me" for persistent sessions - see https://github.com/gbirke/rememberme
- Converted page templates to inheritance architecture, using Twig `extends`
- Start using the `.twig` extension for template files
- All content is now part of a theme, and site can be configured so that one theme is the default theme for unauthenticated users
- User session stored via `user_id`, rather than the entire User object
- UserFrosting now uses Laravel's [Eloquent](http://laravel.com/docs/5.1/eloquent#introduction) as the data layer
- Cleaned up some of the per-page Javascript, refactoring repetitive code
- Implement server-side pagination
- Upgrade to Tablesorter v2.23.4
- Switch from DateJS to momentjs
- Switch to jQueryValidation from FormValidation
- Implement `no_leading_whitespace` and `no_trailing_whitespace` validation rules
- Implement basic interface for modifying group authorization rules
- User events - timestamps for things like sign-in, sign-up, password reset, etc are now stored in a `user_event` table
- Wrapper class Notification for sending emails, other notifications to users
- Remove username requirement for password reset.  It is more likely that an attacker would know the user's username, than the user themselves.  For the next version, we can try to implement some real multi-factor authentication.
- When a user creates another user, they don't need to set a password.  Instead, an email is sent out to the new user, with a token allowing them to set their own password.
- Admins can manually generate a password reset request for another user, or directly change the user's password.
- .htaccess redirect trailing slash: change to only redirect GET requests
- Change "default theme" to "guest theme" and fix loading issues (#463).  What used to be called "default theme" is now base theme, i.e. the theme to fall back to when a template file cannot be found in the current theme (user group or guest theme)
- New public template for "nyx" theme
- Make routes for config.js and theme.css dynamically generated from configuration variables (#461)

### Migrating from UF's classic data model to Eloquent:

```
// Instead of...
$user = UserLoader::fetch(1);   // Fetch User with id=1

// You can do...
$user = User::find(1);


// Instead of...
$user = UserLoader::fetch("alex", "user_name");   // Fetch User with user_name = "alex"

// You can do...
$user = User::where("user_name", "alex")->first(); 

// Instead of...
UserLoader::exists("zergling@userfrosting.com", "email");

// You can do...
( User::where("email", "zergling@userfrosting.com")->first() ? true : false );

// Instead of...
$users = UserLoader::fetchAll();

// You can do...
$users = User::queryBuilder()->get();    // If you want an array of User objects (not indexed by id)
// or...
$users = User::all();                    // If you want an Eloquent Collection of User objects
// or...
$users = User::all()->getDictionary();   // If you want an array of User objects (indexed by id)
```

[Complete change log](CHANGELOG.md)


## Why UserFrosting?

This project grew out of a need for a simple user management system for my tutoring business, [Bloomington Tutors](https://bloomingtontutors.com).  I wanted something that I could develop rapidly and easily customize for the needs of my business.  Since my [prior web development experience](http://alexanderweissman.com/projects/) was in pure PHP, I decided to go with the PHP-based UserCake system.

Over time I modified and expanded the codebase, turning it into the UserFrosting project.  Starting with version 0.3.0, UserFrosting represents a major break from the original architecture of UserCake.  We now use a fully object-oriented data model and a front controller for URL routing.
