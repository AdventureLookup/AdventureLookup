<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
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
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $adventures = $em->getRepository('AppBundle:Adventure')->findAll();

        return $this->render('adventure/index.html.twig', array(
            'adventures' => $adventures,
        ));
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
}
