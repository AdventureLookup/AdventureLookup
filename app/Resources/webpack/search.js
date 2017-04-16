import Bloodhound from "typeahead.js/dist/bloodhound";

(function () {
    const $page = $('#page--search-adventures');
    if (!$page.length) {
        return;
    }
    const searchUrl = $page.data('search-url');

    if ($('#advanced-search').hasClass('d-none')) {
        $('#advanced-search').hide();
    }
    $('#toggle-adv-search').on('click', function () {
        $('#advanced-search').removeClass('d-none').fadeToggle();
    });


    initTypeahead();
    function initTypeahead() {
        $('.adv-search-input').each(function() {
            const id = $(this).data('id');
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

            $(this).typeahead({
                minLength: 0,
                highlight: true
            }, {
                name: 'content',
                source: content,
                limit: 20
            });
        });
    }
})();