# UserFrosting

Skeleton version of framework.

## To get running

1. Place contents in your document root.  Alternatively, you can place just the contents of `public` in your document root, and update the `require` path in `index.php` to point to wherever your `app` directory resides.  Or configure a virtualhost to point directly to `public`.  Whatever you want to do.
2. Run `composer install` in your `app` directory.  If you don't have composer, get it.
3. Take'r for a spin.

## To build

We use [Node.js](https://nodejs.org/en/) and Gulp to do site build tasks, such as asset minification, concatenation, critical CSS, updating paths in CSS, etc.

To be able to perform build tasks, you need to install Node and Gulp globally.  Do this in your local development environment - you won't actually be running Node on your live server.

In the `build` directory, use `npm install` to install all the required node modules, as defined in the included `package.json`.  `package.json` is Node's version of `composer.json`.

You should then be able to run `gulp build` to build your site and prepare for deployment.

To easily switch between raw and built assets, change or override the value of `use_raw_assets` in your `app/config/default.php` file.  Later, we can replace this with an environment variable.
