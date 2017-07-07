// Returns a function, that, as long as it continues to be invoked, will not
// be triggered. The function will be called after it stops being called for
// N milliseconds. If `immediate` is passed, trigger the function on the
// leading edge, instead of the trailing.
function debounce(func, wait, immediate) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) {
            func.apply(context, args);
        }
    };
}

(function () {
    const $page = $('#page--create-adventure');
    if (!$page.length) {
        return;
    }

    const similarTitlesUrl = $page.data('similar-titles-url');

    let $title = $('#appbundle_adventure_title');
    $title.on('change keyup paste', debounce(function (e) {
        $.getJSON(similarTitlesUrl, {
            q: $(this).val()
        }).done(function (data) {
            const similarAdventuresWarning = $('.similar-adventures-warning');
            const similarAdventuresList = $('.similar-adventures-list');
            if (data.length === 0) {
                similarAdventuresWarning.addClass('hidden-xs-up');
            } else {
                similarAdventuresList.empty();
                for (let i = 0; i < data.length; i++) {
                    const adventure = data[i];
                    const link = $('<a></a>');
                    link.text(adventure.title);
                    link.attr('target', '_blank');
                    // TODO: We should not hardcode the URL here!
                    link.attr('href', '/adventures/' + adventure['slug']);
                    similarAdventuresList.append($('<li></li>').append(link));
                }
                similarAdventuresWarning.removeClass('hidden-xs-up');
            }
        })
    }, 500));
})();