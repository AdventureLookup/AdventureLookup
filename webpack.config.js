var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('web/assets/')
    .setPublicPath('/assets')
    .cleanupOutputBeforeBuild()

    .addEntry('app', './app/Resources/webpack/index.js')
    .createSharedEntry('vendor', [
        'jquery',
        'bootstrap',
        // TODO: Including the Bootstrap css here duplicates it for some reason, although the docs indicate otherwise:
        // https://symfony.com/doc/current/frontend/encore/shared-entry.html
        // If you make it work, don't forget to include the vendor css in the base.html.twig layout:
        // <link rel="stylesheet" href="{{ asset('assets/vendor.css') }}" />
        //'bootstrap/scss/bootstrap',
        // TODO: Add other vendor dependencies in here once we cleaned them up.
    ])

    .enableSassLoader()
    .enablePostCssLoader()
    .configureBabel(function(babelConfig) {
        // add additional presets
        babelConfig.presets.push('es2015');

        // no plugins are added by default, but you can add some
        // babelConfig.plugins = ['styled-jsx/babel'];
    })
    .autoProvidejQuery()
    .autoProvideVariables({
        'Tether': 'tether',
    })
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
