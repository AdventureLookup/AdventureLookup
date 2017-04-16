import "typeahead.js/dist/typeahead.jquery";
import Bloodhound from "typeahead.js/dist/bloodhound";
import "typeahead.js-bootstrap4-css/typeaheadjs.css";

(function () {
    const $page = $('#page--add-content');
    if (!$page.length) {
        return;
    }

    const searchUrl = $page.data('content-search-url');

    const prefix = 'appbundle_tagcontent';

    const $tagSelection = $('#' + prefix + '_tag');
    $tagSelection.select2({
        'templateResult': (result) => {
            return result.text.split('|')[0];
        },
        'templateSelection': (result) => {
            return result.text.split('|')[0];
        }
    });
    $tagSelection.on('select2:select', function(){
        $(this).focus();
        changeContent($(this).select2('data')[0]['text'].split('|')[1]);
    });

    const $content = $('#' + prefix + '_content');
    $content.remove();

    $tagSelection.parent().after(`
<div content="form-group">
    <label class="control-label required" for="${prefix}_content">Content</label>
    <div id="tag-content-placeholder" class="mb-3"></div>
</div>
`);


    const $contentContainer = $('#tag-content-placeholder');
    function changeContent(type) {
        $contentContainer.empty();
        switch (type) {
            default:
            case 'string':
                createStringField();
                break;
            case 'text':
                $contentContainer.append(`
<textarea id="${prefix}_content" name="${prefix}[content]" required="required" class="form-control" rows="20">

</textarea>
`);
                break;
            case 'boolean':
                $contentContainer.append(`
<input type="hidden" value="0" name="${prefix}[content]">
<input type="checkbox" value="1" id="${prefix}_content" name="${prefix}[content]" />
`);
                break;
            case 'integer':
                $contentContainer.append(`
<input type="number" id="${prefix}_content" name="${prefix}[content]" required="required" class="form-control" />
`);
                break;
        }

        function createStringField() {
            $contentContainer.append(`
<div class="input-group">
  <input type="text" id="${prefix}_content" name="${prefix}[content]" required="required" class="form-control w-100" />
  <span class="input-group-addon">
    <i class="fa fa-spinner fa-spin" style="display: none" id="content-search-spinner"></i>
  </span>
</div>
`);
            const content = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: searchUrl,
                    prepare: (query, settings) => {
                        settings.url = searchUrl
                            .replace(/__ID__/g, $tagSelection.val())
                            .replace(/__Q__/g, query);
                        return settings;
                    },
                    transform: (response) => {
                        console.log(response);
                        return response.results;
                    }
                }
            });

            $(`#${prefix}_content`).typeahead({
                minLength: 2,
                highlight: true
            }, {
                name: 'content',
                source: content,
            }).bind('typeahead:asyncrequest', (evt) => {
                $('#content-search-spinner').fadeIn();
            }).bind('typeahead:asyncreceive', (evt) => {
                $('#content-search-spinner').fadeOut();
            });
        }
    }

    changeContent($tagSelection.select2('data')[0]['text'].split('|')[1]);
})();