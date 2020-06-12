// Returns a function, that, as long as it continues to be invoked, will not
// be triggered. The function will be called after it stops being called for
// N milliseconds. If `immediate` is passed, trigger the function on the
// leading edge, instead of the trailing.
function debounce(func, wait, immediate) {
  let timeout;
  return function () {
    const context = this,
      args = arguments;
    const later = function () {
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
  const $page = $("#page--create-adventure, #page--edit-adventure");
  if (!$page.length) {
    return;
  }

  const similarTitlesUrl = $page.data("similar-titles-url");
  const searchUrl = $page.data("search-url");

  let $title = $("#appbundle_adventure_title");
  $title.on(
    "change keyup paste",
    debounce(function (e) {
      $.getJSON(similarTitlesUrl, {
        q: $(this).val(),
      }).done(function (data) {
        const similarAdventuresWarning = $(".similar-adventures-warning");
        const similarAdventuresList = $(".similar-adventures-list");
        if (data.length === 0) {
          similarAdventuresWarning.addClass("d-none");
        } else {
          similarAdventuresList.empty();
          for (let i = 0; i < data.length; i++) {
            const adventure = data[i];
            const link = $("<a></a>");
            link.text(adventure.title);
            link.attr("target", "_blank");
            link.attr("rel", "noopener");
            // TODO: We should not hardcode the URL here!
            link.attr("href", "/adventures/" + adventure["slug"]);
            similarAdventuresList.append($("<li></li>").append(link));
          }
          similarAdventuresWarning.removeClass("d-none");
        }
      });
    }, DEBOUNCE)
  );

  $("input[data-autocomplete]").each(function () {
    const $field = $(this);
    const fieldName = $field.attr("id").split("_").pop();
    $field.selectize({
      create: true,
      valueField: "title",
      labelField: "title",
      searchField: "title",
      maxItems: 1,
      preload: "focus",
      load: function (query, callback) {
        $.ajax({
          url: searchUrl.replace(/__FIELD__/g, fieldName),
          data: {
            q: query,
          },
          type: "GET",
          error: function () {
            callback();
          },
          success: function (res) {
            callback(
              res.map((content) => {
                return { title: content };
              })
            );
          },
        });
      },
    });
  });

  // Iterate through all new entity fields and gather the maximum new field index.
  const $newEntityNames = $(
    'input[id^="appbundle_adventure_"][id$="_name"]'
  ).filter(function () {
    return this.id.match(/-new/);
  });
  const newEntityFieldIndices = $newEntityNames
    .map(function () {
      const idParts = this.id.split("_");
      return parseInt(idParts[idParts.length - 2]);
    })
    .get();
  let newFieldIndex =
    newEntityFieldIndices.length === 0
      ? 0
      : Math.max.apply(null, newEntityFieldIndices) + 1;

  $('select[name^="appbundle_adventure"]').each(function () {
    const $select = $(this);
    const fieldName = $select.attr("id").split("_").pop();

    let createNewItemCallback = false;
    if ($select.data("allow-add")) {
      createNewItemCallback = function (query, callback) {
        // Check for existing selected options in select input
        let existingOptionWithSameName = null;
        const existingOptions = $select[0].selectize.options;
        Object.keys(existingOptions).forEach(function (key) {
          let option = existingOptions[key];
          if (option.title.toLowerCase() === query.toLowerCase()) {
            existingOptionWithSameName = option;
          }
        });
        if (existingOptionWithSameName !== null) {
          callback();
          alert("An entity with the same name already exists.");
          return;
        }
        // Check for existing new entities on the page
        $(
          'input[id^="appbundle_adventure_' + fieldName + '-new_"][id$="_name"]'
        ).each(function () {
          if ($(this).val().toLowerCase() === query.toLowerCase()) {
            existingOptionWithSameName = $(this).val();
          }
        });
        if (existingOptionWithSameName !== null) {
          callback();
          alert(
            "An entity with the same name is already going to be added to the adventure."
          );
          return;
        }

        const $modal = $("#newFieldContentModal");
        const $modalForm = $modal.find(".modal-form");
        const $modalAddBtn = $modal.find("#newFieldContentModal-add");

        // Create new form
        const $newEntities = $(`#appbundle_adventure_${fieldName}-new`);
        const prototype = $newEntities
          .data("prototype")
          .replace(/__name__/g, ++newFieldIndex)
          .replace(/__label__/g, "");
        $modalForm.html(prototype);
        $modalForm.find("select").selectize();

        // Set name attribute
        const $nameInput = $(
          `#appbundle_adventure_${fieldName}-new_${newFieldIndex}_name`
        );
        $nameInput.attr("readonly", true);
        $nameInput.val(query);

        const addBtnClickHandler = () => {
          $modalAddBtn.attr("disabled", true);
          $modalForm.children().addClass("d-none").appendTo($newEntities);
          callback({ title: query, value: "n" + query });
          $modal.modal("hide");
        };
        $modalAddBtn.one("click", addBtnClickHandler);
        $modalAddBtn.attr("disabled", false);

        $modal.one("shown.bs.modal", () => {
          $modalAddBtn.focus();
        });
        $modal.one("hidden.bs.modal", () => {
          callback();
          // Make sure to remove the click handler and the modal content, the click handler
          // might not have been executed and the modal content not moved into the form if the
          // modal has been hidden without clicking the add button. Otherwise, the click
          // handler would trigger twice when opening the modal the next time.
          $modalAddBtn.off("click", addBtnClickHandler);
          $modalForm.children().empty();
          selectized.focus();
        });

        $modal.modal("show");
      };
    }

    const selectized = $select.selectize({
      create: createNewItemCallback,
      //sortField: 'title',
      //valueField: 'title',
      labelField: "title",
      maxItems: $select.attr("multiple") ? null : 1,
      preload: "focus",
      searchField: "title",
      render: {
        option: function (item, escape) {
          return "<div>" + escape(item.title) + "</div>";
        },
      },
      load: function (query, callback) {
        $.ajax({
          url: searchUrl.replace(/__FIELD__/g, fieldName),
          data: {
            q: query,
          },
          type: "GET",
          error: function () {
            callback();
          },
          success: function (res) {
            callback(
              res.map((content) => {
                return { title: content };
              })
            );
          },
        });
      },
    })[0].selectize;
  });
})();
