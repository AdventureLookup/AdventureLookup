<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureDocument;
use AppBundle\Security\AdventureVoter;
use AppBundle\Service\AdventureSearch;
use AppBundle\Service\Serializer;
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
     * @param Serializer $serializer
     * @return JsonResponse
     */
    public function indexAction(Request $request, AdventureSearch $adventureSearch, Serializer $serializer)
    {
        list($q, $filters, $page) = $adventureSearch->requestToSearchParams($request);
        list($adventures, $totalNumberOfResults) = $adventureSearch->search($q, $filters, $page);

        return new JsonResponse([
            "total_count" => $totalNumberOfResults,
            "adventures" => array_map(function (AdventureDocument $adventure) use ($serializer) {
                return $serializer->serializeAdventureDocument($adventure);
            }, $adventures)
        ]);
    }

    /**
     * @Route("/adventures/{id}", name="api_adventure")
     * @Method("GET")
     *
     * @param Adventure $adventure
     * @param Serializer $serializer
     * @return JsonResponse
     */
    public function showAction(Adventure $adventure, Serializer $serializer)
    {
        $this->denyAccessUnlessGranted(AdventureVoter::VIEW, $adventure);
        return new JsonResponse($serializer->serializeAdventureWithReviewsAndUnresolvedChangeRequests($adventure));
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
