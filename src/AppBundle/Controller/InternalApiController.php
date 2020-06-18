<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AdventureDocument;
use AppBundle\Exception\FieldDoesNotExistException;
use AppBundle\Field\FieldProvider;
use AppBundle\Service\AdventureSearch;
use AppBundle\Service\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InternalApiController extends Controller
{
    /**
     * @Route("/autocomplete/field/{fieldName}", name="api_autocomplete_field")
     * @Method("GET")
     *
     * @return JsonResponse
     */
    public function autocompleteFieldValueAction(Request $request, FieldProvider $fieldProvider, AdventureSearch $adventureSearch, string $fieldName)
    {
        try {
            $field = $fieldProvider->getField($fieldName);
        } catch (FieldDoesNotExistException $e) {
            throw new NotFoundHttpException();
        }

        $q = $request->query->get('q');
        $results = $adventureSearch->autocompleteFieldContent($field, $q);

        return new JsonResponse($results);
    }

    /**
     * @Route("/autocomplete/similar-titles", name="similar_titles_search")
     * @Method("GET")
     *
     * @return JsonResponse
     */
    public function findSimilarTitlesAction(Request $request, AdventureSearch $adventureSearch)
    {
        $q = $request->query->get('q', false);
        if (false === $q) {
            throw new NotFoundHttpException();
        }
        $ignoreId = $request->query->getInt('ignoreId', -1);

        return new JsonResponse($adventureSearch->similarTitles($q, $ignoreId));
    }

    /**
     * @Route("/autocomplete/similar-adventures", name="similar_adventures_search")
     * @Method("GET")
     *
     * @return JsonResponse
     */
    public function findSimilarAdventures(Request $request, AdventureSearch $adventureSearch, Serializer $serializer)
    {
        $id = $request->query->filter('id', null, FILTER_VALIDATE_INT);
        if (false === $id) {
            throw new NotFoundHttpException();
        }

        [$adventures, $terms] = $adventureSearch->similarAdventures($id);
        $adventures = array_map(fn (AdventureDocument $adventure) => [
                'id' => $adventure->getId(),
                'title' => $adventure->getTitle(),
                'slug' => $adventure->getSlug(),
                'description' => $adventure->getDescription(),
                'score' => $adventure->getScore(),
            ], $adventures);

        return new JsonResponse([
            'adventures' => $adventures,
            'terms' => $terms,
        ]);
    }
}
