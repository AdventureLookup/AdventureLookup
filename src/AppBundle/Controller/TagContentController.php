<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\TagContent;
use AppBundle\Entity\TagName;
use AppBundle\Listener\SearchIndexUpdater;
use Elasticsearch\ClientBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Tagcontent controller.
 */
class TagContentController extends Controller
{
    /**
     * Creates a new tagContent entity.
     *
     * @Route("/adventures/{id}/info/new", name="adventure_info_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request $request
     * @param Adventure $adventure
     *
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request, Adventure $adventure)
    {
        $em = $this->getDoctrine()->getManager();
        $field = $em->getRepository(TagName::class)->find(
            $request->query->get('fieldId')
        );

        $tagContent = new Tagcontent();
        $tagContent->setAdventure($adventure);
        $tagContent->setTag($field);
        if ($this->isGranted('ROLE_CURATOR')) {
            $tagContent->setApproved(true);
        }
        $form = $this->createForm('AppBundle\Form\TagContentType', $tagContent, ['isEdit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tagContent);
            $em->flush();

            $this->addFlash('success', 'Information saved!');

            if ($form->get('saveAndAdd')->isClicked()) {
                return $this->redirectToRoute('adventure_info_new', [
                    'id' => $tagContent->getAdventure()->getId(),
                    'fieldId' => $field->getId(),
                ]);
            }

            return $this->redirectToRoute('adventure_show', ['slug' => $tagContent->getAdventure()->getSlug()]);
        }

        return $this->render('tagcontent/new.html.twig', array(
            'tagContent' => $tagContent,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing tagContent entity.
     *
     * @Route("/adventure-info/{id}/edit", name="adventure_info_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_CURATOR')")
     */
    public function editAction(Request $request, TagContent $tagContent)
    {
        $deleteForm = $this->createDeleteForm($tagContent);
        $editForm = $this->createForm('AppBundle\Form\TagContentType', $tagContent, ['isEdit' => true]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('adventure_show', ['slug' => $tagContent->getAdventure()->getSlug()]);
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
     * @Route("/adventure-info/{id}", name="adventure_info_delete")
     * @Method("DELETE")
     * @Security("is_granted('ROLE_CURATOR')")
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

        return $this->redirectToRoute('adventure_show', ['slug' => $tagContent->getAdventure()->getSlug()]);
    }

    /**
     * @Route("/adventure-info/{id}/search", name="field_content_search")
     *
     * @param TagName $field
     * @return JsonResponse
     */
    public function fieldContentSearchAction(Request $request, TagName $field)
    {
        $q = $request->query->get('q');

        $results = $this->get('adventure_search')->autocompleteFieldContent($field, $q);

        return new JsonResponse($results);
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
