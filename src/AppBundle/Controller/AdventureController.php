<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureDocument;
use AppBundle\Entity\TagName;
use AppBundle\Listener\SearchIndexUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\ClientBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Adventure controller.
 *
 * @Route("adventures")
 */
class AdventureController extends Controller
{
    /**
     * Lists all adventure entities.
     *
     * @Route("/", name="adventure_index")
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $search = $this->get('adventure_search');

        if ($request->query->has('q') && !empty($request->query->get('q', ''))) {
            $adventures = $search->searchAll($request->query->get('q'));
        } else if ($request->request->has('f') || $request->query->has('f')) {
            $filters = $request->get('f');
            $adventures = $search->searchFilter($filters);
        } else {
            $adventures = $search->all();
        }

        $em = $this->getDoctrine()->getManager();
        $tagNames = $em->getRepository('AppBundle:TagName')->findAll();

        $mostCommonValues = $search->aggregateMostCommonValues($tagNames);

        array_unshift($tagNames, (new TagName())->setId('title')->setTitle('Title')->setApproved(false)->setExample('Against the Cult of the Reptile God')->setDescription('The title of the adventure'));

        return $this->render('adventure/index.html.twig', [
            'adventures' => $adventures,
            'tagNames' => $tagNames,
            'mostCommonValues' => $mostCommonValues,
            'filter' => $request->get('f', false)
        ]);
    }

    /**
     * Creates a new adventure entity.
     *
     * @Route("/new", name="adventure_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function newAction(Request $request, UserInterface $user)
    {
        $adventure = new Adventure();
        if ($this->isGranted('ROLE_CURATOR')) {
            $adventure->setApproved(true);
        }
        $form = $this->createForm('AppBundle\Form\AdventureType', $adventure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($adventure);
            $em->flush();

            return $this->redirectToRoute('adventure_show', ['slug' => $adventure->getSlug()]);
        }

        return $this->render('adventure/new.html.twig', array(
            'adventure' => $adventure,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a adventure entity.
     *
     * @Route("/{slug}", name="adventure_show")
     * @Method("GET")
     */
    public function showAction(Adventure $adventure)
    {
        $em = $this->getDoctrine()->getManager();
        $fieldNames = $em->getRepository(TagName::class)->findAll();
        $deleteForm = $this->createDeleteForm($adventure);

        $adventure = AdventureDocument::fromAdventure($adventure);

        return $this->render('adventure/show.html.twig', array(
            'adventure' => $adventure,
            'fieldNames' => $fieldNames,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing adventure entity.
     *
     * @Route("/{id}/edit", name="adventure_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_CURATOR')")
     */
    public function editAction(Request $request, Adventure $adventure)
    {
        $deleteForm = $this->createDeleteForm($adventure);
        $editForm = $this->createForm('AppBundle\Form\AdventureType', $adventure);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('adventure_show', ['slug' => $adventure->getSlug()]);
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
     * @Security("is_granted('ROLE_CURATOR')")
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
}
