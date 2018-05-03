import 'jquery/src/jquery'
import 'bootstrap/dist/js/bootstrap.js';
import 'selectize/dist/js/standalone/selectize'
import autosize from 'autosize';
import toastr from 'toastr/toastr';
import '../sass/style.scss';
import 'cookieconsent/build/cookieconsent.min';

import './adventures';
import './adventure';
import './reviews';
import './adventure_list';
import './paginated_list_group';

window.cookieconsent.initialise({
    palette: {
        popup: { background: "#fff" },
        button: {
            background: "#f56e4e",
            text: "#fff",
        },
    },
    revokable: false,
    location: false,
    theme: "edgeless",
    position: "bottom-left",
});

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

autosize(document.querySelectorAll('textarea.autosize'));

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
