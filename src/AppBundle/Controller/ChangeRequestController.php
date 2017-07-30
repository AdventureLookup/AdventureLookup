<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\User;
use AppBundle\Form\ChangeRequestType;
use AppBundle\Security\ChangeRequestVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * @Route("/change-requests")
 */
class ChangeRequestController extends Controller
{
    /**
     * Show all change requests for the given adventure
     *
     * @param Adventure $adventure
     * @return Response
     */
    public function showAction(Adventure $adventure)
    {
        $em = $this->getDoctrine()->getManager();
        $changeRequestRepository = $em->getRepository(ChangeRequest::class);
        $qb = $changeRequestRepository->createQueryBuilder('c');
        $qb
            ->join('c.adventure', 'a')
            ->andWhere($qb->expr()->eq('c.adventure', ':adventure_id'))
            ->setParameter('adventure_id', $adventure->getId())
            ->orderBy('c.resolved', 'ASC')
            ->addOrderBy('c.createdAt', 'DESC');

        if (!$this->isGranted(['ROLE_CURATOR'])) {
            // Show only change requests which
            // - aren't resolved or
            // - created by the authenticated user or
            // - are for an adventure the authenticated user created
            $user = $this->getUser();
            if ($user instanceof User) {
                $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->eq('c.resolved', $qb->expr()->literal(false)),
                        $qb->expr()->eq('c.createdBy', ':username'),
                        $qb->expr()->eq('a.createdBy', ':username')
                    ))
                    ->setParameter('username', $user->getUsername());
            } else {
                $qb->andWhere($qb->expr()->eq('c.resolved', $qb->expr()->literal(false)));
            }
        }
        $changeRequests = $qb->getQuery()->execute();

        return $this->render('change_request/show.html.twig', [
            'changeRequests' => $changeRequests,
            'adventure' => $adventure,
            'newChangeRequest' => $this->createChangeRequestForAdventure($adventure)
        ]);
    }

    /**
     * @Route("/new/{adventure_id}", name="changerequest_new")
     * @ParamConverter("adventure", options={"id" = "adventure_id"})
     *
     * @param Request $request
     * @param Adventure $adventure
     * @return Response
     */
    public function newAction(Request $request, Adventure $adventure)
    {
        $changeRequest = new ChangeRequest();
        $changeRequest->setAdventure($adventure);
        $this->denyAccessUnlessGranted(ChangeRequestVoter::CREATE, $changeRequest);

        $form = $this->createForm(ChangeRequestType::class, $changeRequest);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($changeRequest);
            $em->flush();

            $this->addFlash('success', 'Change request created.');

            return $this->redirectToRoute('adventure_show', ['slug' => $adventure->getSlug()]);
        }

        return $this->render('change_request/new.html.twig', array(
            'form' => $form->createView(),
            'adventure' => $adventure,
        ));
    }

    /**
     * @Route("/resolve/{id}", name="changerequest_resolve")
     * @Method("POST")
     *
     * @param Request $request
     * @param ChangeRequest $changeRequest
     * @return Response
     */
    public function resolveAction(Request $request, ChangeRequest $changeRequest)
    {
        $this->denyAccessUnlessGranted(ChangeRequestVoter::TOGGLE_RESOLVED, $changeRequest);
        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('resolveChangeRequest', $csrfToken)) {
            throw $this->createAccessDeniedException();
        }

        $resolve = $request->request->filter('resolve', true, FILTER_VALIDATE_BOOLEAN);
        $changeRequest->setResolved($resolve);

        $em = $this->getDoctrine()->getManager();
        $em->merge($changeRequest);
        $em->flush();

        return $this->redirectToRoute('adventure_show', ['slug' => $changeRequest->getAdventure()->getSlug()]);
    }

    /**
     * Creates a new change request for the given adventure.
     *
     * @param Adventure $adventure
     * @return ChangeRequest
     */
    private function createChangeRequestForAdventure(Adventure $adventure)
    {
        $changeRequest = new ChangeRequest();
        $changeRequest->setAdventure($adventure);

        return $changeRequest;
    }

}
