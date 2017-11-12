var Encore = require('@symfony/webpack-encore');
var webpack = require('webpack');

Encore
  .setOutputPath('web/assets/')
  .setPublicPath('/assets')
  .cleanupOutputBeforeBuild()

  .addPlugin(new webpack.ProvidePlugin({
    Popper: ['popper.js', 'default'],
  }))

  .addEntry('app', './app/Resources/webpack/js/index.js')
  .createSharedEntry('vendor', [
    'jquery',
    'bootstrap',
    // TODO: Including the Bootstrap css here duplicates it for some reason, although the docs indicate otherwise:
    // https://symfony.com/doc/current/frontend/encore/shared-entry.html
    // If you make it work, don't forget to include the vendor css in the base.html.twig layout:
    // <link rel="stylesheet" href="{{ asset('assets/vendor.css') }}" />
    //'bootstrap/scss/bootstrap',
    'toastr',
    'selectize',
    'vanilla-lazyload',
  ])

  .enableSassLoader()
  .enablePostCssLoader()
  .autoProvidejQuery()
  .autoProvideVariables({
    'Tether': 'tether',
  })
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
