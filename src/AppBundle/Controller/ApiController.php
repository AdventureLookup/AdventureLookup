<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureDocument;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\Review;
use AppBundle\Security\AdventureVoter;
use AppBundle\Service\AdventureSearch;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * External API controller.
 *
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/adventures/", name="api_adventures")
     * @Method({"GET"})
     *
     * @param Request $request
     * @param AdventureSearch $adventureSearch
     * @return JsonResponse
     */
    public function indexAction(Request $request, AdventureSearch $adventureSearch)
    {
        list($q, $filters, $page) = $adventureSearch->requestToSearchParams($request);
        list($adventures, $totalNumberOfResults) = $adventureSearch->search($q, $filters, $page);

        return new JsonResponse([
            "total_count" => $totalNumberOfResults,
            "adventures" => array_map(function (AdventureDocument $adventure) {
                return $this->serializeAdventureDocument($adventure);
            }, $adventures)
        ]);
    }

    /**
     * @Route("/adventures/{id}", name="api_adventure")
     * @Method("GET")
     *
     * @param Adventure $adventure
     * @return JsonResponse
     */
    public function showAction(Adventure $adventure)
    {
        $this->denyAccessUnlessGranted(AdventureVoter::VIEW, $adventure);

        $reviews = $adventure->getReviews()->map(function (Review $review) {
            return [
                "id" => $review->getId(),
                "is_positive" => $review->isThumbsUp(),
                "comment" => $review->getComment(),
                "created_at" => $review->getCreatedAt()->format("c"),
                "created_by" => $review->getCreatedBy()
            ];
        })->toArray();

        $changeRequests = $adventure->getUnresolvedChangeRequests()->map(function (ChangeRequest $changeRequest) {
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
        })->toArray();

        return new JsonResponse([
            "adventure" => $this->serializeAdventureDocument(AdventureDocument::fromAdventure($adventure)),
            "reviews" => $reviews,
            "change_requests" => $changeRequests
        ]);
    }


    /**
     * @Route("", name="api_docs")
     * @Method("GET")
     *
     * @return Response
     */
    public function docsAction() {

        return $this->render("api/docs.html.twig");
    }

    /**
     * @param AdventureDocument $adventure
     * @return array
     */
    private function serializeAdventureDocument(AdventureDocument $adventure): array
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
            "has_handouts" => $adventure->isHandouts()
        ];
    }
}
