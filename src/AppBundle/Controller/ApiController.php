<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureDocument;
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
        $q = $request->get('q', '');
        $page = (int)$request->get('page', 1);
        $filters = $request->get('f', []);
        if (!is_array($filters)) {
            $filters = [];
        }
        list($adventures, $totalNumberOfResults) = $adventureSearch->search($q, $filters, $page);

        return new JsonResponse([
            "total_count" => $totalNumberOfResults,
            "adventures" => $adventures
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

        $reviews = $adventure->getReviews()->map(function ($review) {
            return [
                "id" => $review->getId(),
                "is_positiv" => $review->isThumbsUp(),
                "comment" => $review->getComment(),
                "createdAt" => $review->getCreatedAt()->format("c")
            ];
        })->toArray();

        return new JsonResponse([
            "adventure" => AdventureDocument::fromAdventure($adventure),
            "reviews" => $reviews
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
}
