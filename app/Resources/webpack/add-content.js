import Bloodhound from "typeahead.js/dist/bloodhound";
import toastr from "toastr";

(function () {
    const $page = $('#page--add-content');
    if (!$page.length) {
        return;
    }

    const searchUrl = $page.data('content-search-url');
    const adventureUrl = $page.data('adventure-url');
    const saveUrl = $page.data('content-save-url');
    const prefix = 'appbundle_tagcontent';

    const $saveAndAddButton = $('#' + prefix + '_saveAndAdd');
    const $saveButton = $('#' + prefix + '_save');
    $saveAndAddButton.on('click', (evt) => {
        evt.preventDefault();
        saveChanges($saveAndAddButton);
    });
    $saveButton.on('click', (evt) => {
        evt.preventDefault();
        saveChanges($saveButton, () => {
            document.location.href = adventureUrl;
        });
    });


    function fieldSelected(data) {
        const $option = $(data[0]['element']);
        changeContent($option.data('type'), $option.data('description'), $option.data('example'));
    }

    const $tagSelection = $('#' + prefix + '_tag');
    $tagSelection.select2();
    $tagSelection.on('select2:select', function(){
        $(this).focus();
        fieldSelected($(this).select2('data'));
    });

    $tagSelection.parent().after(`
<div content="form-group">
    <label class="control-label required" for="${prefix}_content">Content</label>
    <div id="tag-content-placeholder" class="mb-3"></div>
</div>
`);

    let $contentInput;

    const $contentContainer = $('#tag-content-placeholder');
    function changeContent(type, description, example) {
        $contentContainer.empty();
        switch (type) {
            default:
            case 'string':
                createStringField();
                break;
            case 'text':
                $contentContainer.append(`
<textarea id="${prefix}_content" name="${prefix}[content]" required="required" class="form-control" rows="15">

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
        $contentContainer.prepend($('<small class="form-text text-muted"></small>').text('Example: ' + example));
        $contentContainer.prepend($('<small class="form-text text-muted"></small>').text(description));
        $contentContainer.append(`
        <small class="form-text text-muted">If the adventure has multiple creatures, authors, etc., only insert one at a time and then hit 'Save and add more information' to insert the next one.</small>
        <small class="form-text text-muted">You can hit ENTER to quickly save the fields's content.</small>
`);
        $contentInput = $(`#${prefix}_content`);
        $contentInput.keypress(function (e) {
            if (e.which === 13 && $contentInput.get(0).tagName.toLowerCase() === 'input') {
                saveChanges();
            }
        });

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
                datumTokenizer: Bloodhound.tokenizers.whitespace,
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: searchUrl,
                    prepare: (query, settings) => {
                        settings.url = searchUrl
                            .replace(/__ID__/g, $tagSelection.val())
                            .replace(/__Q__/g, query);
                        return settings;
                    },
                },
                sufficient: 20
            });

            $(`#${prefix}_content`).typeahead({
                minLength: 0,
                highlight: true
            }, {
                name: 'content',
                source: content,
                limit: 20
            }).bind('typeahead:asyncrequest', (evt) => {
                //$('#content-search-spinner').fadeIn();
            }).bind('typeahead:asyncreceive', (evt) => {
                //$('#content-search-spinner').fadeOut();
            });
        }
    }

    fieldSelected($tagSelection.select2('data'));
    $contentInput.focus();

    function saveChanges($btn, done) {
        if ($contentInput.val().length === 0) {
            toastr['error']('Content cannot be empty!');
            return;
        }
        if (!$btn) {
            $btn = $saveButton;
        }

        $btn.prop('disabled', true);
        $contentInput.prop('readonly', true);
        $('#content-search-spinner').fadeIn();
        $.ajax(saveUrl, {
            data: {
                fieldId: $tagSelection.val(),
                content: $contentInput.attr('type') === 'checkbox' ? $contentInput.prop('checked') : $contentInput.val()
            },
            method: 'POST'
        }).done(() => {
            if ($contentInput.attr('type') === 'checkbox') {
                $contentInput.prop('checked', false);
            } else {
                $contentInput.val('');
            }
            toastr['success']('Changes saved!');
            if (done) {
                done();
            }
        }).fail(() => {
            toastr['error']('Sorry, something went wrong.', 'Your changes could not be saved.');
        }).always(() => {
            $contentInput.prop('readonly', false);
            $btn.prop('disabled', false);
            $contentInput.focus();
            $('#content-search-spinner').fadeOut();
        });
    }
})();