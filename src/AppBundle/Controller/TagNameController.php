<?php

namespace AppBundle\Controller;

use AppBundle\Entity\TagName;
use AppBundle\Listener\SearchIndexUpdater;
use Elasticsearch\ClientBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Tagname controller.
 *
 * @Route("tags")
 * @Security("is_granted('ROLE_CURATOR')")
 */
class TagNameController extends Controller
{
    /**
     * Lists all tagName entities.
     *
     * @Route("/", name="tags_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var TagName[] $tagNames */
        $tagNames = $em->getRepository('AppBundle:TagName')->findAll();

        return $this->render('tagname/index.html.twig', array(
            'tagNames' => $tagNames,
        ));
    }

    /**
     * Creates a new tagName entity.
     *
     * @Route("/new", name="tags_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $tagName = new TagName();
        $form = $this->createForm('AppBundle\Form\TagNameType', $tagName);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tagName);
            $em->flush();

            return $this->redirectToRoute('tags_index');
        }

        return $this->render('tagname/new.html.twig', array(
            'tagName' => $tagName,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing tagName entity.
     *
     * @Route("/{id}/edit", name="tags_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, TagName $tagName)
    {
        $deleteForm = $this->createDeleteForm($tagName);
        $editForm = $this->createForm('AppBundle\Form\TagNameType', $tagName);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tags_index');
        }

        return $this->render('tagname/edit.html.twig', array(
            'tagName' => $tagName,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a tagName entity.
     *
     * @Route("/{id}", name="tags_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, TagName $tagName)
    {
        $form = $this->createDeleteForm($tagName);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tagName);
            $em->flush();
        }

        return $this->redirectToRoute('tags_index');
    }

    /**
     * Creates a form to delete a tagName entity.
     *
     * @param TagName $tagName The tagName entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(TagName $tagName)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('tags_delete', array('id' => $tagName->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
