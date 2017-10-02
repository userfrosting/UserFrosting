# Guidelines for Getting Help with UserFrosting

**Before** you open a new issue or ask a question in chat, you **must** read these guidelines.  If it is evident from your issue that you failed to research your question properly, your issue may be closed without being answered.

## Troubleshooting

There are a few common stumbling blocks that new users face when setting up UserFrosting for the first time.  If you are new to the current version of UserFrosting, please first look at the [basic requirements and installation instructions](https://learn.userfrosting.com/basics/requirements/basic-stack).

If you don't find what you're looking for in the troubleshooting page, then please check the [wiki](https://github.com/userfrosting/UserFrosting/wiki) and [existing issues](https://github.com/alexweissman/UserFrosting/issues?utf8=%E2%9C%93&q=is%3Aissue), both opened and closed.  Your question may have already been asked and answered before!

You can also search for help on Stack Overflow.  In addition to the tags for the components that UF builds upon, such as [Slim](http://stackoverflow.com/questions/tagged/slim), [Twig](http://stackoverflow.com/questions/tagged/twig), [Eloquent](http://stackoverflow.com/questions/tagged/eloquent), [jQuery Validate](http://stackoverflow.com/questions/tagged/jquery-validate), [Select2](http://stackoverflow.com/questions/tagged/jquery-select2), there is now a [UserFrosting tag](http://stackoverflow.com/questions/tagged/userfrosting) as well.

There are also tags for the utilities upon which UserFrosting depends, such as [Composer](http://stackoverflow.com/questions/tagged/composer-php) and [Git](http://stackoverflow.com/questions/tagged/git).

## Asking for Help

In general, the Github issue tracker should only be used for bug reports and feature requests.  If you're just having trouble getting something to work, you should ask on Stack Overflow instead. Tag your question with the `userfrosting` tag, and optionally with any tags specific to the relevant underlying technologies, such as `slim`, `twig`, `eloquent`, `composer`, etc.  You should also mention the version of UserFrosting that you are using.

After posting a question on Stack Overflow, please [link to it in chat](https://chat.userfrosting.com).  This will ensure that more people see it, and provide a place where we can discuss and help clarify your question.

On Github, Chat, and Stack Overflow, please keep in mind the following:

1. Remember that courtesy and proper grammar go a long way. Please take the time to craft a **precise, polite issue**. We will do our best to help, but remember that this is an open-source project - none of us are getting paid a salary to develop this project, or act as your personal support hotline :wink:

2. Report any errors in detail.  Vague issues like "it doesn't work when I do this" are not helpful.  Show that you have put some effort into identifying the cause of the error.

3. There are three main places where you may find error messages:

- Backend (PHP-related) fatal errors: in your PHP error log. This is usually a file called `php_error_log` or something like that. In XAMPP, the default location of this file is `XAMPP/xamppfiles/logs/`. For other web hosting platforms, please consult the documentation or do a quick Google search (i.e. "where is the php error log in _____"). Some web hosts may provide a special interface for accessing the php error log, through ssh, cpanel, etc. Please ask them directly for help with this.

- Non-fatal PHP errors will be logged in your UserFrosting error log.  Look for your `app/logs/errors.log` file.

- Frontend (Javascript-related) errors: in your browser's Javascript console. See [this guide](https://learn.userfrosting.com/background/client-side) to using your browser console.

You should also try testing your code in a local development environment, to separate **code-related** issues from **server** issues.  In general, we recommend that you install a local development server on your computer, rather than [testing your code directly on the production server](https://pbs.twimg.com/media/BxfENwpIYAAcHqQ.png).  This means you can test your code directly on your own computer, making development faster and without the risk of exposing sensitive information to the public.  We recommend installing [XAMPP](https://www.apachefriends.org) if you don't already have a local server set up.

## Contributing to the Codebase

We welcome your technical expertise!  But first, please join us in [chat](https://chat.userfrosting.com) to discuss your proposed changes/fixes/enhancements before you get started.  At least one member of our development team will usually be around.

Please also be sure to read our [style guidelines](STYLE-GUIDE.md).

When it's time to integrate changes, our git flow more or less follows http://nvie.com/posts/a-successful-git-branching-model/.

### Branches

- `master`: The current release or release candidate.  Always numbered as `major.minor.revision`, possibly with an `-alpha` or `-beta` extension as well.
- `develop`: During alpha/beta, contains major changes to a release candidate.  After beta, contains breaking changes that will need to wait for the next version to be integrated.  Always numbered as `major.minor.x`, possibly with an `-alpha` or `-beta` extension as well.

### Changes

#### Hotfixes

Hotfixes should be created in a separate branch, and then merged into both **master** and **develop**.

#### Features

New features that introduce some breaking changes should be created in a separate branch.  When they are ready, they can be merged into `develop`.

### Releases

After every release, the `master` branch (and possibly `develop`, for minor/major releases) should immediately be version-bumped.  That way, new changes can be accumulated until the next release.

When a new version is created, the version number need to be changed in `app/define.php`. `CHANGELOG.md` should also be updated and the associated tag should be created on Github.

#### Alpha/beta releases

During alpha/beta, a release candidate sits on the `master` branch.  Minor improvements should be treated as hotfixes, while major changes should be treated as features.  In alpha/beta, major changes can still be integrated into `master` from `develop`.  However, this should bump the revision number instead of the minor/major number.

## Building the API documentation

To build the API documentation, install [ApiGen](http://www.apigen.org/) globally and then run:

`apigen generate --source UserFrosting/app,userfrosting-assets/src,userfrosting-config/Config,userfrosting-fortress/Fortress,userfrosting-i18n/I18n,userfrosting-session/Session,userfrosting-support/Support --destination userfrosting-api --exclude *vendor*,*_meta* --template-theme "bootstrap"`

from inside your dev directory.
