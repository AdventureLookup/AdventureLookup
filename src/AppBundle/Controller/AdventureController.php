<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureList;
use AppBundle\Entity\Review;
use AppBundle\Field\FieldProvider;
use AppBundle\Form\Type\AdventureType;
use AppBundle\Form\Type\ReviewType;
use AppBundle\Security\AdventureVoter;
use AppBundle\Service\AdventureSearch;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormInterface;
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
     * @Route("/adventures", name="adventure_index")
     * @Method("GET")
     *
     * @return Response
     */
    public function indexAction(Request $request, AdventureSearch $adventureSearch, FieldProvider $fieldProvider)
    {
        list($q, $filters, $page, $sortBy, $seed) = $adventureSearch->requestToSearchParams($request);
        list($adventures, $totalNumberOfResults, $hasMoreResults, $stats) = $adventureSearch->search(
            $q,
            $filters,
            $page,
            $sortBy,
            $seed
        );

        return $this->render('adventures/index.html.twig', [
            'adventures' => $adventures,
            'totalNumberOfResults' => $totalNumberOfResults,
            'hasMoreResults' => $hasMoreResults,
            'page' => $page,
            'stats' => $stats,
            'searchFilter' => $filters,
            'fields' => $fieldProvider->getFields(),
            'q' => $q,
            'sortBy' => $sortBy,
            'seed' => $seed,
        ]);
    }

    /**
     * Redirect from route with trailing slash to route without trailing slash.
     * Based on https://symfony.com/doc/3.4/routing/redirect_trailing_slash.html
     *
     * TODO: Remove in Symfony 4.x (https://symfony.com/doc/4.4/routing.html#redirecting-urls-with-trailing-slashes)
     *
     * @Route("/adventures/")
     * @Method("GET")
     */
    public function redirectFromURLWithTrailingSlashAction(Request $request): RedirectResponse
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        // 308 (Permanent Redirect) is similar to 301 (Moved Permanently) except
        // that it does not allow changing the request method (e.g. from POST to GET)
        return $this->redirect($url, 308);
    }

    /**
     * Creates a new adventure entity.
     *
     * @Route("/adventure", name="adventure_new")
     * @Method({"GET", "POST"})
     *
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

        return $this->render('adventure/new.html.twig', [
            'adventure' => $adventure,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a adventure entity.
     *
     * @Route("/adventures/{slug}", name="adventure_show")
     * @Method("GET")
     *
     * @param UserInterface $user
     *
     * @return Response
     */
    public function showAction(Adventure $adventure, EntityManagerInterface $em,
                               UserInterface $user = null)
    {
        $this->denyAccessUnlessGranted(AdventureVoter::VIEW, $adventure);

        $deleteForm = $this->createDeleteForm($adventure);
        $reviewForm = $this->createReviewForm($adventure);
        $reviewDeleteForm = $this->createdReviewDeleteFormTemplate();
        $adventureListRepository = $em->getRepository(AdventureList::class);

        return $this->render('adventure/index.html.twig', [
            'adventure' => $adventure,
            'delete_form' => $deleteForm->createView(),
            'review_form' => $reviewForm->createView(),
            'review_delete_form' => $reviewDeleteForm->createView(),
            'lists' => $adventureListRepository->myLists($user),
        ]);
    }

    /**
     * Finds and displays a random adventure entity.
     *
     * @Route("/random-adventure", name="adventure_random")
     * @Method("GET")
     *
     * @return Response
     */
    public function randomAction(EntityManagerInterface $em)
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Adventure::class, 'a');
        $rsm->addFieldResult('a', 'id', 'id');
        $rsm->addFieldResult('a', 'slug', 'slug');

        $query = $em->createNativeQuery('
            SELECT a.id, a.slug from adventure a ORDER by RAND() limit 1
        ', $rsm);

        $randomAdventure = $query->getResult();

        $a = $randomAdventure[0];

        if (!$a) {
            throw $this->createNotFoundException('No adventure found');
        }

        return $this->redirectToRoute('adventure_show', ['slug' => $a->getSlug()]);
    }

    /**
     * Displays a form to edit an existing adventure entity.
     *
     * @Route("/adventures/{id}/edit", name="adventure_edit")
     * @Method({"GET", "POST"})
     *
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

        return $this->render('adventure/edit.html.twig', [
            'adventure' => $adventure,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Deletes a adventure entity.
     *
     * @Route("/adventures/{id}", name="adventure_delete")
     * @Method("DELETE")
     *
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
     * @return FormInterface
     */
    private function createDeleteForm(Adventure $adventure)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adventure_delete', ['id' => $adventure->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Creates a form to create/edit a review for the specified adventure.
     *
     * @return FormInterface
     */
    private function createReviewForm(Adventure $adventure)
    {
        $review = $adventure->getReviewBy($this->getUser());
        if (null === $review) {
            $review = new Review($adventure);
            $actionUrl = $this->generateUrl('review_new', ['id' => $adventure->getId()]);
        } else {
            $actionUrl = $this->generateUrl('review_edit', ['id' => $review->getId()]);
        }

        return $this->createForm(ReviewType::class, $review, [
            'action' => $actionUrl,
        ]);
    }

    /**
     * Creates a form template to delete a review.
     * The action needs to be set manually inside the template:
     * {{ form_start(form, {'action': path('review_delete', {id: <ID>})}) }}
     *
     * @return FormInterface
     */
    private function createdReviewDeleteFormTemplate()
    {
        return $this->createFormBuilder()
            ->setMethod('DELETE')
            ->getForm();
    }
}
