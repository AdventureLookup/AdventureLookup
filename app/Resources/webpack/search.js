import 'nouislider/distribute/nouislider.css';

(function () {
    const $page = $('#page--search-adventures');
    if (!$page.length) {
        return;
    }

    $('.filter-card .filter-title').click(function () {
        const $filter = $(this).parent('.filter-card');
        const $filterEnabled = $(this).parent('.filter-enabled');
        $filterEnabled.val($filter.toggleClass('expanded'));
    });
    $('.filter-tag .filter-remove').click(function () {
        const fieldName = $(this).data('field-name');
        const value = $(this).data('value');
        const key = $(this).data('key');
        const fieldType = $(this).data('field-type');
        if (fieldType === 'string') {
            $(`input[name^="f[${fieldName}][v]"][value="${value}"]`).prop('checked', false);
        } else if (fieldType === 'boolean') {
            $(`input[name^="f[${fieldName}][v]"][value=""]`).prop('checked', true);
        } else if (fieldType === 'integer') {
            $(`input[name^="f[${fieldName}][v][${key}]"]`).val('');
        }
        $(this).parent('.filter-tag').remove();
        $('#search-btn').click();
    });

    $('.filter-card .filters .show-more').on('click', function () {
        $(this).closest('.filters').find('.form-check').removeClass('d-none');
        $(this).remove();
    });
})();
