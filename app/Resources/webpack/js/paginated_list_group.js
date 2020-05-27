$(function () {
  const perPage = 15;

  $(".list-group-paginated").each(function () {
    const $listGroup = $(this);
    const $items = $listGroup.children();
    const total = $items.length;
    if (total > perPage) {
      // Hide items from index perPage up to the end.
      $items.slice(perPage).addClass("d-none");
      const $showMore = $(`
                <a href="javascript:void(0)" class="list-group-item list-group-item-action" style="border-top-width: 5px">
                    <em>Show ${perPage} more</em>
                </a>
            `);
      $listGroup.append($showMore);
      $showMore.click(function () {
        const $hiddenItems = $items.filter(".d-none");
        if ($hiddenItems.length - perPage <= 0) {
          $(this).remove();
        }
        $hiddenItems.slice(0, perPage).removeClass("d-none");
      });
    }
  });
});
