const Encore = require('@symfony/webpack-encore');

// List dependent sprinkles and local entries files
const sprinkles = {
    AdminLTE: require('@userfrosting/theme-adminlte/webpack.entries'),
    Admin: require('@userfrosting/sprinkle-admin/webpack.entries'),
    App: require('./webpack.entries')
}

// Merge dependent Sprinkles entries with local entries
let entries = {}
Object.values(sprinkles).forEach(sprinkle => {
    entries = Object.assign(entries, sprinkle);
});

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

    // Include all entries
    .addEntries(entries)

    // Copy Favicons
    .copyFiles({ from: './app/assets/favicons', to: 'favicons/[path][name].[hash:8].[ext]' })

    // Copy images
    .copyFiles({ from: './app/assets/images', to: 'images/[path][name].[hash:8].[ext]' })

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

    // uncomment if you use React
    //.enableReactPreset()
;

module.exports = Encore.getWebpackConfig();