# UserFrosting v0.3.0

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

## Why UserFrosting?

This project grew out of a need for a simple user management system for my tutoring business, [Bloomington Tutors](https://bloomingtontutors.com).  I wanted something that I could develop rapidly and easily customize for the needs of my business.  Since my [prior web development experience](http://alexanderweissman.com/projects/) was in pure PHP, I decided to go with the PHP-based UserCake system.

Over time I modified and expanded the codebase, turning it into the UserFrosting project.  This latest version (0.3.0) represents a major break from the original architecture of UserCake.  We now use a fully object-oriented data model and a front controller for URL routing.
