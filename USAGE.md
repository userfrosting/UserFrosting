# UserFrosting

Skeleton version of framework.

## To get running

1. Place contents in your document root.  Alternatively, you can place just the contents of `public` in your document root, and update the `require` path in `index.php` to point to wherever your `app` directory resides.  Or configure a virtualhost to point directly to `public`.  Whatever you want to do.
2. Run `composer install` in your `app` directory.  If you don't have composer, get it.
To install Composer
  1.  [Get Composer and Follow Installation instructions here](https://getcomposer.org/download )
  2. make sure your composer is installed Globally https://getcomposer.org/doc/00-intro.md#globally `mv composer.phar /usr/local/bin/composer`
3. Take'r for a spin.

## To build
To be able to perform build tasks, you need to install Node and Gulp globally.  
Do this in your local development environment - you won't actually be running Node on your live server.

1. Install Node
https://nodejs.org/en/download/package-manager/
We use [Node.js](https://nodejs.org/en/) and Gulp to do site build tasks, 
such as asset minification, concatenation, critical CSS, updating paths in CSS, etc.

2. Install gulp globally
```npm install --global gulp-cli ```

### Once Node (npm), gulp and composer are installed your development environment is ready

Now download the user frosting package and unzip in your target location <yourdir>

#### Update Composer Packages 
1. Go to <yourdir>/UserFrosting/app
    1. run the following on the command line (this should install / update packages)
        - `composer update`
        1. Please note you don’t have to run this the first time you download the user frosting package, the package already contains all the packages it needs. you can run this if you wish to refresh the packages

#### Download Node (NPM) Modules
1. Go to <yourdir>/UserFrosting/build
    1. Run the following on the command line (this should install all the Node packages needed for the build)
        -  `npm install `
        1. Please note that these node modules are only used for environment build tasks and are not used by the application itself.
    2. Once the node modules are installed - you can now create the asset packages needed for userfrosting. 
The required node modules, as defined in the included `package.json`.  `package.json` is Node's version of `composer.json`.

#### Gulp Build - to build your site and prepare for deployment.
1. Run the following on the command line (this should create public/asset directory with all the asset packges)
  - `gulp  build`
2. To easily switch between raw and built assets, change or override the value of `use_raw_assets`  in your `app/config/default.php` file.  Later, we can replace this with an environment variable.

