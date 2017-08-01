import 'jquery/src/jquery'
import 'bootstrap/dist/js/bootstrap.js';
import 'font-awesome-webpack';
import 'select2/dist/css/select2.css';
import 'select2/dist/js/select2.full.js';
import 'selectize/dist/css/selectize.bootstrap3.css'
import 'selectize/dist/js/standalone/selectize'
import toastr from 'toastr/toastr';
import 'toastr/toastr.scss';
import "typeahead.js/dist/typeahead.jquery";
import "typeahead.js-bootstrap4-css/typeaheadjs.css";
import LazyLoad from "vanilla-lazyload/dist/lazyload";
import './sass/style.scss';

import './add-content.js';
import './search.js';
import './adventure.js';


toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

// Lazy-load images using
// https://github.com/verlok/lazyload
export const myLazyLoad = new LazyLoad();

// Hack to reload CSS using HMR
// https://github.com/symfony/webpack-encore/pull/8#issuecomment-312599836
// Needed until https://github.com/symfony/webpack-encore/issues/3 is fixed
import hotEmitter from 'webpack/hot/emitter';
if (module.hot) {
    hotEmitter.on('webpackHotUpdate', () => {
        document.querySelectorAll('link[href][rel=stylesheet]').forEach((link) => {
            link.href = link.href.replace(/(\?\d+)?$/, `?${Date.now()}`); // eslint-disable-line
        });
    });
}
