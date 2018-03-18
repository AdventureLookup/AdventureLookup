import LazyLoad from "vanilla-lazyload/dist/lazyload";

(function () {
  if (!$('#search-results').length) {
    return;
  }

  const myLazyLoad = new LazyLoad(),
    $optionsList = $('.options-list'),
    $searchQuery = $('#search-query'),
    $searchForm = $("#search-form");

  function search() {
    $searchQuery.attr("disabled", true);

    // Copy search query from query box into hidden input inside the
    // <form id="search-form">.
    $('#search-query-form').val($searchQuery.val());

    $searchForm.submit();
  }

  $searchQuery.on('keypress', function (e) {
    if(e.which === 13) {
      search();
    }
  });

  $('#search-submit').on('click', search);

  // Toggle open a filters' options
  $('.adl-sidebar').on('click', '.filter > .title', e => {
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
  // If a filter has more options than displayed, clicking on 'show-more' shows them (obviously)
  $optionsList.on('click', '.show-more', e => {
    let more = e.target;
    $(more).siblings('.d-none').toggleClass('d-none').toggleClass('d-none-inactive');
    $(more).hide();
    $(more).siblings('.show-less').css('display', 'flex');
  });
  // If a filter has more options than displayed, clicking on 'show-less' hides them
  $optionsList.on('click', '.show-less', e => {
    let less = e.target;
    $(less).siblings('.d-none-inactive').toggleClass('d-none').toggleClass('d-none-inactive');
    $(less).hide();
    $(less).siblings('.show-more').css('display', 'flex');
  });
  // Show more filters (hopefully the lesser-used ones)
  $('#filter-more').on('click', e => {
    $('.adl-sidebar').find('.filter.d-none').removeClass('d-none');
    $(e.target).hide();
  });

  // Clicking on a filter tag removes it
  $('#search-tags').on('click', '.filter-tag', e => {
    const fieldName = $(e.target).data('field-name'),
      value = $(e.target).data('value'),
      key = $(e.target).data('key'),
      fieldType = $(e.target).data('field-type');

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

    $(e.target).remove();

    search();
  });

  // Load more adventures
  let currentPage = 1;
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
      if ($(result).find('#load-more-btn').length === 0) {
          $loadMoreBtn.remove();
      }

      $('#search-results').append($(result).find('#search-results'));

      myLazyLoad.update();
    }).fail(function () {
      alert('Something went wrong.');
    }).always(function () {
      $loadMoreBtn.attr('disabled', false);
      $loadMoreBtn.find('.fa-spin').addClass('d-none');
    });
  });
})();
