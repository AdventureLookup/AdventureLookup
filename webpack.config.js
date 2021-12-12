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
  .addEntry('vendor', './app/Resources/webpack/js/vendor.js')
  .splitEntryChunks()
  .enableSingleRuntimeChunk()

  .enableSassLoader()
  .enablePostCssLoader()
  .enableReactPreset()
  .autoProvidejQuery()
  .autoProvideVariables({
    'Tether': 'tether',
  })
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
