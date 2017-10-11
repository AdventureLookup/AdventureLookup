(function () {
  const $results = $('#search-results'),
    $optionsList = $('.options-list');

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
  $optionsList.on('click', '.option', e => {
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
  });
  // If a filter has more options than displayed, clicking on "more" shows them (obviously)
  $optionsList.on('click', '.show-more', () => {
    $(this).siblings('.d-none').removeClass('d-none');
    $(this).remove();
  });
  // Clicking on a filter tag removes it
  $('#search-tags').on('click', '.filter-tag', () => {
    const fieldName = $(this).data('field-name');
    const value = $(this).data('value');
    const key = $(this).data('key');
    const fieldType = $(this).data('field-type');
    if (fieldType === 'string') {
      const $strInput = $(`input[name^="f[${fieldName}][v]"][value="${value}"]`);
      if ($strInput.is(':hidden')) {
        $strInput.remove()
      } else {
        $strInput.prop('checked', false);
      }
    } else if (fieldType === 'boolean') {
      $(`input[name^="f[${fieldName}][v]"][value=""]`).prop('checked', true);
    } else if (fieldType === 'integer') {
      $(`input[name^="f[${fieldName}][v][${key}]"]`).val('');
    }
    $(this).parent('.filter-tag').remove();
    $('#search-btn').click();
  });
  // Load more adventures
  let currentPage = 1;
  const $searchForm = $("#search-form");
  const $loadMoreBtn = $('#load-more-btn');
  $loadMoreBtn.click(function () {
    $loadMoreBtn.attr('disabled', true);
    $loadMoreBtn.find('.fa-spin').removeClass('d-none');

    const data = $searchForm.serialize() + '&page=' + ++currentPage;
    $.ajax({
      method: 'POST',
      url: $searchForm.attr('action'),
      data: data,
    }).done(function (result) {
      $('#search-results').append($(result).find('#search-results'));

      const $newLoadMoreBtn = $(result).find('#load-more-btn')[0];
      $loadMoreBtn.attr('disabled', $($newLoadMoreBtn).is(':disabled'));

      myLazyLoad.update();
    }).fail(function () {
      alert('Something went wrong.');
    }).always(function () {
      $loadMoreBtn.find('.fa-spin').addClass('d-none');
    });
  });
})();
