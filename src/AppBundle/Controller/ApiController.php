<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureDocument;
use AppBundle\Field\FieldProvider;
use AppBundle\Security\AdventureVoter;
use AppBundle\Service\AdventureSearch;
use AppBundle\Service\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * External API controller.
 *
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/adventures", name="api_adventures")
     * @Method("GET")
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request, AdventureSearch $adventureSearch, Serializer $serializer)
    {
        list($q, $filters, $page, $sortBy, $seed) = $adventureSearch->requestToSearchParams($request);
        list($adventures, $totalNumberOfResults) = $adventureSearch->search($q, $filters, $page, $sortBy, $seed);

        return new JsonResponse([
            'total_count' => $totalNumberOfResults,
            'adventures' => array_map(function (AdventureDocument $adventure) use ($serializer) {
                return $serializer->serializeAdventureDocument($adventure);
            }, $adventures),
            'seed' => $seed,
        ]);
    }

    /**
     * Redirect from route with trailing slash to route without trailing slash.
     * Based on https://symfony.com/doc/3.4/routing/redirect_trailing_slash.html
     *
     * TODO: Remove in Symfony 4.x (https://symfony.com/doc/4.4/routing.html#redirecting-urls-with-trailing-slashes)
     *
     * @Route("/adventures/")
     * @Method("GET")
     */
    public function redirectFromURLWithTrailingSlashAction(Request $request): RedirectResponse
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        // 308 (Permanent Redirect) is similar to 301 (Moved Permanently) except
        // that it does not allow changing the request method (e.g. from POST to GET)
        return $this->redirect($url, 308);
    }

    /**
     * @Route("/adventures/{id}", name="api_adventure")
     * @Method("GET")
     *
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
    public function docsAction(FieldProvider $fieldProvider)
    {
        $fields = $fieldProvider->getFields();

        return $this->render('api/docs.html.twig', [
            'fields' => $fields,
        ]);
    }
}
