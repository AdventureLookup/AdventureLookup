<?php

namespace AppBundle\Controller;

use AppBundle\Curation\BulkEditFormHandler;
use AppBundle\Curation\BulkEditFormProvider;
use AppBundle\Entity\Adventure;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\CuratedDomain;
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
     * @Method("GET")
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
     * @return Response
     */
    public function bulkEditAdventuresAction(BulkEditFormProvider $formProvider)
    {
        return $this->render('curation/adventures.html.twig', [
            'formsAndFields' => $formProvider->getFormsAndFields(),
        ]);
    }

    /**
     * @Route("/adventures/edit", name="curation_do_bulk_edit_adventures")
     * @Method("POST")
     *
     * @return Response
     */
    public function doBulkEditAdventuresAction(BulkEditFormProvider $formProvider, BulkEditFormHandler $formHandler, Request $request)
    {
        foreach ($formProvider->getFormsAndFields() as $formAndField) {
            $affected = $formHandler->handle($request, $formAndField['form'], $formAndField['field']);
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
     * @return RedirectResponse
     */
    public function bulkResolveChangeRequestsAction(Request $request, EntityManagerInterface $em)
    {
        $changeRequestIds = $request->request->get('change_request', []);
        $remarks = $request->request->get('remarks', '');
        if (count($changeRequestIds) > 0) {
            $numUpdates = $em->getRepository(ChangeRequest::class)
                ->resolveChangeRequestsByIds($changeRequestIds, $remarks);
            $this->addFlash('success', sprintf('%s change requests resolved.', $numUpdates));
        } else {
            $this->addFlash('warning', "You didn't select any change request to resolve!");
        }

        return $this->redirectToRoute('curation_pending_change_requests');
    }

    /**
     * @Route("/links", name="curation_review_links")
     * @Method("GET")
     */
    public function reviewLinksAction(EntityManagerInterface $em): Response
    {
        return $this->render('curation/review_urls.html.twig', [
            'links' => $this->getUrls($em, 'link'),
            'field' => 'link',
            'description' => 'This page provides an overview of the links used by adventures.
            Links are sorted by domain name.',
            'fieldTitle' => 'Download Link',
        ]);
    }

    /**
     * @Route("/image-urls", name="curation_review_image_urls")
     * @Method("GET")
     */
    public function reviewImageURLsAction(EntityManagerInterface $em): Response
    {
        return $this->render('curation/review_urls.html.twig', [
            'links' => $this->getUrls($em, 'thumbnailUrl'),
            'field' => 'thumbnailUrl',
            'description' => 'This page provides an overview of the thumbnail URLs used by adventures.
            URLS are sorted by domain name.',
            'fieldTitle' => 'Thumbnail URL',
        ]);
    }

    private function getUrls(EntityManagerInterface $em, string $field): array
    {
        $adventureRepository = $em->getRepository(Adventure::class);
        $qb = $adventureRepository->createQueryBuilder('a');
        $qb
            ->select('a.id')
            ->addSelect('a.title')
            ->addSelect('a.slug')
            ->addSelect('a.'.$field)
            ->where($qb->expr()->isNotNull('a.'.$field))
            ->orderBy($qb->expr()->asc('a.'.$field));

        $blockedDomains = $em->getRepository(CuratedDomain::class)->findBy([
            'type' => 'B',
        ]);

        $links = $qb->getQuery()->execute();
        $links = array_map(function ($link) use ($blockedDomains, $field) {
            $link['domain'] = parse_url($link[$field], PHP_URL_HOST);
            $blocked = false;
            foreach ($blockedDomains as $blockedDomain) {
                if ($blockedDomain->matchesDomain($link['domain'])) {
                    $blocked = true;
                    break;
                }
            }
            $link['blocked'] = $blocked;

            return $link;
        }, $links);
        usort($links, fn ($a, $b) => $a['domain'] <=> $b['domain']);

        return $links;
    }
}
