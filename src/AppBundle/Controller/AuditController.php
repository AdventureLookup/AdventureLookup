<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use DataDog\AuditBundle\Entity\AuditLog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        foreach ($filters as $name => $filter) {
            $this->applyFilter($qb, $name, $filter);
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

    /**
     * @param QueryBuilder $qb
     * @param $key
     * @param $val
     * @throws \Exception
     */
    private function applyFilter(QueryBuilder $qb, $key, $val)
    {
        switch ($key) {
            case 'history':
                if ($val) {
                    $orx = $qb->expr()->orX();
                    $orx->add('s.fk = :fk');
                    $orx->add('t.fk = :fk');
                    $qb->andWhere($orx);
                    $qb->setParameter('fk', intval($val));
                }
                break;
            case 'class':
                $orx = $qb->expr()->orX();
                $orx->add('s.class = :class');
                $orx->add('t.class = :class');
                $qb->andWhere($orx);
                $qb->setParameter('class', $val);
                break;
            case 'blamed':
                if ($val === 'null') {
                    $qb->andWhere($qb->expr()->isNull('a.blame'));
                } else {
                    // this allows us to safely ignore empty values
                    // otherwise if $qb is not changed, it would add where the string is empty statement.
                    $qb->andWhere($qb->expr()->eq('b.fk', ':blame'));
                    $qb->setParameter('blame', $val);
                }
                break;
            default:
                // if user attempts to filter by other fields, we restrict it
                throw new \Exception("filter not allowed");
        }
    }
}
