import LazyLoad from "vanilla-lazyload/dist/lazyload";

(function () {
  if (!$("#search-results").length) {
    return;
  }

  const myLazyLoad = new LazyLoad();

  // Load more adventures
  let currentPage = 1;
  const $loadMoreBtn = $("#load-more-btn");
  $loadMoreBtn.click(function () {
    $loadMoreBtn.attr("disabled", true);
    $loadMoreBtn.find(".fa-spin").removeClass("d-none");

    const $searchForm = $("#search-form");
    const data = $searchForm.serialize() + "&page=" + ++currentPage;
    $.ajax({
      method: "POST",
      url: $searchForm.attr("action"),
      data: data,
    })
      .done(function (result) {
        if ($(result).find("#load-more-btn").length === 0) {
          $loadMoreBtn.remove();
        }

        $("#search-results").append($(result).find("#search-results"));

        myLazyLoad.update();
      })
      .fail(function () {
        alert("Something went wrong.");
      })
      .always(function () {
        $loadMoreBtn.attr("disabled", false);
        $loadMoreBtn.find(".fa-spin").addClass("d-none");
      });
  });
})();
