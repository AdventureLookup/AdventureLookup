$(function () {
    const $addReviewBtn = $('#add-review-btn');
    const $confirmReviewBtn = $('#confirm-review-btn');
    const $cancelReviewBtn = $('#cancel-review-btn');
    const $createReviewContainer = $('#create-review-container');
    const $thumbsUp = $('#review-thumbs-up');
    const $thumbsDown = $('#review-thumbs-down');
    const $thumbsMessage = $('#review-thumbs-message');
    const $reviewForm = $('#review_form');
    const $reviewRating = $('#review_rating');

    const hasReviewed = !!$createReviewContainer.data('has-reviewed');

    if (!hasReviewed || (hasReviewed && $reviewRating.prop('checked'))) {
        thumbsUp();
    } else {
        thumbsDown();
    }

    $addReviewBtn.click(function() {
        $addReviewBtn.addClass('d-none');
        $confirmReviewBtn.removeClass('d-none');
        $cancelReviewBtn.removeClass('d-none');
        $createReviewContainer.removeClass('d-none');
    });

    $cancelReviewBtn.click(function () {
        $addReviewBtn.removeClass('d-none');
        $confirmReviewBtn.addClass('d-none');
        $cancelReviewBtn.addClass('d-none');
        $createReviewContainer.addClass('d-none');
    });

    $thumbsUp.click(thumbsUp);
    $thumbsDown.click(thumbsDown);

    $confirmReviewBtn.click(function () {
        $reviewForm.submit();
    });

    function thumbsUp() {
        $thumbsUp.addClass('active');
        $thumbsDown.removeClass('active');
        $thumbsMessage.text('I liked this adventure!');
        $reviewRating.prop('checked', true);
    }

    function thumbsDown() {
        $thumbsUp.removeClass('active');
        $thumbsDown.addClass('active');
        $thumbsMessage.text('I disliked this adventure!');
        $reviewRating.prop('checked', false);
    }
});
