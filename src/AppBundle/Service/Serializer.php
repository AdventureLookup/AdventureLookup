<?php

namespace AppBundle\Service;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureDocument;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\Review;

class Serializer
{
    /**
     * Serializes the given adventure into an array, ready to be json_encoded.
     * It also includes all reviews and unresolved change requests.
     *
     * @param Adventure $adventure
     * @return array
     */
    public function serializeAdventureWithReviewsAndUnresolvedChangeRequests(Adventure $adventure): array
    {
        $reviews = $adventure->getReviews()
            ->map(function ($review) {
                return $this->serializeReview($review);
            })
            ->toArray();
        $changeRequests = $adventure->getUnresolvedChangeRequests()
            ->map(function ($changeRequest) {
                return $this->serializeChangeRequest($changeRequest);
            })
            ->toArray();

        return [
            "adventure" => $this->serializeAdventureDocument(AdventureDocument::fromAdventure($adventure)),
            "reviews" => $reviews,
            "change_requests" => $changeRequests
        ];
    }

    /**
     * @param ChangeRequest $changeRequest
     * @return array
     */
    public function serializeChangeRequest(ChangeRequest $changeRequest): array
    {
        return [
            "id" => $changeRequest->getId(),
            "field_name" => $changeRequest->getFieldName(),
            "comment" => $changeRequest->getComment(),
            "curator_remarks" => $changeRequest->getCuratorRemarks(),
            "resolved" => $changeRequest->isResolved(),
            "updated_at" => $changeRequest->getUpdatedAt()->format("c"),
            "updated_by" => $changeRequest->getUpdatedBy(),
            "created_at" => $changeRequest->getCreatedAt()->format("c"),
            "created_by" => $changeRequest->getCreatedBy()
        ];
    }

    /**
     * @param Review $review
     * @return array
     */
    public function serializeReview(Review $review): array
    {
        return [
            "id" => $review->getId(),
            "is_positive" => $review->isThumbsUp(),
            "comment" => $review->getComment(),
            "created_at" => $review->getCreatedAt()->format("c"),
            "created_by" => $review->getCreatedBy()
        ];
    }

    /**
     * @param AdventureDocument $adventure
     * @return array
     */
    public function serializeAdventureDocument(AdventureDocument $adventure): array
    {
        return [
            "id" => $adventure->getId(),
            "title" => $adventure->getTitle(),
            "description" => $adventure->getDescription(),
            "slug" => $adventure->getSlug(),
            "authors" => $adventure->getAuthors(),
            "edition" => $adventure->getEdition(),
            "environments" => $adventure->getEnvironments(),
            "items" => $adventure->getItems(),
            "publisher" => $adventure->getPublisher(),
            "setting" => $adventure->getSetting(),
            "common_monsters" => $adventure->getCommonMonsters(),
            "boss_monsters" => $adventure->getBossMonsters(),
            "min_starting_level" => $adventure->getMinStartingLevel(),
            "max_starting_level" => $adventure->getMaxStartingLevel(),
            "starting_level_range" => $adventure->getStartingLevelRange(),
            "num_pages" => $adventure->getNumPages(),
            "found_in" => $adventure->getFoundIn(),
            "part_of" => $adventure->getPartOf(),
            "official_url" => $adventure->getLink(),
            "thumbnail_url" => $adventure->getThumbnailUrl(),
            "soloable" => $adventure->isSoloable(),
            "has_pregenerated_characters" => $adventure->hasPregeneratedCharacters(),
            "has_tactical_maps" => $adventure->isTacticalMaps(),
            "has_handouts" => $adventure->isHandouts(),
            "publication_year" => $adventure->getYear(),
        ];
    }
}
