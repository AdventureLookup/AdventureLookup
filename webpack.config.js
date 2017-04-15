const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = {
  entry: './app/Resources/webpack/index.js',
  output: {
    filename: 'bundle.js',
    path: path.resolve(__dirname, 'web/assets')
  },
  module: {
    rules: [
      {test: /\.(js|jsx)$/, use: 'babel-loader'},
      {
        test: /\.(scss|css)$/,
        use: ExtractTextPlugin.extract({
          use: [
            'css-loader',
            'sass-loader',
          ]
        })
      },
    ]
  },
  plugins: [
    new webpack.optimize.UglifyJsPlugin(),
    new webpack.ProvidePlugin({
      $: "jquery",
      jQuery: "jquery",
      Tether: "tether",
    }),
    new ExtractTextPlugin('styles.css'),
    //new webpack.optimize.CommonsChunkPlugin({
    //  name: 'commons',
    //  filename: 'commons.js',
    //  minChunks: 2,
    //}),
  ],
  resolve: {
    alias: {
      jquery: "jquery/src/jquery",
      tether: "tether/dist/js/tether.js",
    }
  },
  watchOptions: {
    poll: true
  },
};
