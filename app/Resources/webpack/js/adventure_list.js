import toastr from "toastr/toastr";

(function () {
  if ($("#adventure_list").length) {
    handleNameEdit();
  } else if ($("#adventure_list-bookmark-menu").length) {
    handleBookmarkMenu();
  }

  function handleNameEdit() {
    const $actions = $("#adventure_list-actions");
    const $editBtn = $("#adventure_list-edit-btn");
    const $cancelBtn = $("#adventure_list-cancel-btn");
    const $editForm = $("#adventure_list-edit-form");
    const $titleHeading = $("#adventure_list-title");

    $editBtn.click(() => {
      $titleHeading.addClass("d-none");
      $actions.addClass("d-none");
      $editForm.removeClass("d-none");
    });
    $cancelBtn.click(() => {
      $titleHeading.removeClass("d-none");
      $actions.removeClass("d-none");
      $editForm.addClass("d-none");
    });
  }

  function handleBookmarkMenu() {
    const $menu = $("#adventure_list-bookmark-menu");
    const $myLists = $menu.find(".dropdown-item[data-adventure-list-id]");

    $myLists.click(function () {
      const $myList = $(this);
      const toggleUrl = $myList.data("toggle-url");
      const $icon = $myList.find("i.fa");
      const spinnerIconClass = "fa-spinner fa-spin";
      const checkedIconClass = "fa-check";

      $icon.removeClass(checkedIconClass).addClass(spinnerIconClass);
      $myList.attr("disabled", true);

      $.ajax({
        method: "PATCH",
        url: toggleUrl,
      })
        .done((result) => {
          if (result.contained) {
            toastr["success"]("The adventure has been bookmarked.");
            $icon.addClass(checkedIconClass);
          } else {
            toastr["success"](
              "The adventure has been removed from your bookmarks."
            );
          }
        })
        .fail(() => {
          toastr["error"]("Something went wrong.");
        })
        .always(() => {
          $icon.removeClass(spinnerIconClass);
          $myList.attr("disabled", false);
        });
    });
  }
})();
