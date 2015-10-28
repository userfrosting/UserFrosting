# Troubleshooting UserFrosting

These are some of the most common issues faced by developers who are new to UserFrosting.

*   [I get a blank page when I try to install UserFrosting.](#blank-page)
*   [UF takes me to a blank page after I create the master account.](#wrong-status-code)
*   [I created a new class, but UserFrosting doesn't seem to recognize it.](#new-class)
*   [Composer gives me errors when I run `composer update`.](#composer-error)
*   [UF doesn't seem to work on nginx or IIS.](#other-servers)
*   [I'm using an Apache alias for my site and I get 404 errors when trying to install.](#alias)

## I get a blank page when I try to install UserFrosting.

### Solution 1

If you are using Apache (the default web server that comes installed with WAMP, XAMPP, and most shared web hosting services), check that you have the Rewrite Engine module (`mod_rewrite.c`) installed and enabled.

Some distributions, like WAMP, may not have this module automatically enabled, and you will need to do so manually.

In a shared hosting environment, you may need to have your hosting service do this for you.

If you have shell access to your server, please take the following steps (from [Stack Overflow](http://stackoverflow.com/questions/869092/how-to-enable-mod-rewrite-for-apache-2-2/21658877#21658877)):

1.  <small>Open up your console and type into it: `sudo a2enmod rewrite`</small>
2.  <small>Restart your apache server: `service apache2 restart`</small>

You may also, in addition to the above, if it does not work, have to change the override rule from the apache conf file (either apache2.conf, http.conf , or 000-default file).

1.  <small>Locate "Directory /var/www/"</small>
2.  <small>Change the "Override None" to "Override All"</small>

If you get an error stating rewrite module is not found, then probably your userdir module is not enabled. For this reason you need to enable it.

1.  <small>Type this into the console: `sudo a2enmod userdir`</small>
2.  <small>Then try enabling the rewrite module if still not enabled (as mentioned above).</small>

To read further on this, you can visit this site: [http://seventhsoulmountain.blogspot.com/2014/02/wordpress-permalink-ubuntu-problem-solutions.html](http://seventhsoulmountain.blogspot.com/2014/02/wordpress-permalink-ubuntu-problem-solutions.html)

### Solution 2

Make sure that your system meets the [minimum requirements]({{site.url}}/installation). In particular, you must have PHP 5.4 or higher installed.

### Solution 3

If you are sure that you have the Rewrite module enabled and properly configured, make sure that you have your database credentials properly set in `config-userfrosting.php`. See [Installing UserFrosting]({{site.url}}/installation) for more details.

## Nothing happens after I press "register" to create the master account.

There is probably an error, but it is not being displayed because your server is returning an `HTTP 200 - OK` instead of the error code it is supposed to. You can confirm this by checking in your browser console for the POST request to `/install/master`.

See, UserFrosting is built on the principles of [REST](https://en.wikipedia.org/wiki/Representational_state_transfer), which says that we should use the native [HTTP status codes](https://en.wikipedia.org/wiki/List_of_HTTP_status_codes) when communicating with the server. When you submit something to a UserFrosting application, the server side returns a code such as:

*   `200 - OK`
*   `500 - Internal server error`
*   `400 - Bad request`
*   `403 - Forbidden`
*   `404 - Not found`

On the client side, UserFrosting uses these HTTP codes to determine what action to take after the response is received. Thus, if it receives `HTTP 200 - OK`, it will assume the request was successful. So, thinking that the master account creation was successful, UF tries to forward you to the home page. But when it gets there, UF sees that the master account was in fact _not_ successful, so it takes you back to the registration page, thus making it seem like the registration page just refreshed.

We are not yet sure why some servers are returning the wrong HTTP status code. This problem has been reported on the [Slim forums](http://help.slimframework.com/discussions/questions/640-response-header) as well. It may be a problem due to caching, buffering, or certain [reverse proxy](http://serverfault.com/questions/400345/apache-reverse-proxy-not-preserving-headers) configurations.

Our suggestion for now, if you're having this problem, is to contact your hosting service and explain that the server does not seem to be preserving your response headers correctly.

## I created a new class, but UserFrosting doesn't seem to recognize it.

You need to [install Composer](http://getcomposer.org), PHP's dependency manager. In addition to automatically loading external packages, Composer also autoloads the class files for your project. When you add a new class, you must run `composer update` in the `userfrosting` directory (the same directory that contains `composer.json`). Make sure that you have installed Composer [globally](https://getcomposer.org/doc/00-intro.md#globally).

## Composer gives me errors when I run `composer update`.

When this happens, you might get an error message complaining about a `.git` directory missing in `vendor`.  To solve this problem, simply delete the `vendor/alexweissman` directory and re-run `composer update`.

## UF doesn't seem to work on nginx or IIS.

Currently, UserFrosting only comes with a configuration file for Apache. So, we do not yet "officially" support nginx or IIS (but we hope to soon!)

If you are proficient in IIS or other server technologies, and wish to contribute the corresponding configuration file, please open a pull request - we'd really appreciate it!

In the meantime, there may be a workaround: you can install [Helicon Ape](http://www.helicontech.com/ape/), which provides support for Apache `.htaccess` and `.htpasswd` configuration files in Microsoft IIS. Choose "full server download" when prompted by the Helicon Ape installer.

## I'm using an Apache alias for my site and I get 404 errors when trying to install.

Assuming your Apache alias maps `/project` to `C:/WebProject/`, you'll be accessing the installer at `http://example.com/project/public/`, but your 404 error will be showing `/WebProject/public/index.php` instead.

The problem here is that `mod_rewrite` is incorrectly pointing to the directory itself (`WebProject`) rather than the alias name (`project`).

To correct this, you’ll need to figure out the proper `RewriteBase` to use based on your alias and the 404 error. Add a line in `/public/.htaccess` after `"RewriteEngine On"`:

`RewriteBase /project/public/`

This line will direct `mod_rewrite` to use the Apache alias. Once this is done, you can re-run the installer by going to `http://example.com/project/public` again.