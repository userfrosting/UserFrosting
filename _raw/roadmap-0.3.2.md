# Roadmap for UserFrosting 0.3.2

UserFrosting has come a long way from its procedural, chaotic origins.  We've outsourced much of the common functionality to third-party components, freeing us to work on new features and provide support in chat.  However, we still have a long way to go.

This roadmap outlines our goals and proposed implementation strategies for UF 0.3.2 and beyond.  These are not set in stone, and are subject to change as our plan evolves.

## What is UserFrosting?

There has been some confusion over what exactly UserFrosting is.  Generally, this comes up because people ask for some feature, like "plugins", and then we have a long discussion about whether or not that makes sense.  Rather than try to define what UserFrosting *is*, perhaps we can define what UserFrosting *isn't*:

### UserFrosting is not a Content Management System (CMS).

A CMS is a piece of software that allows one to manage the content of a website, and some aspects of its behavior and presentation, without generally touching the code.  

Additional functionality is typically achieved by authoring a *plugin*, which extends without directly modifying the core codebase.  Yes, I realize that there are Wordpress core hackers, but most users of a CMS will probably never even write a plugin, let alone hack the core.  A CMS is, *by design*, targeted towards non-developers.

UserFrosting by contast, is definitely targeted towards developers.  The range of applications for UserFrosting is qualitatively different; whereas CMS's are primarily concerned with blocks of text, images, and pages (with user accounts perhaps as an added feature), UF is meant for developing arbitrarily complex web applications.

