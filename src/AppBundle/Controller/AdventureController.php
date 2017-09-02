<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureList;
use AppBundle\Field\FieldProvider;
use AppBundle\Form\Type\AdventureType;
use AppBundle\Security\AdventureVoter;
use AppBundle\Service\AdventureSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Adventure controller.
 */
class AdventureController extends Controller
{
    /**
     * Lists all adventure entities.
     *
     * @Route("/adventures/", name="adventure_index")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param AdventureSearch $adventureSearch
     * @param FieldProvider $fieldProvider
     * @return Response
     */
    public function indexAction(Request $request, AdventureSearch $adventureSearch, FieldProvider $fieldProvider)
    {
        $q = $request->get('q', '');
        $page = (int)$request->get('page', 1);
        $filters = $request->get('f', []);
        $fields = $fieldProvider->getFields();
        list($paginatedAdventureDocuments, $totalNumberOfResults, $stats) = $adventureSearch->search($q, $filters, $page);

        return $this->render('adventure/index.html.twig', [
            'adventures' => $paginatedAdventureDocuments,
            'totalNumberOfResults' => $totalNumberOfResults,
            'page' => $page,
            'stats' => $stats,
            'searchFilter' => $filters,
            'fields' => $fields,
            'q' => $q,
        ]);
    }

    /**
     * Creates a new adventure entity.
     *
     * @Route("/adventure", name="adventure_new")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        $adventure = new Adventure();
        $this->denyAccessUnlessGranted(AdventureVoter::CREATE, $adventure);

        $isCurator = $this->isGranted('ROLE_CURATOR');
        if ($isCurator) {
            $adventure->setApproved(true);
        }

        $form = $this->createForm(AdventureType::class, $adventure);
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
     * @Route("/adventures/{slug}", name="adventure_show")
     * @Method("GET")
     *
     * @param Adventure $adventure
     * @param EntityManagerInterface $em
     * @param UserInterface $user
     * @return Response
     */
    public function showAction(Adventure $adventure, EntityManagerInterface $em,
                               UserInterface $user = null)
    {
        $this->denyAccessUnlessGranted(AdventureVoter::VIEW, $adventure);

        $deleteForm = $this->createDeleteForm($adventure);
        $adventureListRepository = $em->getRepository(AdventureList::class);

        return $this->render('adventure/show.html.twig', [
            'adventure' => $adventure,
            'delete_form' => $deleteForm->createView(),
            'lists' => $adventureListRepository->myLists($user),
        ]);
    }

    /**
     * Displays a form to edit an existing adventure entity.
     *
     * @Route("/adventures/{id}/edit", name="adventure_edit")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param Adventure $adventure
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, Adventure $adventure)
    {
        $this->denyAccessUnlessGranted(AdventureVoter::EDIT, $adventure);

        $deleteForm = $this->createDeleteForm($adventure);
        $editForm = $this->createForm(AdventureType::class, $adventure);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('adventure_show', ['slug' => $adventure->getSlug()]);
        }

        return $this->render('adventure/edit.html.twig', array(
            'adventure' => $adventure,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a adventure entity.
     *
     * @Route("/adventures/{id}", name="adventure_delete")
     * @Method("DELETE")
     *
     * @param Request $request
     * @param Adventure $adventure
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Adventure $adventure)
    {
        $this->denyAccessUnlessGranted(AdventureVoter::DELETE, $adventure);

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
