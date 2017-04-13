<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\TagContent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tagcontent controller.
 *
 * @Route("/adventure-info")
 */
class TagContentController extends Controller
{
    /**
     * Creates a new tagContent entity.
     *
     * @Route("/{id}/new", name="adventure_info_new")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param Adventure $adventure
     *
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request, Adventure $adventure)
    {
        $tagContent = new Tagcontent();
        $tagContent->setAdventure($adventure);
        $form = $this->createForm('AppBundle\Form\TagContentType', $tagContent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tagContent);
            $em->flush();

            return $this->redirectToRoute('adventure_show', array('id' => $tagContent->getAdventure()->getId()));
        }

        return $this->render('tagcontent/new.html.twig', array(
            'tagContent' => $tagContent,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing tagContent entity.
     *
     * @Route("/{id}/edit", name="adventure_info_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, TagContent $tagContent)
    {
        $deleteForm = $this->createDeleteForm($tagContent);
        $editForm = $this->createForm('AppBundle\Form\TagContentType', $tagContent);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('adventure_show', array('id' => $tagContent->getAdventure()->getId()));
        }

        return $this->render('tagcontent/edit.html.twig', array(
            'tagContent' => $tagContent,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a tagContent entity.
     *
     * @Route("/{id}", name="adventure_info_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, TagContent $tagContent)
    {
        $form = $this->createDeleteForm($tagContent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tagContent);
            $em->flush();
        }

        return $this->redirectToRoute('adventure_show', ['id' => $tagContent->getAdventure()->getId()]);
    }

    /**
     * Creates a form to delete a tagContent entity.
     *
     * @param TagContent $tagContent The tagContent entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(TagContent $tagContent)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adventure_info_delete', array('id' => $tagContent->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
