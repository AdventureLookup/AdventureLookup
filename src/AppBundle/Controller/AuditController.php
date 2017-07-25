<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManager;
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
    /**
     * @Route("/audit", name="audit")
     * @Method("GET")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
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
        } else if (isset($filters['class']) && isset($filters['id'])) {
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

        $logs = $qb->getQuery()->execute();

        return $this->render('audit/index.html.twig', [
            'logs' => $logs
        ]);
    }

    /**
     * @Route("/audit/diff/{id}")
     * @Method("GET")
     *
     * @param AuditLog $log
     * @return Response
     */
    public function diffAction(AuditLog $log)
    {
        return $this->render('audit/diff.html.twig', [
            'log' => $log
        ]);
    }
}
