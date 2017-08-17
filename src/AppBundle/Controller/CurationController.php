<?php

namespace AppBundle\Controller;

use AppBundle\Curation\BulkEditFormHelper;
use AppBundle\Entity\Adventure;
use AppBundle\Entity\ChangeRequest;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/curation")
 * @Security("is_granted('ROLE_CURATOR')")
 */
class CurationController extends Controller
{
    const ITEMS_PER_PAGE = 20;

    /**
     * @Route("/", name="curation")
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        return $this->redirectToRoute('curation_adventures_with_change_requests');
    }

    /**
     * @Route("/adventures/edit", name="curation_bulk_edit_adventures")
     * @Method("GET")
     *
     * @param BulkEditFormHelper $bulkEditFormGenerator
     * @return Response
     */
    public function bulkEditAdventuresAction(BulkEditFormHelper $bulkEditFormGenerator)
    {
        $formsAndFields = $bulkEditFormGenerator->getFormsAndFields();

        return $this->render('curation/adventures.html.twig', [
            'formsAndFields' => $formsAndFields,
        ]);
    }

    /**
     * @Route("/adventures/edit", name="curation_do_bulk_edit_adventures")
     * @Method("POST")
     *
     * @param BulkEditFormHelper $bulkEditFormGenerator
     * @param Request $request
     * @return Response
     */
    public function doBulkEditAdventuresAction(BulkEditFormHelper $bulkEditFormGenerator, Request $request)
    {
        $formsAndFields = $bulkEditFormGenerator->getFormsAndFields();

        foreach ($formsAndFields as $formAndField) {
            $affected = $bulkEditFormGenerator->handle($request, $formAndField['form'], $formAndField['field']);
            if ($affected >= 0) {
                $this->addFlash('success', sprintf('%s adventure(s) were updated!', $affected));

                return $this->redirectToRoute('curation_bulk_edit_adventures');
            }
        }

        $this->addFlash('danger', 'Nothing happened, either no form submitted or invalid submission');

        return $this->redirectToRoute('curation_bulk_edit_adventures');
    }

    /**
     * @Route("/adventures/change-requests", name="curation_adventures_with_change_requests")
     * @Method("GET")
     *
     * @param EntityManagerInterface $em
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function adventuresWithChangeRequestsAction(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request)
    {
        $adventureRepository = $em->getRepository(Adventure::class);
        $page = $request->query->getInt('page', 1);
        /** @var SlidingPagination|Adventure[] $adventures */
        $withMostUnresolvedChangeRequestsQuery = $adventureRepository->getWithMostUnresolvedChangeRequestsQuery();
        $adventures = $paginator->paginate($withMostUnresolvedChangeRequestsQuery, $page, self::ITEMS_PER_PAGE, [
            'defaultSortFieldName' => 'changeRequestCount',
            'defaultSortDirection' => 'desc',
        ]);

        return $this->render('curation/adventures_with_change_requests.html.twig', [
            'adventures' => $adventures,
        ]);
    }

    /**
     * @Route("/change-requests", name="curation_pending_change_requests")
     * @Method("GET")
     *
     * @param EntityManagerInterface $em
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function pendingChangeRequestsAction(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request)
    {
        $changeRequestRepository = $em->getRepository(ChangeRequest::class);
        $page = $request->query->getInt('page', 1);
        /** @var SlidingPagination|ChangeRequest[] $changeRequests */
        $changeRequestsQuery = $changeRequestRepository->getUnresolvedChangeRequestsQuery();
        $changeRequests = $paginator->paginate($changeRequestsQuery, $page, self::ITEMS_PER_PAGE, [
            'defaultSortFieldName' => 'c.createdAt',
            'defaultSortDirection' => 'desc',
        ]);

        return $this->render('curation/pending_change_requests.html.twig', [
            'changeRequests' => $changeRequests,
        ]);
    }

    /**
     * @Route("/change-requests", name="curation_bulk_resolve_change_requests")
     * @Method("POST")
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     */
    public function bulkResolveChangeRequestsAction(Request $request, EntityManagerInterface $em)
    {
        $changeRequestIds = $request->request->get('change_request', []);
        $remarks = $request->request->get('remarks', '');
        if (count($changeRequestIds) > 0) {
            $qb = $em->createQueryBuilder();
            $qb
                ->update(ChangeRequest::class, 'c')
                ->set('c.resolved', $qb->expr()->literal(true))
                ->where($qb->expr()->in('c.id', $changeRequestIds))
                ->andWhere($qb->expr()->eq('c.resolved', $qb->expr()->literal(false)));
            if (!empty($remarks)) {
                $qb->set('c.curatorRemarks', $remarks);
            }

            $numUpdates = $qb->getQuery()->execute();
            $this->addFlash('success', sprintf('%s change requests resolved.', $numUpdates));
        } else {
            $this->addFlash('warning', "You didn't select any change request to resolve!");
        }

        return $this->redirectToRoute('curation_pending_change_requests');
    }
}
