const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = {
  entry: {
    main: './app/Resources/webpack/index.js',
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'web/assets')
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: [/node_modules/],
        use: [{
          loader: 'babel-loader',
          options: { presets: ['es2015'] }
        }]
      },
      {
        test: /\.(scss|css)$/,
        use: ExtractTextPlugin.extract({
          use: [
            'css-loader',
            'sass-loader',
          ]
        })
      },
      // the url-loader uses DataUrls.
      { test: /\.woff(2)?(\?v=[0-9]\.[0-9]\.[0-9])?$/, loader: 'url-loader?limit=10000&mimetype=application/font-woff&publicPath=/assets/' },
      // the file-loader emits files.
      { test: /\.(ttf|eot|svg)(\?v=[0-9]\.[0-9]\.[0-9])?$/, loader: 'file-loader?publicPath=/assets/' },
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
