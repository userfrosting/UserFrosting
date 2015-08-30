**Problem 1**

Firstly, it may be a problem with your apache not having the mod_rewrite.c module installed or enabled. 

For this reason, you would have to  enable it as follows

1. Open up your console and type into it, this: 

    `sudo a2enmod rewrite`

2. Restart your apache server.

    `service apache2 restart`

**Problem 2**

1. You may also, in addition to the above, if it does not work, have to change the override rule from the apache conf file (either apache2.conf, http.conf , or 000-default file).

2. Locate "Directory /var/www/"

3. Change the "Override None" to "Override All"

**Problem 3**

If you get an error stating rewrite module is not found, then probably your userdir module 
is not enabled. For this reason you need to enable it. 

1. Type this into the console:

    `sudo a2enmod userdir`

2. Then try enabling the rewrite module if still not enabled (as mentioned above).

To read further on this, you can visit this site: [http://seventhsoulmountain.blogspot.com/2014/02/wordpress-permalink-ubuntu-problem-solutions.html](http://seventhsoulmountain.blogspot.com/2014/02/wordpress-permalink-ubuntu-problem-solutions.html)

** Slim treating POST as GET request **
https://github.com/slimphp/Slim/issues/1220

