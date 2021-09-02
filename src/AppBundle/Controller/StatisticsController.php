<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureList;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\Review;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/statistics")
 */
class StatisticsController extends Controller
{
    /**
     * @Route("", name="statistics")
     * @Method("GET")
     */
    public function indexAction(EntityManagerInterface $em): Response
    {
        $qb = $em->getRepository(User::class)->createQueryBuilder('u');
        $qb->select($qb->expr()->count('u.id'));
        $userCount = $qb->getQuery()->getSingleScalarResult();

        $adventureListResult = $em->createQuery('
            SELECT COUNT(a.id) as cnt
            FROM '.AdventureList::class.' al
            LEFT JOIN al.adventures a
            GROUP BY al.id
            ORDER BY cnt
        ')->getResult();
        $medianAdventuresPerList = 0 === count($adventureListResult) ? 0 : $adventureListResult[round(count($adventureListResult) / 2)]['cnt'];

        return $this->render('statistics.html.twig', [
            'adventures' => $this->getDataForChart($em, Adventure::class),
            'reviews' => $this->getDataForChart($em, Review::class),
            'changeRequests' => $this->getDataForChart($em, ChangeRequest::class),
            'userCount' => $userCount,
            'adventureListCount' => count($adventureListResult),
            'medianAdventuresPerList' => $medianAdventuresPerList,
        ]);
    }

    private function getDataForChart(EntityManagerInterface $em, string $class)
    {
        $qb = $em->getRepository($class)->createQueryBuilder('a');
        $qb
            ->select('SUBSTRING(a.createdAt, 1, 7) AS yw')
            ->orderBy('a.createdAt', 'ASC');
        $result = $qb->getQuery()->getResult();
        $data = [];
        foreach ($result as $each) {
            if (!isset($data[$each['yw']])) {
                if (count($data)) {
                    $data[$each['yw']] = current($data);
                    next($data);
                } else {
                    $data[$each['yw']] = 0;
                }
            }
            ++$data[$each['yw']];
        }

        reset($data);
        [$startYear, $startMonth] = explode('-', key($data));
        end($data);
        [$endYear, $endMonth] = explode('-', key($data));

        $sum = 0;
        for ($year = (int) $startYear; $year <= (int) $endYear; ++$year) {
            for ($month = (int) $startMonth; $month <= (int) $endMonth; ++$month) {
                $yw = $year.'-'.($month < 10 ? '0'.$month : $month);
                if (!isset($data[$yw])) {
                    $data[$yw] = $sum;
                } else {
                    $sum = $data[$yw];
                }
            }
        }
        uksort($data, fn ($a, $b) => $a <=> $b);

        return $data;
    }
}
