import toastr from "toastr";

$(function () {
    handleReviewWriting();
    handleReviewVoting();

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

    function handleReviewVoting() {
        const $reviewContainer = $('#review-container');
        const voteUrl = $reviewContainer.data('vote-url');
        const csrfToken = $reviewContainer.data('vote-csrf-token');

        $('.review').each(function () {
            const reviewId = $(this).data('review-id');
            const $voteUp = $(this).find('.review-vote-up');
            const $voteDown = $(this).find('.review-vote-down');
            const $upvotes = $(this).find('.review-upvotes');
            const $downvotes = $(this).find('.review-downvotes');

            $voteUp.click(() => {
                toggleActive($voteUp);
                markInactive($voteDown);
                updateVote();
            });
            $voteDown.click(() => {
                toggleActive($voteDown);
                markInactive($voteUp);
                updateVote();
            });

            const activeClass = 'btn-success';
            const inactiveClass = 'btn-outline-secondary';

            function isActive($btn) {
                return $btn.hasClass(activeClass);
            }

            function toggleActive($btn) {
                if (isActive($btn)) {
                    markInactive($btn);
                } else {
                    markActive($btn);
                }
            }

            function markActive($btn) {
                $btn.removeClass(inactiveClass).addClass(activeClass);
            }

            function markInactive($btn) {
                $btn.removeClass(activeClass).addClass(inactiveClass);
            }

            function updateVote() {
                let vote = 0;
                if (isActive($voteUp)) {
                    vote = 1;
                } else if (isActive($voteDown)) {
                    vote = -1;
                }

                $voteUp.attr('disabled', true);
                $voteDown.attr('disabled', true);

                $.ajax({
                    method: 'POST',
                    url: voteUrl.replace("_ID_", reviewId),
                    data: {
                        vote: vote,
                        _token: csrfToken,
                    },
                }).done((result) => {
                    toastr['success']('Your vote has been saved.');
                    $upvotes.text(result.upvotes);
                    $downvotes.text(result.downvotes);
                }).fail(() => {
                    toastr['error']('Something went wrong.');
                }).always(() => {
                    $voteUp.removeAttr('disabled');
                    $voteDown.removeAttr('disabled');
                });
            }
        })
    }
});