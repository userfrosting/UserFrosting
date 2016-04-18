# Guidelines for Getting Help with UserFrosting

**Before** you open a new issue or ask a question in chat, you **must** read these guidelines.  If it is evident from your issue that you failed to research your question properly, your issue may be closed without being answered.

## Troubleshooting

There are a few common stumbling blocks that new users face when setting up UserFrosting for the first time.  If you are new to the current version of UserFrosting, please first look at the [troubleshooting page](http://www.userfrosting.com/troubleshooting).

If you don't find what you're looking for in the troubleshooting page, then please check [existing issues](https://github.com/alexweissman/UserFrosting/issues?utf8=%E2%9C%93&q=is%3Aissue), both opened and closed.  Your question may have already been asked and answered before!

## Opening Issues

If you can't find what you need in the troubleshooting page, or in the existing issues, then you may open a new issue.  But first, please read the following:

1. Remember that courtesy and proper grammar go a long way. Please take the time to craft a **precise, polite issue**. We will do our best to help, but remember that this is an open-source project - none of us are getting paid a salary to develop this project, or act as your personal support hotline :wink:

2. Report any errors in detail.  Vague issues like "it doesn't work when I do this" are not helpful.  Show that you have put some effort into identifying the cause of the error.

3. There are two main places where you may find error messages:

- Backend (PHP-related) errors: in your PHP error log. This is usually a file called `php_error_log` or something like that. In XAMPP, the default location of this file is `XAMPP/xamppfiles/logs/`. For other web hosting platforms, please consult the documentation or do a quick Google search (i.e. "where is the php error log in _____"). Some web hosts may provide a special interface for accessing the php error log, through ssh, cpanel, etc. Please ask them directly for help with this.

- Frontend (Javascript-related) errors: in your browser's Javascript console. This can be accessed from "Tools->Web Developer" menu in Firefox, or "More tools->Javascript Console" in Chrome. For help with other browsers, please Google "where is the javascript console in ____".

You should also try testing your code in a local development environment, to separate **code-related** issues from **server** issues.  In general, we recommend that you install a local development server on your computer, rather than [testing your code directly on the production server](https://pbs.twimg.com/media/BxfENwpIYAAcHqQ.png).  This means you can test your code directly on your own computer, making development faster and without the risk of exposing sensitive information to the public.  We recommend installing [XAMPP](https://www.apachefriends.org) if you don't already have a local server set up.
