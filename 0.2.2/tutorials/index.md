---
layout: v022
title: "UserFrosting: Tutorials and Help"
---   

# Tutorials and how to get help with UserFrosting

## High-level overview of code structure

<img src="{{site.url}}/0.2.2/images/data-flow-overview.png">

## Adding a new user field

The most common question we get is "how do I add a new user field?"  We have created a [simple tutorial on our wiki]https://github.com/userfrosting/UserFrosting/wiki/0.2.2-only---How-to-add-a-new-user-field) to help you get started.  Please note that it is only a guide to help you get started, and only covers the very basics of modifying and extending UF.  To make more advanced changes, you must take the time to read through the code to understand the different components and how they interact.

Please feel free to ask specific questions on our [Issues page](https://github.com/userfrosting/UserFrosting/issues), **after** you have spent time trying to figure it out yourself and **after** you have already searched the existing issues.  Also, remember that courtesy and proper grammar go a long way.  Please take the time to craft a precise, polite question.  We will do our best to help, but remember that this is an open source project - none of us are getting paid to develop this project, or act as your personal support hotline ;-)

**If you post an issue on Github, please report any error messages you get.**  There are two main places where you may find error messages:

1. Backend (PHP-related) errors: in your PHP error log.  This is usually a file called `php_error_log` or something like that.  In `XAMPP`, the default location of this file is `XAMPP/xamppfiles/logs/`.  For other web hosting platforms, please consult the documentation or do a quick Google search (i.e. "where is the php error log in _____").  Some web hosts may provide a special interface for accessing the php error log, through `ssh`, cpanel, etc.  Please ask them directly for help with this.

2. Frontend (Javascript-related) errors: in your browser's Javascript console.  This can be accessed from "Tools->Web Developer" menu in Firefox, or "More tools->Javascript Console" in Chrome.  For help with other browsers, please Google "where is the javascript console in ____".

If you find a problem in any of the tutorials, let us know on the Issues page.  Also, please feel free to contribute to the wiki pages if you have something relevant and useful to add.

## Adding new navigation menu items

Please see the wiki page, https://github.com/userfrosting/UserFrosting/wiki/0.2.2-only---Adding-a-navigation-menu-item.

