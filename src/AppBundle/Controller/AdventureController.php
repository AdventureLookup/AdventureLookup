<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\TagName;
use AppBundle\Listener\SearchIndexUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\ClientBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Adventure controller.
 *
 * @Route("adventure")
 */
class AdventureController extends Controller
{
    /**
     * Lists all adventure entities.
     *
     * @Route("/", name="adventure_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $client = ClientBuilder::create()->build();

        if ($request->query->has('q')) {
            $q = $request->query->get('q');

            $result = $client->search([
                'index' => SearchIndexUpdater::INDEX,
                'type' => SearchIndexUpdater::TYPE,
                'body' => [
                    'query' => [
                        'multi_match' => [
                            'query' => $q,
                            'fields' => ['title', 'info_*']
                        ]
                    ]
                ]
            ]);
            $adventures = $this->searchResultToAdventures($result, $em);
        } else if ($request->query->has('filter')) {
            $filters = $request->query->get('filter');

            $matches = [];
            foreach ($filters as $id => $filter) {
                if ($id !== 'title' && !is_numeric($id)) {
                    continue;
                }
                $content = $filter['content'];
                if (empty($content)) {
                    continue;
                }

                $field = is_integer($id) ? 'info_' . (int)$id : 'title';
                $operator = $filter['operator'];

                if (in_array($operator, ['gte', 'gt', 'lt', 'lte'])) {
                    $matches[] = ['range' => [$field => [$operator => $content]]];
                } else {
                    $matches[] = ['match' => [$field => $content]];
                }
            }
            if (empty($matches)) {
                return $this->redirectToRoute('adventure_index');
            } else {
                $result = $client->search([
                    'index' => SearchIndexUpdater::INDEX,
                    'type' => SearchIndexUpdater::TYPE,
                    'body' => [
                        'query' => [
                            'bool' => [
                                "must" => $matches
                            ]
                        ]
                    ]
                ]);
                $adventures = $this->searchResultToAdventures($result, $em);
            }
        } else {
            $adventures = $em->getRepository('AppBundle:Adventure')->findAll();
        }

        $tagNames = $em->getRepository('AppBundle:TagName')->findAll();
        array_unshift($tagNames, (new TagName())->setId('title')->setTitle('Title')->setSuggested(false));

        return $this->render('adventure/index.html.twig', [
            'adventures' => $adventures,
            'tagNames' => $tagNames,
        ]);
    }

    /**
     * Creates a new adventure entity.
     *
     * @Route("/new", name="adventure_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $adventure = new Adventure();
        $form = $this->createForm('AppBundle\Form\AdventureType', $adventure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($adventure);
            $em->flush();

            return $this->redirectToRoute('adventure_show', array('id' => $adventure->getId()));
        }

        return $this->render('adventure/new.html.twig', array(
            'adventure' => $adventure,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a adventure entity.
     *
     * @Route("/{id}", name="adventure_show")
     * @Method("GET")
     */
    public function showAction(Adventure $adventure)
    {
        $deleteForm = $this->createDeleteForm($adventure);

        return $this->render('adventure/show.html.twig', array(
            'adventure' => $adventure,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing adventure entity.
     *
     * @Route("/{id}/edit", name="adventure_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Adventure $adventure)
    {
        $deleteForm = $this->createDeleteForm($adventure);
        $editForm = $this->createForm('AppBundle\Form\AdventureType', $adventure);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('adventure_edit', array('id' => $adventure->getId()));
        }

        return $this->render('adventure/edit.html.twig', array(
            'adventure' => $adventure,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a adventure entity.
     *
     * @Route("/{id}", name="adventure_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Adventure $adventure)
    {
        $form = $this->createDeleteForm($adventure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($adventure);
            $em->flush();
        }

        return $this->redirectToRoute('adventure_index');
    }

    /**
     * Creates a form to delete a adventure entity.
     *
     * @param Adventure $adventure The adventure entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Adventure $adventure)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adventure_delete', array('id' => $adventure->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * @param array $result
     * @param EntityManagerInterface $em
     * @return array
     */
    private function searchResultToAdventures(array $result, EntityManagerInterface $em): array
    {
        dump($result);

        $hits = $result['hits'];
        $nHits = $hits['total'];
        if ($nHits == 0) {
            $adventures = [];
        } else {
            $ids = array_map(function ($hit) {
                return $hit['_id'];
            }, $hits['hits']);

            $qb = $em->getRepository('AppBundle:Adventure')
                ->createQueryBuilder('a');
            $qb->where($qb->expr()->in('a.id', $ids));

            $adventures = $qb->getQuery()->execute();
        }
        return $adventures;
    }
}
