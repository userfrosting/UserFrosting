# UserFrosting

Development branch for UF 4.

[Roadmap](https://github.com/userfrosting/UserFrosting/wiki/Roadmap-for-UserFrosting-4)
[Style Guidelines](STYLE-GUIDE.md)

## To get running

### Copy files

Pull the repo into a directory in your document root.  Alternatively, you can place just the contents of `public` in your document root, and update the `require` path in `index.php` to point to wherever your `app` directory resides.  Or configure a virtualhost to point directly to `public`.  Whatever you want to do.

### Install with Composer

Run `composer install` in your `app` directory.  If you don't have composer, get it.
To install Composer
  1. [Get Composer and Follow Installation instructions here](https://getcomposer.org/download )
  2. Be sure to [install Composer **globally**](https://getcomposer.org/doc/00-intro.md#globally): `mv composer.phar /usr/local/bin/composer`

### Install build environment

For development purposes, the first two steps are all that should be necessary.  However, if you'd like to test out or contribute to the build tools, you'll need to install Node and Gulp globally.

We use [Node.js](https://nodejs.org/en/) and Gulp to do site build tasks such as asset minification, concatenation, critical CSS, updating paths in CSS, etc.  There are PHP-based tools for this, but they seem to be on the way out and are lacking community support.  The trend has been towards using Node for these types of build tasks.

1. Install Node
https://nodejs.org/en/download/package-manager/

Do this in your local development environment - you won't actually be running Node on your live server.

2. Install gulp globally

```
npm install --global gulp-cli
```

Once Node (npm), gulp and composer are installed, your development environment is ready.  You can now install the packages necessary for the build script.

#### Install required Node modules

npm is to Node what Composer is to PHP.  And, just like Composer has `composer.json`, npm has `package.json`.  To install the required packages for our build script, simply run `npm install` in the `/build` directory.

You can safely exclude the `node_modules` directory from your repository, even if you plan to use git to push your project to production.  These node modules are only used for environment build tasks and are not used by the application itself.

Once the node modules are installed - you can now run the build task.

#### Gulp build task - to build your site and prepare for deployment.

All build tasks are defined in `gulpfile.js`.  We currently have two tasks, `build` and `copy`.  `copy` is automatically run when `build` is run.  Their purpose is to use [`gulp-bundle-assets`](https://github.com/dowjones/gulp-bundle-assets) to compile Javascript and CSS assets from the raw asset directories across your sprinkles, into "bundles".  These bundles consist of minified and concatenated files, which are smaller and more efficient for your users to download from your live site.  This improves site speed and performance.

To run the build task:

1. Run the following on the command line (this should create a `public/asset` directory with all the asset packges)
  - `gulp build`
2. To easily switch between raw and built assets, change or override the value of `use_raw_assets` in your `app/sprinkles/site/config/default.php` file.  Set it to `false` to tell UF to use your compiled asset bundles in `public/asset` instead of fetching the raw files.

## About the Developers

### Alex Weissman

Alex is the founder and co-owner of two companies, one that does [math tutoring at IU](https://bloomingtontutors.com) in Bloomington, IN and another company that does [math tutoring at UMD](https://collegeparktutors.com) in College Park, MD.  He is also a PhD student in the School of Informatics and Computing at Indiana University.

### Mike Jacobs

Mike's a programmer and IT specialist for a small business in NH, and works on open source projects when he's not camping or traveling.

### Louis Charette

Louis's a civil engineer who also has a passion for coding. He is one of the main contributors for SimpsonsCity.com and like to share is knowledge by helping other the same way he was helped when he first started coding.
