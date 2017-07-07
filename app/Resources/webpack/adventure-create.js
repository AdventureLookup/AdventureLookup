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
    const DEBOUNCE = 250;
    const $page = $('#page--create-adventure');
    if (!$page.length) {
        return;
    }

    const similarTitlesUrl = $page.data('similar-titles-url');
    const searchUrl = $page.data('search-url');

    let $title = $('#appbundle_adventure_field_title');
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
    }, DEBOUNCE));

    $('select.adventure-field').each(function () {
        const $select = $(this);
        $select.selectize({
            create: true,
            //sortField: 'title',
            valueField: 'title',
            labelField: 'title',
            maxItems: null,
            preload: 'focus',
            searchField: 'title',
            render: {
                option: function(item, escape) {
                    return '<div>' + escape(item.title) + '</div>';
                }
            },
            //score: function(search) {
            //    var score = this.getScoreFunction(search);
            //    return function(item) {
            //        return score(item) * (1 + Math.min(item.watchers / 100, 1));
            //    };
            //},
            load: function(query, callback) {
                $.ajax({
                    url: searchUrl.replace(/__ID__/g, $select.data('id')),
                    data: {
                        q: query
                    },
                    type: 'GET',
                    error: function() {
                        callback();
                    },
                    success: function(res) {
                        callback(res.map((content) => {return {'title': content}}),);
                    }
                });
            }
        });
        /*$(this).select2({
            tags: true,
            minimumInputLength: 1,
            ajax: {
               url: searchUrl.replace(/__ID__/g, $(this).data('id')),
                   //.replace(/__Q__/g, $(this).val()),
               dataType: 'json',
               delay: DEBOUNCE,
               data: function (params) {
                   return {
                       q: params.term,
                       page: params.page
                   };
               },
               processResults: function (data, params) {
                   // parse the results into the format expected by Select2
                   // since we are using custom formatting functions we do not need to
                   // alter the remote JSON data, except to indicate that infinite
                   // scrolling can be used
                   console.log(data);
                   params.page = params.page || 1;

                   return {
                       results: data.map((content) => {return {'id': content, 'text': content}}),
                       pagination: {
                           more: false //(params.page * 30) < data.total_count
                       }
                   };
               },
               cache: true
           },
           createTag: function (params) {
               return {
                   id: params.term,
                   text: params.term,
                   newOption: true
               }
           },
           templateResult: function (data) {
               var $result = $("<span></span>");

               $result.text(data.text);

               if (data.newOption) {
                   $result.append(" <em>(new)</em>");
               }

               return $result;
           }
       });*/
    });
})();