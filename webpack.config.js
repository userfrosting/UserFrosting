const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.UF_MODE || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/assets')
    // public path used by the web server to access the output path
    .setPublicPath('/assets/')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './app/assets/app.js')
    //.addEntry('mypage', './assets/mypage.js')

    // ACCOUNT, ADMIN & ADMIN-LTE entries
    .addEntry('dashboard', './node_modules/sprinkle-admin/app/assets/dashboard.js')
    .addEntry('page.dashboard', './node_modules/sprinkle-admin/app/assets/page.dashboard.js')
    .addEntry('page.activities', './node_modules/sprinkle-admin/app/assets/page.activities.js')
    .addEntry('page.group', './node_modules/sprinkle-admin/app/assets/page.group.js')
    .addEntry('page.groups', './node_modules/sprinkle-admin/app/assets/page.groups.js')
    .addEntry('page.role', './node_modules/sprinkle-admin/app/assets/page.role.js')
    .addEntry('page.roles', './node_modules/sprinkle-admin/app/assets/page.roles.js')
    .addEntry('page.permission', './node_modules/sprinkle-admin/app/assets/page.permission.js')
    .addEntry('page.permissions', './node_modules/sprinkle-admin/app/assets/page.permissions.js')
    .addEntry('page.user', './node_modules/sprinkle-admin/app/assets/page.user.js')
    .addEntry('page.users', './node_modules/sprinkle-admin/app/assets/page.users.js')
    .addEntry('page.register', './node_modules/theme-adminlte/app/assets/register.js')
    .addEntry('page.sign-in', './node_modules/theme-adminlte/app/assets/sign-in.js')
    .addEntry('page.forgot-password', './node_modules/theme-adminlte/app/assets/forgot-password.js')
    .addEntry('page.resend-verification', './node_modules/theme-adminlte/app/assets/resend-verification.js')
    .addEntry('page.set-or-reset-password', './node_modules/theme-adminlte/app/assets/set-or-reset-password.js')
    .addEntry('page.account-settings', './node_modules/theme-adminlte/app/assets/account-settings.js')

    // Copy Favicons
    .copyFiles({ from: './app/assets/favicons', to: 'favicons/[path][name].[hash:8].[ext]' })

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()
    // .disableSingleRuntimeChunk()
    
    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
    // .enableVersioning()

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer require api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/admin.js')
;

module.exports = Encore.getWebpackConfig();