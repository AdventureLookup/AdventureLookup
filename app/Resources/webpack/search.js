import Bloodhound from 'typeahead.js/dist/bloodhound';
import noUiSlider from 'nouislider';
import 'nouislider/distribute/nouislider.css';

(function () {
    const $page = $('#page--search-adventures');
    if (!$page.length) {
        return;
    }

    //$('.filter-slider').each(function () {
    //    const min = $(this).data('min');
    //    const max = $(this).data('max');
    //    const fieldId = $(this).data('field-id');
    //
    //    const $min = $(`#filter-${fieldId}-min`);
    //    const $max = $(`#filter-${fieldId}-max`);
    //
    //    const slider = noUiSlider.create($(this)[0], {
    //        start: [$min.val(), $max.val()],
    //        range: {
    //            'min': [min],
    //            'max': [max]
    //        },
    //        connect: true,
    //        step: 1,
    //        tooltips: true,
    //        format: {
    //            to: function (value) {
    //                return parseInt(value);
    //            },
    //            from: function (value) {
    //                return parseFloat(value)
    //            }
    //        }
    //    });
    //
    //    slider.on('update', function( values, handle ) {
    //        $min.val(values[0]);
    //        $max.val(values[1]);
    //    });
    //});

    $('#filter-add').click(function () {
        const $filter = $("#filter-selection");
        const val = $filter.val();
        if (val === "") {
            return;
        }
        $filter.find('option:selected').remove();
        const id = 'filter-' + val;
        $('#' + id).removeClass('d-none');
        document.getElementById(id).scrollIntoView();
        $filter.prop("selectedIndex", 0);

        $('#' + id + '-enabled').val('1');
    });

    $('.filter-card .filters .show-more').on('click', function () {
        $(this).closest('.filters').find('.form-check').removeClass('d-none');
        $(this).remove();
    });
    $(document).on('click', '.filter-card .filter-input button', function () {
        const $inputContainer = $(this).closest('.filter-input');
        const $clone = $inputContainer.clone();
        const $clonedInput = $clone.find('.adv-search-input');
        $clonedInput.val('');
        $inputContainer.after($clone);
        //initTypeahead($clonedInput);
    });

    const searchUrl = $page.data('search-url');

    if ($('#advanced-search').hasClass('d-none')) {
        $('#advanced-search').hide();
    }
    $('#toggle-adv-search').on('click', function () {
        $('#advanced-search').removeClass('d-none').fadeToggle();
    });

    $('.adv-search-input').each(function () {
        initTypeahead($(this));
    });
    function initTypeahead($element) {
        const id = $element.data('id');
        const content = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.whitespace,
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: searchUrl,
                prepare: (query, settings) => {
                    settings.url = searchUrl
                        .replace(/__ID__/g, id)
                        .replace(/__Q__/g, query);
                    return settings;
                },
            },
            sufficient: 20
        });

        $element.typeahead({
            minLength: 0,
            highlight: true
        }, {
            name: 'content',
            source: content,
            limit: 20
        });
    }
})();