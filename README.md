# UserFrosting v0.3.1 (development)

http://www.userfrosting.com

If you simply want to show that you like this project, or want to remember it for later, you should **star**, not **fork**, this repository.  Forking is only for when you are ready to create your own copy of the code to work on.

[![Join the chat at https://gitter.im/alexweissman/UserFrosting](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/alexweissman/UserFrosting?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Click here to lend your support to: UserFrosting: A secure, modern user management system for PHP and make a donation at pledgie.com !](https://pledgie.com/campaigns/29583.png?skin_name=chrome)](https://pledgie.com/campaigns/29583)

## By [Alex Weissman](http://alexanderweissman.com)

Copyright (c) 2015, free to use in personal and commercial software as per the [license](licenses/UserFrosting.md).

UserFrosting is a secure, modern user management system written in PHP and built on top of the [Slim Microframework](http://www.slimframework.com/) and the [Twig](http://twig.sensiolabs.org/) templating engine.

## Migrating from UF's classic data model to Eloquent:

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

## What's new in 0.3.0

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
- Data Model: [Eloquent](http://laravel.com/docs/5.1/eloquent#introduction)

## Why UserFrosting?

This project grew out of a need for a simple user management system for my tutoring business, [Bloomington Tutors](https://bloomingtontutors.com).  I wanted something that I could develop rapidly and easily customize for the needs of my business.  Since my [prior web development experience](http://alexanderweissman.com/projects/) was in pure PHP, I decided to go with the PHP-based UserCake system.

Over time I modified and expanded the codebase, turning it into the UserFrosting project.  This latest version (0.3.0) represents a major break from the original architecture of UserCake.  We now use a fully object-oriented data model and a front controller for URL routing.
