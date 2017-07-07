import 'bootstrap/dist/js/bootstrap.js';
import 'font-awesome-webpack';
import 'select2/dist/css/select2.css';
import 'select2/dist/js/select2.full.js';
import toastr from 'toastr/toastr';
import 'toastr/toastr.scss';
import "typeahead.js/dist/typeahead.jquery";
import "typeahead.js-bootstrap4-css/typeaheadjs.css";
import './style.scss';

import './add-content.js';
import './search.js';
import './adventure-create.js';


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