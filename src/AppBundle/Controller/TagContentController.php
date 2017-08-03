<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\TagContent;
use AppBundle\Entity\TagName;
use AppBundle\Form\TagContentType;
use AppBundle\Listener\SearchIndexUpdater;
use AppBundle\Service\FieldUtils;
use Elasticsearch\ClientBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param Adventure $adventure
     *
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request, Adventure $adventure)
    {
        $em = $this->getDoctrine()->getManager();
        $fields = $em->getRepository(TagName::class)->findAll();
        $selectedField = $em->getRepository(TagName::class)->find(
            $request->query->get('fieldId')
        );

        return $this->render('tagcontent/new.html.twig', array(
            'fields' => $fields,
            'selected' => $selectedField,
            'adventure' => $adventure,
        ));
    }

    /**
     * Displays a form to edit an existing tagContent entity.
     *
     * @Route("/adventure-info/{id}/edit", name="adventure_info_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param TagContent $tagContent
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, TagContent $tagContent)
    {
        $deleteForm = $this->createDeleteForm($tagContent);
        $editForm = $this->createForm(TagContentType::class, $tagContent, [
            'type' => $tagContent->getTag()->getType()
        ]);
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
     * @Security("is_granted('ROLE_ADMIN')")
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
     * @Route("/adventure-info/similar-titles", name="similar_titles_search")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function findSimilarTitlesAction(Request $request)
    {
        $q = $request->query->get('q', false);
        if ($q === false) {
            throw new NotFoundHttpException();
        }

        $results = $this->get('adventure_search')->similarTitles($q);

        return new JsonResponse($results);
    }

    /**
     * @Route("/adventure-info/title/search")
     *
     * @return JsonResponse
     */
    public function fieldContentSearchTitleAction(Request $request)
    {
        $q = $request->query->get('q', false);
        if ($q === false) {
            throw new NotFoundHttpException();
        }

        $field = (new FieldUtils())->getTitleField();

        $results = $this->get('adventure_search')->autocompleteFieldContent($field, $q);

        return new JsonResponse($results);
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
     * @Route("/adventures/{id}/info/new/ajax", name="adventure_info_new_ajax")
     * @Method({"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param Adventure $adventure
     * @return JsonResponse
     */
    public function newAjaxAction(Request $request, Adventure $adventure)
    {
        $fieldId = $request->request->get('fieldId');
        $content = $request->request->get('content');

        $em = $this->getDoctrine()->getManager();
        $field = $em->getRepository(TagName::class)->find($fieldId);

        $contentEntity = new TagContent();
        $contentEntity
            ->setAdventure($adventure)
            ->setApproved($this->isGranted('ROLE_CURATOR'))
            ->setContent($content)
            ->setTag($field);

        $em->persist($contentEntity);
        $em->flush();

        return new JsonResponse();
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