**TL;DR: A CMS is for building a blog, while UserFrosting is for building a timesheet system.   Or a [safety message notification system](http://safetymessaging.net/).  Or Github (maybe).**

This is why the concept of *plugins* doesn't quite make sense for UserFrosting.  Plugins rely on the highly structured and predictable behavior of the core codebase, hooking into predefined points in the code.  This comes at a price - your application is locked into a particular behavior.  Does your application require a more complex user registration process than is shipped with UF by default?  Well too bad, because a bunch of plugins depend on the registration process working as originally designed.

Fine, you might say, but why don't I just develop my fancy registration process itself as a plugin?  For that to happen, we'd need one of two things:

- UF would have to predict which aspects of the default registration process you might want to change, and provide appropriate event dispatchers ("hooks", to people coming from Wordpress).  
Practically speaking, this is impossible - by specifying certain points where developers are allowed to hook into the code, we are implicitly saying that there are other points where developers are *not* allowed to hook in.  In the extreme case, we'd basically have to provide a hook between every line of code.  And then document all of those hooks.  And even then, [it still might not be enough](https://developer.wordpress.org/plugins/hooks/creating-custom-hooks/).  
- Allow you to override the entire registration process (controllers, templates, and perhaps even models) with your own code.  In this case, you're essentially rewriting an entire portion of the codebase - it's just that now your overriding code lives in a separate place.  There's nothing wrong with this, and it is the basic concept behind inheritance in OOP and templating engines.  However, is it really a plugin any more?  And, you'll still need to put hooks in your own overriding code to allow *other* plugins to hook in.

This isn't to say that UF can't become more modular.  I am very much of the mind that we *should* try to more cleanly separate the default code that comes with UF from code created by developers.  But the relationship would be much more like that suggested in the second point above, where the developer simply overrides default behavior through class and template inheritance.

I'm also not criticizing CMS's - they are definitely more useful for many types of applications.  For a well-designed and well-maintained, modern, open source and free CMS for PHP, check out [Grav](https://getgrav.org/).

### UserFrosting is not drop-in user management.

We see a good number of newbie developers, bundles of spaghetti code in hand, come to UF looking for a drop-in user management solution ("I just need a user script!")

UserFrosting is not this.  Why not?  For some of the same reasons that UF is not a CMS.  There really is no good way, with all of its features, to make a user management system that you can just "drop in" to any arbitrarily designed and organized (or disorganized) code base.  Yes, there are [authentication packages](http://stackoverflow.com/a/26083520/2970321) and commerical authentication APIs, but these still require you to write code to use them in your application.  

UserFrosting is intended to work out of the box.  This means that it needs to be a complete application, with a working data model, views (HTML, CSS, and Javascript), routes, and other services.  To implement this complete application we had to make certain design choices, such as using [Slim](www.slimframework.com) as a microframework, [Eloquent](https://laravel.com/docs/5.2/eloquent) as a data model abstraction, and [Twig](twig.sensiolabs.org) as a templating engine.  These components are pervasive and, if used correctly, will necessarily dictate much of your codebase's architecture.  

We picked these components because they are FOSS (free, open source software), standards-compliant, widely used and supported, and we believe that they really are the best lightweight components available for building a modern MVC application.  If your code is a jumble of newbie spaghetti (newbghetti?), UserFrosting is a good opportunity to get up to speed with modern tools and design paradigms (like MVC).  

By integrating your code into UF you'll learn more about the advantages these tools provide, and how professional software engineers work.  Being a developer is more than just "writing code" - it's about designing and architecting a piece of software to be modular, easy to understand and maintain, and robust to bugs introduced by modifications.

Think about it like the construction of a new building.  A "coder" is your construction worker.  They know how to use the tools and do the job at hand, but they're not really thinking about the design of the building as a whole.  A software engineer, on the other hand, is an architect.  They're planning out the structure of the building, making high-level decisions that will affect the the building and its users throughout its lifetime.

UserFrosting, perhaps, is a prefab - it makes a lot of the basic design decisions for you, and functions out of the box, but you need to make it into a home.

### UserFrosting is not a web framework (well, this depends on how you define framework).

A web framework generally provides useful tools and services to make it easier to develop a web application in an organized, well-designed manner.  However, it doesn't usually provide a starting point for a complete, working application.

UserFrosting is designed to work out of the box.  UserFrosting *is built on top of a framework* (Slim), but as a fully functional application that relies on a mix of in-house and external components, it's unclear whether UF itself qualifies as a framework.  UF is geared specifically towards web applications that are centered around the actitivies of users.  It also provides a good example of how to use Slim (which *is* a framework) with other components to build a web application.

However, there is no intention to make UF a general-purpose framework in the same sense as Laravel or Symfony.  Those things already exist!

## Upgrade to Slim 3

Slim 3 has been out [for over four months now](http://www.slimframework.com/2015/12/07/slim-3.html), and has a lot of exciting new features:

- PSR-7 compliance
- Easier access to common services through Pimple, a dependency injection container (DIC)
- Even slimmer, outsourcing a lot of services that we didn't use anyway

The upgrade will require a fundamental change in how the code is architected.  Rather than handfuls of code scattered around `initialize.php`, `config-userfrosting.php`, and `UserFrosting.php`, we'll design object-oriented services that handle initialization and then inject these into Slim's DI container.

Slim 3 has gotten rid of "hooks" (which are usually called *event dispatchers* in other frameworks).  The hope is to redesign UF so that it doesn't need hooks (as discussed earlier) for extending functionality.  However, if we do, there are third-party options available such as Laravel's [Events](https://laravel.com/docs/5.2/events).

## Configuration

Configuration for UserFrosting is a mess.  It's tightly coupled to Slim 2's `config()` method, which means you have to define all your configuration through Slim.  This makes it difficult to use the same configuration file for multiple applications, for example in a multi-site configuration.  According to the principles of the [Twelve-Factor App](http://12factor.net/config), configuration must be stored separately from code.

Slim 3 doesn't even have a `config()` method any more, so this is a good time to improve how configuration works.  I have started developing a standalone [config](https://github.com/userfrosting/config) module, which can be constructed as a separate service and injected into Slim 3's DIC, just like any other service.  It can load configuration from multiple different files (for example, a base configuration, and then site- or package- specific configs), recursively merging them together and overriding base values.  It can also support different configuration modes.

Finally, it is based on a [Laravel Repository](https://github.com/illuminate/config/blob/master/Repository.php), which supports the `array.dot.syntax`.  So, instead of accessing a config variable as `$app->config('uri')['assets']['js']`, you can simply do `$config['uri.assets.js']` - much cleaner and more elegant.

There is also the matter of environment variables, another practice encouraged by the 12-factor app guidelines.  Rather than maintain separate configuration files for different environments, and then manually switch between the two during development and deployment, we can simply store them directly in the server's environment variables and access them using `getenv`.  For shared hosting users, who may not be able to modify environment variables, this can be simulated with the [dotenv](https://github.com/vlucas/phpdotenv) package.

## Directory Structure

The overall organization of the codebase is a mess.  We have some controller code defined in one huge `index.php` file, while other controller code is defined in various controller classes inside of `userfrosting/controllers`.  We also have Javascript scattered around, between `/public` and inlined into various templates.  This makes it difficult to find a particular piece of client-side code.

This is dumb.  I propose we move all controller code *out* of index.php and into either controller classes, or functionally grouped route files that can be included by globbing an entire directory.  "Package" code (non-core) could also define routes in a designated subdirectory, which UF will scan.

Assets (JS, CSS, images, etc) should be moved out of `public` as well, and instead grouped into a core `assets` directory (again, package code could have their own `assets` subdirectories as well).  When in development mode, a special route will retrieve and render these assets, since they will no longer be available by letting the server fetch them directly from the public directory.  When the build steps are run before deployment, only the *compiled* assets will be automatically copied to the public directory.  See the section on Asset Management for more information on how this will work.

We should also organize the core classes into directories to be compliant with [PSR-4](http://www.php-fig.org/psr/psr-4/).

## Authentication

UF's authentication system has grown out of the system used originally in UserCake - using PHP's `$_SESSION` to store the currently authenticated user's id.  We throw in a few bells and whistles, like "remember me" (persistent login), but it still functions in fundamentally the same way.

Unfortunately, using `$_SESSION` does not scale well to very large applications, and furthermore it is at the mercy of the whims of PHP and the operating system's session cleanup routines.  We should decouple the *storage* of authenticated users from their representation in UF, and allow authenticated users to be stored via other means on the server (database, native sessions, etc).

To do this, we might as well take advantage of an existing package - again, why reinvent the wheel?  Some possibilities include:

- [Sentinel](https://cartalyst.com/manual/sentinel/2.0)
- [Confide](https://github.com/Zizaco/confide)
- Laravel's [Authentication](https://laravel.com/docs/5.2/authentication) and [Authorization](https://laravel.com/docs/5.2/authorization) packages

## Error Handling

Our current approach to error handling is to add a flash message to the alert stream, and then use Slim (v2)'s `halt` method.  For example, when checking that the account is enabled during login:

```
   // Check that the user's account is enabled
   if ($user->flag_enabled == 0){
       $ms->addMessageTranslated("danger", "ACCOUNT_DISABLED");
       $this->_app->halt(403);
   }
```

This presents a couple problems.  First of all, Slim 3 no longer has a `halt` method - we would now have to generate a complete response.  This would make our code considerably messier.  

The bigger issue is that by immediately returning the response, we are closing off the possibility of implementing more extensive functionality in a generic way.  For example, right now our pattern is to *always* add a message to the alert stream and return an HTTP error code response.  This is really only designed for AJAX requests, for example when we want to let the client-side code handle the rendering of error messages within a page.

But, what if we just want to take the user to a custom error page?  If there is an error in a `GET` route that renders a page, for example, UF gives you a white screen of death, and you don't get to see any sign of an error message until you navigate to a different page.  It might be more useful in these cases if something in the route that renders the page, fails, to take the user to a custom error page.

We can handle both of these cases by throwing an exception that corresponds to the type of error, e.g. `BadRequestException`, `UnauthorizedException`, etc.  Then when Slim's error-handler catches the exception, it can take into account the type of request (XHR/AJAX vs traditional) and choose to either add a message to error stream and return an error code, or return a response containing a custom error page.

Of course, there are situations where neither of these responses are appropriate.  For example, if an unauthenticated client attempts to access a page restricted to authenticated users, you might want to simply redirect the client to the login page (rather than display a custom error page).  In these cases, it will be up to the controller to take an appropriate action instead of throwing an exception.

## Asset Management and Build Process

If you've ever created even the most basic, static web page, you've probably needed to include external resources such as Javascript, CSS, and image/video files.  For example, consider the following example page:


```
<!DOCTYPE html>
<html lang="en">
    <head>
    	<meta charset="utf-8">
      	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	<meta name="author" content="Alex Weissman">
   
    	<title>Alexander Weissman | Researcher, Designer, Developer, and Entrepreneur</title>
   
    	<!-- Favicon -->
   		<link rel="shortcut icon" type="image/x-icon" href="https://alexanderweissman.com/images/favicon.ico" />                        
   
		<link rel="stylesheet" href="https://alexanderweissman.com/css/font-awesome-4.3.0.css" type="text/css" media="">
		<link rel="stylesheet" href="https://alexanderweissman.com/css/bootstrap-3.3.2.css" type="text/css" media="">
		<link rel="stylesheet" href="https://alexanderweissman.com/css/font-bloomingtontutors.css" type="text/css" media="">
		<link rel="stylesheet" href="https://alexanderweissman.com/css/timeline.css" type="text/css" media="">
		<link rel="stylesheet" href="https://alexanderweissman.com/css/styles.css" type="text/css" media="">
    </head>
    
    <body id="page-top" data-target=".navbar-fixed-top">

		<h1>Projects</h1>
		<div class="row">
			<div class="col-md-4">
			  <img src="https://alexanderweissman.com/images/bloomingtontutors.png" alt="Bloomington Tutors">
			  <h2><a href="https://bloomingtontutors.com">Bloomington Tutors</a></h2>
			  <small>Finite Math, Calculus, Statistics and Business Tutoring at Indiana University.</small>
			  <p>
			      <ul class="text-left">
			          <li>Co-founder</li>
			          <li>Developer, website and CRM system</li>
			      </ul>
			  </p>
			</div>
			<div class="col-md-4">
			  <img src="https://alexanderweissman.com/images/userfrosting-new.png" alt="UserFrosting">
			  <h2><a href="http://www.userfrosting.com">UserFrosting</a></h2>
			  <small>A secure, modern user management system for PHP.</small>
			</div>
			<div class="col-md-4">
			  <img src="https://alexanderweissman.com/images/indiana-university.jpg" alt="Indiana University">
			  <h2><a href="http://bigscience.soic.indiana.edu/">Big Science Survey</a></h2>
			  <small>Indiana University Bloomington</small>
			</div>
		</div>
		<a class="btn btn-lg btn-default" href="projects">See more of my work</a>

		<script defer src="https://alexanderweissman.com/js/config.js" ></script>

		<!-- Load jQuery -->
		<script src="//code.jquery.com/jquery-1.11.3.min.js" ></script>
		<!-- Fallback if CDN is unavailable -->
		<script>window.jQuery || document.write('<script src="https://alexanderweissman.com/jsmin/jquery-1.11.3.min.js"><\/script>')</script>

		<script defer src="https://alexanderweissman.com/js/bootstrap-3.3.2.js" ></script>
		<script defer src="https://alexanderweissman.com/js/jqueryValidation/jquery.validate.js" ></script>
		<script defer src="https://alexanderweissman.com/js/jqueryValidation/jqueryvalidation-methods-fortress.js" ></script>
		<script defer src="https://alexanderweissman.com/js/jqueryValidation/additional-methods.js" ></script>
		<script defer src="https://alexanderweissman.com/js/userfrosting.js" ></script>
		<script defer src="https://alexanderweissman.com/js/scripts.js" ></script>
          
    </body>
</html>
```

As you can see, we're including multiple CSS files in the `<head>` of the page, for example `https://alexanderweissman.com/css/font-awesome-4.3.0.css`.  In the `<body>` of the page, we have some images, such as `https://alexanderweissman.com/images/indiana-university.jpg`, and at the bottom we've included multiple `.js` files, both those hosted on our site's server (such as `https://alexanderweissman.com/js/userfrosting.js`), and external resources such as `//code.jquery.com/jquery-1.11.3.min.js`.  Collectively, these resources are known as our page's **assets**.  

We can also talk about resources used by other pages on our site - some might be used on every single page (like `bootstrap.css` and `bootstrap.js`), others might only be used on specific types of pages (for example, I could have a CSS file that I use to style my company's "product" pages, but not our "user profile" pages).  Still others might only be used on one page ever - for example, a background image on my home page.  Collectively, we can refer to all of these assets used by one or more pages on a website as the **site assets**.

When you're first learning web development, or you're just developing a small website, you're probably used to just inserting the necessary `<link>`, `<img>`, and `<script>` tags directly into your HTML.  This is fine for smaller projects, but for larger systems, it starts to become less manageable.  For example:

- You might want to move an asset file to a different location.  For example, perhaps you decide that you want Bootstrap to have its own directory, rather than being split into separate `js` and `css` directories.
- You might want to easily switch between different versions of an asset, for example low-def and hi-def versions of the same video.
- You might want to keep separate CSS and JS files for each widget that your website uses during development (to make it easier to organize and debug), but then combine and minify them when you deploy the live site (to improve site speed and efficiency).
- You might want to change the name of an asset when you change its content, so that users' browsers are forced to download the latest version rather than using a cached copy (this is known as "cache-busting").
- You might decide that a certain page or group of pages no longer requires a particular asset and so the references should be removed.
- You might want to extract critical "above-the-fold" CSS for each page and inline it directly inside `<style>` tags to improve user experience when using asynchronous CSS loading techniques.

Each of these situations would require the developer to manually change part of the HTML for these pages.  Even if you're using a templating engine (which you should), some of these tasks can require a non-trivial amount of work and be very prone to human error.

It would be better, therefore, if our *web application* could automatically locate and generate the appropriate HTML tags for all of our site's pages' assets, with as little effort on the developer's part as possible.  This general problem is known as **asset management**.


### Critical CSS

https://www.smashingmagazine.com/2015/08/understanding-critical-css/


## Logging


## Testing

