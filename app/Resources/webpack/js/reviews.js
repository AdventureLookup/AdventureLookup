$(function () {
    handleReviewWriting();
    handleReviewVoting();

    function handleReviewVoting() {
        $('.review-voting').each(function () {
            const reviewId = $(this).data('review-id');
            const $voteUp = $(this).find('.review-voting-up');
            const $voteDown = $(this).find('.review-voting-down');

            $voteUp.parent().click(() => {
                $voteUp.parent().toggleClass('active', $voteUp.prop('checked'));
                $voteDown.prop('checked', false);
                $voteDown.parent().removeClass('active');
                updateVote();
            });
            $voteDown.parent().click(() => {
                $voteDown.parent().toggleClass('active', $voteDown.prop('checked'));
                $voteUp.prop('checked', false);
                $voteUp.parent().removeClass('active');
                updateVote();
            });

            function updateVote() {
                let vote = 0;
                if ($voteUp.prop('checked') === true) {
                    vote = 1;
                } else if ($voteDown.prop('checked') === true) {
                    vote = -1;
                }
            }
        })
    }

    function handleReviewWriting() {
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
    }
});