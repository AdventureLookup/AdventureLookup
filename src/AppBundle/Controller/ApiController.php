<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    /**
     * @Route("/autocomplete/{field}", name="api_autocomplete_field")
     */
    public function autocompleteFieldValue(Request $request, string $field)
    {
        // TODO: Search properly!
        //$fields = $this->get('app.')

        $q = $request->query->get('q');

        $results = ['a', 'b', 'c'];//$this->get('adventure_search')->autocompleteFieldContent($field, $q);

        return new JsonResponse($results);
    }
}
