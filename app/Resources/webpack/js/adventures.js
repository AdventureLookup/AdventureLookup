(function () {
  const $results = $('#search-results');

  if (!$results.length) {
    return;
  }

  $('#search-submit').on('click', () => {
    $('#search-form').submit();
  });
  // Toggle open a filters' options
  $('#filter-panel').on('click', '.filter > .title', e => {
    $(e.target).closest('.filter').toggleClass('open');
  });
  // Set appropriate input option and update visuals for the whole filter
  $('.options-list').on('click', '.option', e => {
    let $option = $(e.target),
      $filter = $option.closest('.filter'),
      checkbox = $option.find('input[type=checkbox]')[0];

    if (checkbox) {
      $option.toggleClass('filter-marked');
      checkbox.checked = !checkbox.checked;
      // Check or un-check the filter title
      $filter.toggleClass('filter-marked', $filter.find('input[type=checkbox]:checked').length > 0);
      e.preventDefault();
      return;
    }
  })
})();
