<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use DataDog\AuditBundle\Entity\AuditLog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Displays a log of the most recent creations, updates and removals.
 * Allows easy filtering by user and entity.
 *
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AuditController extends Controller
{
    const LOGS_PER_PAGE = 50;

    /**
     * @Route("/audit", name="audit")
     * @Method("GET")
     *
     * @return Response
     */
    public function indexAction(Request $request, PaginatorInterface $paginator)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository(AuditLog::class)
            ->createQueryBuilder('a')
            ->addSelect('s', 't', 'b')
            ->innerJoin('a.source', 's')
            ->leftJoin('a.target', 't')
            ->leftJoin('a.blame', 'b')
            ->orderBy('a.loggedAt', 'DESC');

        $filters = $request->query->get('filters', []);
        if (isset($filters['blamed'])) {
            $qb
                ->andWhere($qb->expr()->eq('b.fk', ':blame'))
                ->setParameter('blame', $filters['blamed']);
        } elseif (isset($filters['class']) && isset($filters['id'])) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->andX(
                        's.fk = :fk',
                        's.class = :class'
                    ),
                    $qb->expr()->andX(
                        't.fk = :fk',
                        't.class = :class'
                    )
                ))
                ->setParameter('fk', intval($filters['id']))
                ->setParameter('class', $filters['class']);
        }

        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            self::LOGS_PER_PAGE
        );

        return $this->render('audit/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/audit/diff/{id}")
     * @Method("GET")
     *
     * @return Response
     */
    public function diffAction(AuditLog $log)
    {
        return $this->render('audit/diff.html.twig', [
            'log' => $log,
        ]);
    }
}
