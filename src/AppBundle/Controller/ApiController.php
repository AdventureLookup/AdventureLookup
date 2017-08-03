<?php

namespace AppBundle\Controller;

use AppBundle\Exception\FieldDoesNotExistException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiController extends Controller
{
    /**
     * @Route("/autocomplete/{fieldName}", name="api_autocomplete_field")
     *
     * @param Request $request
     * @param string $fieldName
     * @return JsonResponse
     */
    public function autocompleteFieldValue(Request $request, string $fieldName)
    {
        try {
            $field = $this->get('app.field_provider')->getField($fieldName);
        } catch (FieldDoesNotExistException $e) {
            throw new NotFoundHttpException();
        }

        $q = $request->query->get('q');

        $results = $this->get('adventure_search')->autocompleteFieldContent($field, $q);

        return new JsonResponse($results);
    }
}
